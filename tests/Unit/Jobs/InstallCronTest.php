<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallCron;
use App\Models\Cron;
use App\Notifications\JobOnServerFailed;
use App\Tasks\UploadFile;
use Database\Factories\CronFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallCronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_cron_file()
    {
        TaskRunner::fake();

        /** @var Cron */
        $cron = CronFactory::new()->create();
        $cron->id = 1;

        $this->assertEquals('/etc/cron.d/cron-1', $cron->path());
        $this->assertEquals('/home/eddy/.eddy/cron-1.log', $cron->logPath());

        //

        $job = new InstallCron($cron);
        $job->handle();

        $this->assertNotNull($cron->fresh()->installed_at);

        TaskRunner::assertDispatched(function (UploadFile $task) use ($cron) {
            return $task->path === $cron->path()
                && Str::contains($task->contents, '* * * * * eddy php -v');
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $cron = CronFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallCron($cron, $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('crons', [
            'id' => $cron->id,
            'installation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($cron) {
            return $notification->server->is($cron->server);
        });
    }
}
