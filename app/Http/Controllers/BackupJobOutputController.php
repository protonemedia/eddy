<?php

namespace App\Http\Controllers;

use App\Models\BackupJob;
use App\Models\BackupJobStatus;
use App\Models\Server;

class BackupJobOutputController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function __invoke(Server $server, BackupJob $backupJob)
    {
        return view('backups.job', [
            'backupJob' => $backupJob,
            'isFinished' => $backupJob->status === BackupJobStatus::Finished,
            'isPendingOrRunning' => in_array($backupJob->status, [BackupJobStatus::Pending, BackupJobStatus::Running]),
        ]);
    }
}
