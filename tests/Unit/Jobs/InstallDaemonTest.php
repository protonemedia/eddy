<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallDaemon;
use App\Models\Daemon;
use App\Notifications\JobOnServerFailed;
use App\Tasks\ReloadSupervisor;
use App\Tasks\UploadFile;
use Database\Factories\DaemonFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallDaemonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_supervisor_config_and_reloads_supervisor()
    {
        TaskRunner::fake();
        Bus::fake();

        /** @var Daemon */
        $daemon = DaemonFactory::new()->create();
        $daemon->id = 1;

        $this->assertEquals('/etc/supervisor/conf.d/daemon-1.conf', $daemon->path());
        $this->assertEquals('/home/eddy/.eddy/daemon-1.err', $daemon->errorLogPath());
        $this->assertEquals('/home/eddy/.eddy/daemon-1.log', $daemon->outputLogPath());

        //

        $job = new InstallDaemon($daemon);
        $job->handle();

        $this->assertNotNull($daemon->fresh()->installed_at);

        TaskRunner::assertDispatched(function (UploadFile $task) use ($daemon) {
            return $task->path === $daemon->path()
                && Str::contains($task->contents, 'command=php -v');
        });

        TaskRunner::assertDispatched(ReloadSupervisor::class);
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $daemon = DaemonFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallDaemon($daemon, $user);
        $job->failed(new \Exception('Installation failed.'));

        $this->assertDatabaseHas('daemons', [
            'id' => $daemon->id,
            'installation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($daemon) {
            return $notification->server->is($daemon->server);
        });
    }
}
