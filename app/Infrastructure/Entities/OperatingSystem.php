<?php

namespace App\Infrastructure\Entities;

enum OperatingSystem: string
{
    case Ubuntu2204 = 'ubuntu2204';
    case Unknown = 'unknown';
}
