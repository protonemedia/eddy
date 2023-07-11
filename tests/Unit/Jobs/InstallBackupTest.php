<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallBackup;
use App\Models\Backup;
use App\Notifications\JobOnServerFailed;
use App\Tasks\InstallEddyBackupCli;
use App\Tasks\UploadFile;
use Database\Factories\BackupFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallBackupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_install_the_cli_helper_and_uploads_the_cron_file()
    {
        TaskRunner::fake();

        /** @var Backup */
        $backup = BackupFactory::new()->create();
        $backup->id = 1;

        $this->assertEquals('/etc/cron.d/backup-1', $backup->cronPath());

        $backup->discardChanges();

        //

        $job = new InstallBackup($backup);
        $job->handle();

        $this->assertNotNull($backup->fresh()->installed_at);

        TaskRunner::assertDispatched(InstallEddyBackupCli::class);

        TaskRunner::assertDispatched(function (UploadFile $task) use ($backup) {
            return $task->path === $backup->cronPath()
                && Str::contains($task->contents, '0 0 * * * eddy (curl -X POST');
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $backup = BackupFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallBackup($backup, $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('backups', [
            'id' => $backup->id,
            'installation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($backup) {
            return $notification->server->is($backup->server);
        });
    }
}
