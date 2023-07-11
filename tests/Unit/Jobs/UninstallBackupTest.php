<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallBackup;
use App\Models\Backup;
use App\Notifications\JobOnServerFailed;
use App\Tasks\DeleteFile;
use Database\Factories\BackupFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallBackupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_the_backup_cron_file()
    {
        TaskRunner::fake();

        /** @var Backup */
        $backup = BackupFactory::new()->create();

        $job = new UninstallBackup($backup);
        $job->handle();

        $this->assertNull($backup->fresh());

        TaskRunner::assertDispatched(function (DeleteFile $task) use ($backup) {
            return $task->path === $backup->cronPath();
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $backup = BackupFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UninstallBackup($backup, $user);
        $job->failed(new Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('backups', [
            'id' => $backup->id,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($backup) {
            return $notification->server->is($backup->server);
        });
    }
}
