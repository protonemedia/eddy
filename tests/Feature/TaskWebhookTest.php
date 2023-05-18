<?php

namespace Tests\Feature;

use App\Jobs\UpdateTaskOutput;
use App\Models\Task;
use App\Models\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaskWebhookTest extends TestCase
{
    use RefreshDatabase;

    private TaskWithCallbacks $task;

    private Task $taskModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->task = new TaskWithCallbacks;

        $this->taskModel = TaskFactory::new()->create([
            'type' => get_class($this->task),
            'instance' => serialize($this->task),
        ]);

        Cache::driver('file')->forget('test-task');

        config(['eddy.webhook_url' => null]);
    }

    /** @test */
    public function it_can_mark_a_task_as_timed_out()
    {
        Bus::fake();

        $this->post($this->taskModel->timeoutUrl())->assertOk();

        $this->assertEquals(TaskStatus::Timeout, $this->taskModel->fresh()->status);
        $this->assertEquals('timeout', Cache::driver('file')->get('test-task'));

        Bus::assertDispatched(fn (UpdateTaskOutput $job) => $job->task->is($this->taskModel));
    }

    /** @test */
    public function it_can_mark_a_task_as_failed()
    {
        Bus::fake();

        $this->post($this->taskModel->failedUrl(), ['exit_code' => 127])->assertOk();

        $this->taskModel->refresh();
        $this->assertEquals(127, $this->taskModel->exit_code);
        $this->assertEquals(TaskStatus::Failed, $this->taskModel->status);
        $this->assertEquals('failed', Cache::driver('file')->get('test-task'));

        Bus::assertDispatched(fn (UpdateTaskOutput $job) => $job->task->is($this->taskModel));
    }

    /** @test */
    public function it_can_mark_a_task_as_finished()
    {
        Bus::fake();

        $this->post($this->taskModel->finishedUrl(), ['exit_code' => 127])->assertOk();

        $this->taskModel->refresh();
        $this->assertEquals(TaskStatus::Finished, $this->taskModel->status);
        $this->assertEquals('finished', Cache::driver('file')->get('test-task'));

        Bus::assertDispatched(fn (UpdateTaskOutput $job) => $job->task->is($this->taskModel));
    }

    /** @test */
    public function it_can_call_a_callback_url_with_data()
    {
        Bus::fake();

        $this->post($this->taskModel->callbackUrl(), ['foo' => 'bar'])->assertOk();
        $this->assertEquals(['foo' => 'bar'], Cache::driver('file')->get('test-task'));

        Bus::assertNothingBatched();
    }
}
