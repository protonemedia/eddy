<?php

namespace Tests\Unit\Tasks;

use App\Models\ServerTaskDispatcher;
use App\Models\Task as TaskModel;
use App\Models\TaskStatus;
use App\Tasks\Task;
use App\Tasks\TrackTaskInBackground;
use Database\Factories\ServerFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Process;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PendingTask;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;
use Tests\TestCase;

class TrackTaskInBackgroundTest extends TestCase
{
    use DatabaseMigrations;
    use SwitchDatabaseInEnvironmentFile;

    private $scriptCwd = __DIR__.'/output';

    public function setUp(): void
    {
        parent::setUp();

        $whichTimeout = (new ProcessRunner)->run(Process::command('which timeout'));

        if (! $whichTimeout->getBuffer()) {
            $this->markTestSkipped('The `timeout` command is not available on this system.');
        }

        config(['eddy.webhook_url' => null]);

        TaskRunner::fake()->preventStrayTasks();

        (new Filesystem)->ensureDirectoryExists($this->scriptCwd);
        (new Filesystem)->cleanDirectory($this->scriptCwd);
    }

    public function tearDown(): void
    {
        (new Filesystem)->cleanDirectory($this->scriptCwd);

        parent::tearDown();
    }

    private function runScript(string $script): ProcessOutput
    {
        $scriptPath = $this->scriptCwd.'/script.sh';
        $logPath = $this->scriptCwd.'/script.log';

        file_put_contents($scriptPath, $script);

        return (new ProcessRunner)->run(
            Process::command("bash {$scriptPath} > {$logPath}")->path($this->scriptCwd)
        );
    }

    private function dispatchTask(Task $task): TaskModel
    {
        TaskRunner::fake(TrackTaskInBackground::class);

        $dispatcher = new ServerTaskDispatcher(ServerFactory::new()->create(), PendingTask::make($task));

        $taskModel = $dispatcher->asRoot()->keepTrackInBackground()->dispatch();

        $this->assertInstanceOf(TaskModel::class, $taskModel);

        TaskRunner::assertDispatched(function (PendingTask $pendingTask) {
            if ($pendingTask->task instanceof TrackTaskInBackground) {
                return $this->runScript($pendingTask->task->getScript());
            }

            return false;
        });

        return $taskModel->fresh();
    }

    /** @test */
    public function it_writes_the_actual_script_to_a_dedicated_path()
    {
        $task = new class extends Task
        {
            public function render()
            {
                return 'echo "hello!"';
            }
        };

        $taskModel = $this->dispatchTask($task);

        $this->assertFileExists($this->scriptCwd.'/script.sh');
        $this->assertFileExists($this->scriptCwd.'/script-original.sh');

        $this->assertEquals('echo "hello!"', trim(file_get_contents($this->scriptCwd.'/script-original.sh')));
        $this->assertStringContainsString($taskModel->finishedUrl(), file_get_contents($this->scriptCwd.'/script.sh'));
        $this->assertStringContainsString($taskModel->failedUrl(), file_get_contents($this->scriptCwd.'/script.sh'));
        $this->assertStringContainsString($taskModel->timeoutUrl(), file_get_contents($this->scriptCwd.'/script.sh'));
    }

    /** @test */
    public function it_adds_30_seconds_to_the_original_timeout()
    {
        $originalTask = new class extends Task
        {
            protected $timeout = 10;
        };

        $task = new TrackTaskInBackground($originalTask, '', '', '');
        $this->assertEquals(40, $task->getTimeout());
    }

    /** @test */
    public function it_calls_the_failed_webhook()
    {
        $failedTask = new class extends Task
        {
            public function render()
            {
                return 'exit 42';
            }
        };

        $taskModel = $this->dispatchTask($failedTask);

        $this->assertEquals(42, $taskModel->exit_code);
        $this->assertEquals(TaskStatus::Failed, $taskModel->status);
    }

    /** @test */
    public function it_calls_the_timeout_webhook()
    {
        $timeoutTask = new class extends Task
        {
            protected $timeout = 1;

            public function render()
            {
                return 'sleep 10';
            }
        };

        $taskModel = $this->dispatchTask($timeoutTask);

        $this->assertEquals(TaskStatus::Timeout, $taskModel->status);
    }

    /** @test */
    public function it_calls_the_finished_webhook()
    {
        $successfulTask = new class extends Task
        {
            public function render()
            {
                return 'echo "hello!"';
            }
        };

        $taskModel = $this->dispatchTask($successfulTask);

        $this->assertNull($taskModel->exit_code);
        $this->assertEquals(TaskStatus::Finished, $taskModel->status);
    }

    /** @test */
    public function it_can_send_a_custom_callback()
    {
        $taskModel = $this->dispatchTask(new TaskWithCallback);

        $this->assertNull($taskModel->exit_code);
        $this->assertEquals(TaskStatus::Finished, $taskModel->status);

        $this->assertEquals('Hi from callback!', $taskModel->name);
    }
}
