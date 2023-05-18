<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateTaskOutput;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Tasks\GetFile;
use Database\Factories\TaskFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UpdateTaskOutputTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_download_the_output_log_from_the_server()
    {
        /** @var Task */
        $task = TaskFactory::new()->create();

        Queue::fake();

        TaskRunner::fake([
            GetFile::class => 'Output!',
        ]);

        $job = new UpdateTaskOutput($task);
        $job->handle();

        $this->assertEquals('Output!', $task->fresh()->output);

        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_can_dispatch_a_new_job_after_a_delay()
    {
        /** @var Task */
        $task = TaskFactory::new()->create();

        Queue::fake();

        TaskRunner::fake([
            GetFile::class => 'Output!',
        ]);

        $job = new UpdateTaskOutput($task, 10);
        $job->handle();

        $this->assertEquals('Output!', $task->fresh()->output);

        Queue::assertPushed(UpdateTaskOutput::class, function ($job) use ($task) {
            return $job->task->is($task) && $job->delay === 10;
        });
    }

    /** @test */
    public function it_can_mark_the_task_as_timed_out()
    {
        Carbon::setTestNow('2022-12-01 12:00:00');

        /** @var Task */
        $task = TaskFactory::new()->create([
            'timeout' => 30,
        ]);

        TaskRunner::fake([
            GetFile::class => 'Output!',
        ]);

        (new UpdateTaskOutput($task))->handle();
        $this->assertEquals(TaskStatus::Pending, $task->fresh()->status);

        // 30 seconds later
        Carbon::setTestNow('2022-12-01 12:00:30');
        (new UpdateTaskOutput($task))->handle();
        $this->assertEquals(TaskStatus::Pending, $task->fresh()->status);

        // 31 seconds later
        Carbon::setTestNow('2022-12-01 12:00:31');
        (new UpdateTaskOutput($task))->handle();
        $this->assertEquals(TaskStatus::Timeout, $task->fresh()->status);
    }
}
