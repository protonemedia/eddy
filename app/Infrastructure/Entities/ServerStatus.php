<?php

namespace App\Infrastructure\Entities;

enum ServerStatus: string
{
    case New = 'new';
    case Starting = 'starting';
    case Provisioning = 'provisioning';
    case Running = 'running';
    case Paused = 'paused';
    case Stopped = 'stopped';
    case Deleting = 'deleting';
    case Archived = 'archived';
    case Unknown = 'unknown';
}
