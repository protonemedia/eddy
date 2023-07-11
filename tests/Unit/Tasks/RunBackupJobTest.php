<?php

namespace Tests\Unit\Tasks;

use App\Models\Backup;
use App\Tasks\RunBackupJob;
use App\UlidGenerator;
use Database\Factories\BackupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RunBackupJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calls_the_backup_cli_with_the_url_to_the_job()
    {
        Carbon::setTestNow('2023-06-01 12:00:00');

        UlidGenerator::createUuidsUsing(function () {
            static $ulid = 0;

            return (string) str_pad($ulid++, 26, '0', STR_PAD_LEFT);
        });

        /** @var Backup */
        $backup = BackupFactory::new()->create();

        $backupJob = $backup->createJob();

        $this->assertMatchesBashSnapshot(
            (new RunBackupJob($backupJob))->getScript()
        );
    }
}
