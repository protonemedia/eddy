<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallCron;
use App\Models\Cron;
use App\Notifications\JobOnServerFailed;
use App\Tasks\DeleteFile;
use Database\Factories\CronFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallCronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_the_cron_file()
    {
        TaskRunner::fake();

        /** @var Cron */
        $cron = CronFactory::new()->create();

        $job = new UninstallCron($cron);
        $job->handle();

        $this->assertNull($cron->fresh());

        TaskRunner::assertDispatched(function (DeleteFile $task) use ($cron) {
            return $task->path === $cron->path();
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $cron = CronFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UninstallCron($cron, $user);
        $job->failed(new Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('crons', [
            'id' => $cron->id,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($cron) {
            return $notification->server->is($cron->server);
        });
    }
}
