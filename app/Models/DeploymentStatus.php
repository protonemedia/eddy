<?php

namespace App\Models;

enum DeploymentStatus: string
{
    case Finished = 'finished';
    case Pending = 'pending';
    case Timeout = 'timeout';
    case Failed = 'failed';
}
