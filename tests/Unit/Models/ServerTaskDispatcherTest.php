<?php

namespace Tests\Unit\Models;

use App\Jobs\UpdateTaskOutput;
use App\Models\Server;
use App\Tasks\Whoami;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class ServerTaskDispatcherTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_mock_the_execution_of_scripts_as_root()
    {
        /** @var Server $server */
        $server = ServerFactory::new()->create();

        TaskRunner::fake();

        $server->runTask(Whoami::class)->asRoot()->dispatch();

        TaskRunner::assertDispatched(Whoami::class, function ($task) use ($server) {
            return $task->shouldRunOnConnection($server->connectionAsRoot());
        });
    }

    /** @test */
    public function it_can_mock_the_execution_of_scripts_as_user()
    {
        /** @var Server $server */
        $server = ServerFactory::new()->create();

        TaskRunner::fake();

        $server->runTask(Whoami::class)->asUser()->dispatch();

        TaskRunner::assertDispatched(Whoami::class, function ($task) use ($server) {
            return $task->shouldRunOnConnection($server->connectionAsUser());
        });
    }

    /** @test */
    public function it_can_dispatch_a_job_to_keep_the_output_log_updated()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        TaskRunner::fake();

        Queue::fake();

        $server->runTask(Whoami::class)
            ->asUser()
            ->keepTrackInBackground()
            ->updateLogIntervalInSeconds(10)
            ->dispatch();

        Queue::assertPushed(UpdateTaskOutput::class, function ($job) {
            return $job->delay === 5 && $job->dispatchNewJobAfter === 10;
        });
    }
}
