<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProvisionServer;
use App\Models\Server;
use App\Models\Task;
use App\Tasks\ProvisionFreshServer;
use App\Tasks\TrackTaskInBackground;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PendingTask;
use Tests\TestCase;

class ProvisionServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_runs_the_provision_script_in_the_background()
    {
        /** @var Server */
        $server = ServerFactory::new()->waitingToConnect()->create([
            'public_ipv4' => '1.2.3.4',
        ]);

        TaskRunner::fake();

        $job = new ProvisionServer($server);
        $taskModel = $job->handle();

        $this->assertInstanceOf(Task::class, $taskModel);

        TaskRunner::assertDispatched(TrackTaskInBackground::class, function (PendingTask $task) use ($server) {
            return $task->shouldRunInBackground()
                && $task->shouldRunOnConnection($server->connectionAsRoot())
                && $task->task->actualTask instanceof ProvisionFreshServer;
        });
    }
}
