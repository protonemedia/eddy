<?php

namespace Tests\Feature;

use App\Events\BackupUpdated;
use App\Http\Middleware\ValidateSignature;
use App\Mail\BackupResults;
use App\Models\BackupJobStatus;
use App\Tasks\RunBackupJob;
use Database\Factories\BackupFactory;
use Database\Factories\BackupJobFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class BackupJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_start_a_backup_job_with_the_correct_token()
    {
        TaskRunner::fake();
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();

        $this->postJson(route('backup-job.store', [
            'backup' => $backup,
            'token' => $backup->dispatch_token,
        ]))->assertOk();

        TaskRunner::assertDispatched(function (RunBackupJob $task) use ($backup) {
            return $task->backupJob->backup->is($backup);
        });
    }

    /** @test */
    public function it_cant_start_a_backup_job_with_a_wrong_token()
    {
        TaskRunner::fake();

        $backup = BackupFactory::new()->create();

        $this->postJson(route('backup-job.store', [
            'backup' => $backup,
            'token' => 'nope',
        ]))->assertForbidden();

        TaskRunner::assertNotDispatched(RunBackupJob::class);
    }

    /** @test */
    public function it_shows_the_backup_information()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();
        $backupJob = $backup->createJob();

        $this->withoutMiddleware(ValidateSignature::class)->getJson(route('backup-job.show', [
            'backup_job' => $backupJob,
        ]))->assertJsonStructure([
            'name',
            'database_password',
            'databases',
            'disk',
            'exclude_files',
            'include_files',
            'patch_url',
        ]);

        $this->assertEquals(BackupJobStatus::Running, $backupJob->fresh()->status);
    }

    /** @test */
    public function it_wont_show_the_backup_info_twice()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();
        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Running]);

        $this->withoutMiddleware(ValidateSignature::class)->getJson(route('backup-job.show', [
            'backup_job' => $backupJob,
        ]))->assertForbidden();
    }

    /** @test */
    public function it_cant_update_the_backup_status_if_it_is_not_running()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();
        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Finished]);

        $this->withoutMiddleware(ValidateSignature::class)->patchJson(route('backup-job.update', [
            'backup_job' => $backupJob,
        ]))->assertForbidden();
    }

    /** @test */
    public function it_can_update_the_backup_status_as_finished()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();
        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Running]);

        $this->withoutMiddleware(ValidateSignature::class)->patchJson(route('backup-job.update', [
            'backup_job' => $backupJob,
        ]), [
            'success' => true,
            'size' => 100,
        ])
            ->assertOk()
            ->assertJson(['backups_to_delete' => []]);
    }

    /** @test */
    public function it_sends_a_mail_with_results_when_configured()
    {
        Event::fake(BackupUpdated::class);
        Mail::fake();

        $backup = BackupFactory::new()->create([
            'notification_email' => 'test@eddy.management',
            'notification_on_success' => true,
        ]);
        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Running]);

        $this->withoutMiddleware(ValidateSignature::class)->patchJson(route('backup-job.update', [
            'backup_job' => $backupJob,
        ]), [
            'success' => true,
            'size' => 100,
        ])
            ->assertOk();

        Mail::assertQueued(function (BackupResults $mail) use ($backup) {
            // Make sure the Mailable can be rendered without errors
            $mail->render();

            return $mail->hasTo($backup->notification_email);
        });
    }

    /** @test */
    public function it_returns_the_backup_names_to_delete()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create([
            'retention' => 3,
        ]);

        Carbon::setTestNow('2023-06-01 00:00:00');

        $jobToDelete = BackupJobFactory::new()->create([
            'backup_id' => $backup->id,
            'status' => BackupJobStatus::Finished,
        ]);

        Carbon::setTestNow('2023-06-02 00:00:00');

        $jobsToKeep = BackupJobFactory::new()->count(2)->create([
            'backup_id' => $backup->id,
            'status' => BackupJobStatus::Finished,
        ]);

        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Running]);

        $this->withoutMiddleware(ValidateSignature::class)->patchJson(route('backup-job.update', [
            'backup_job' => $backupJob,
        ]), [
            'success' => true,
            'size' => 100,
        ])
            ->assertOk()
            ->assertJson(['backups_to_delete' => ['2023-06-01-00-00-00-my-backup']]);

        $this->assertNull($jobToDelete->fresh());
        $this->assertNotNull($jobsToKeep[0]->fresh());
        $this->assertNotNull($jobsToKeep[1]->fresh());
        $this->assertNotNull($backupJob->fresh());
    }

    /** @test */
    public function it_can_update_the_backup_status_as_failed()
    {
        Event::fake(BackupUpdated::class);

        $backup = BackupFactory::new()->create();
        $backupJob = $backup->createJob();
        $backupJob->update(['status' => BackupJobStatus::Running]);

        $this->withoutMiddleware(ValidateSignature::class)->patchJson(route('backup-job.update', [
            'backup_job' => $backupJob,
        ]), [
            'success' => false,
            'error' => 'Something went wrong',
        ])
            ->assertOk()
            ->assertJson(['backups_to_delete' => []]);

        $backupJob->refresh();

        $this->assertEquals(BackupJobStatus::Failed, $backupJob->status);
        $this->assertEquals('Something went wrong', $backupJob->error);
    }
}
