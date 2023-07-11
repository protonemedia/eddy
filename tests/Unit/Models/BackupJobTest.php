<?php

namespace Tests\Unit\Models;

use App\Events\BackupUpdated;
use App\Models\Backup;
use App\Models\BackupJobStatus;
use Database\Factories\BackupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BackupJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_doesnt_send_notifications_when_not_configured()
    {
        Event::fake(BackupUpdated::class);

        /** @var Backup */
        $backup = BackupFactory::new()->create([
            'notification_email' => null,
            'notification_on_failure' => false,
            'notification_on_success' => false,
        ]);

        $backupJob = $backup->createJob();

        $backupJob->update(['status' => BackupJobStatus::Finished]);
        $this->assertFalse($backupJob->userShouldBeNotified());

        $backupJob->update(['status' => BackupJobStatus::Failed]);
        $this->assertFalse($backupJob->userShouldBeNotified());
    }

    /** @test */
    public function it_sends_notifications_on_finished_jobs()
    {
        Event::fake(BackupUpdated::class);

        /** @var Backup */
        $backup = BackupFactory::new()->create([
            'notification_email' => 'test@eddy.management',
            'notification_on_failure' => false,
            'notification_on_success' => true,
        ]);

        $backupJob = $backup->createJob();

        $backupJob->update(['status' => BackupJobStatus::Finished]);
        $this->assertTrue($backupJob->userShouldBeNotified());

        $backupJob->update(['status' => BackupJobStatus::Failed]);
        $this->assertFalse($backupJob->userShouldBeNotified());
    }

    /** @test */
    public function it_sends_notifications_on_failed_jobs()
    {
        Event::fake(BackupUpdated::class);

        /** @var Backup */
        $backup = BackupFactory::new()->create([
            'notification_email' => 'test@eddy.management',
            'notification_on_failure' => true,
            'notification_on_success' => false,
        ]);

        $backupJob = $backup->createJob();

        $backupJob->update(['status' => BackupJobStatus::Finished]);
        $this->assertFalse($backupJob->userShouldBeNotified());

        $backupJob->update(['status' => BackupJobStatus::Failed]);
        $this->assertTrue($backupJob->userShouldBeNotified());
    }
}
