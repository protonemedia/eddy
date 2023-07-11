<?php

namespace Tests\Unit\Models;

use App\Models\Backup;
use App\Models\BackupJobStatus;
use Database\Factories\BackupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_keeps_the_latest_finished_backups_and_all_failed_backups_in_between()
    {
        Carbon::setTestNow('2023-06-01 12:00:00');

        /** @var Backup */
        $backup = BackupFactory::new()->create([
            'retention' => 3,
        ]);

        $backupJobA = tap($backup->createJob())->update(['status' => BackupJobStatus::Finished]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:01
        $backupJobB = tap($backup->createJob())->update(['status' => BackupJobStatus::Finished]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:02
        $backupJobC = tap($backup->createJob())->update(['status' => BackupJobStatus::Failed]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:03
        $backupJobD = tap($backup->createJob())->update(['status' => BackupJobStatus::Finished]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:04
        $backupJobE = tap($backup->createJob())->update(['status' => BackupJobStatus::Failed]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:05
        $backupJobF = tap($backup->createJob())->update(['status' => BackupJobStatus::Finished]);

        Carbon::setTestNow(now()->addSecond()); // 2023-06-01 12:00:06
        $backupJobG = tap($backup->createJob())->update(['status' => BackupJobStatus::Failed]);

        // BackupJobA should be deleted

        $deletableBackups = $backup->cleanupAndFindDeletableBackups();
        $this->assertCount(1, $deletableBackups);
        $this->assertEquals('2023-06-01-12-00-00-my-backup', $deletableBackups[0]);
        $this->assertNull($backupJobA->fresh());
        $this->assertCount(6, $backup->jobs);
    }
}
