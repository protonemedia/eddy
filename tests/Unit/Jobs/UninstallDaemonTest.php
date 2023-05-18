<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallDaemon;
use App\Models\Daemon;
use App\Notifications\JobOnServerFailed;
use App\Tasks\DeleteFile;
use Database\Factories\DaemonFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallDaemonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_the_daemon_file()
    {
        TaskRunner::fake();

        /** @var Daemon */
        $daemon = DaemonFactory::new()->create();

        $job = new UninstallDaemon($daemon);
        $job->handle();

        $this->assertNull($daemon->fresh());

        TaskRunner::assertDispatched(function (DeleteFile $task) use ($daemon) {
            return $task->path === $daemon->path();
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $daemon = DaemonFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UninstallDaemon($daemon, $user);
        $job->failed(new \Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('daemons', [
            'id' => $daemon->id,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($daemon) {
            return $notification->server->is($daemon->server);
        });
    }
}
