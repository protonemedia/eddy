<?php

namespace App\Infrastructure\Entities;

enum Distribution: string
{
    case Ubuntu = 'ubuntu';
    case Unknown = 'unknown';
}
