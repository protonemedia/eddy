<?php

namespace App\Models;

enum BackupJobStatus: string
{
    case Failed = 'failed';
    case Finished = 'finished';
    case Pending = 'pending';
    case Running = 'running';
}
