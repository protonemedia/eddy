<?php

namespace App\Tasks;

use App\Models\BackupJob;

class RunBackupJob extends Task
{
    public function __construct(public BackupJob $backupJob)
    {
    }

    public function getTimeout(): ?int
    {
        return $this->backupJob->backup->getCronIntervalInSeconds() * 5;
    }
}
