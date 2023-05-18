<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use App\Tasks\GetFile;
use Database\Factories\ServerFactory;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class TaskTest extends TestCase
{
    /** @test */
    public function it_generates_a_log_path_based_on_the_user()
    {
        $server = ServerFactory::new()->make();
        $task = new Task(['id' => 1, 'user' => 'root']);
        $task->server = $server;
        $this->assertEquals('/root/.eddy/task-1.log', $task->outputLogPath());

        $server = ServerFactory::new()->make();
        $task = new Task(['id' => 2, 'user' => 'protone']);
        $task->server = $server;
        $this->assertEquals('/home/eddy/.eddy/task-2.log', $task->outputLogPath());
    }

    /** @test */
    public function it_can_check_if_it_is_older_than_timeout()
    {
        $task = Task::factory()->create([
            'timeout' => 60,
        ]);

        $this->assertFalse($task->isOlderThanTimeout());

        $task->update(['created_at' => now()->subMinutes(2)]);

        $this->assertTrue($task->isOlderThanTimeout());
    }

    /** @test */
    public function it_can_return_output_lines()
    {
        $task = Task::factory()->create([
            'output' => "Line 1\nLine 2\nLine 3",
        ]);

        $outputLines = $task->outputLines();

        $this->assertEquals(['Line 1', 'Line 2', 'Line 3'], $outputLines->toArray());
    }

    /** @test */
    public function it_can_return_tail_of_output()
    {
        $task = Task::factory()->create([
            'output' => "Line 1\nLine 2\nLine 3\nLine 4\nLine 5",
        ]);

        $tailOutput = $task->tailOutput(3);

        $this->assertEquals("Line 3\nLine 4\nLine 5", $tailOutput);
    }

    /** @test */
    public function it_can_update_its_output()
    {
        /** @var Task */
        $task = Task::factory()->create();

        TaskRunner::fake([
            GetFile::class => 'Hey, I am a fake task!',
        ]);

        $task->updateOutput();

        $this->assertEquals('Hey, I am a fake task!', $task->output);
    }
}
