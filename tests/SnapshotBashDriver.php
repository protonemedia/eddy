<?php

namespace Tests;

use Spatie\Snapshots\Drivers\TextDriver;

class SnapshotBashDriver extends TextDriver
{
    public function extension(): string
    {
        return 'sh';
    }
}
