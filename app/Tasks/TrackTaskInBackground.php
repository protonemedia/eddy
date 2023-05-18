<?php

namespace App\Tasks;

use Illuminate\Support\Str;

class TrackTaskInBackground extends Task
{
    public string $eof;

    public function __construct(
        public Task $actualTask,
        public string $finishedUrl,
        public string $failedUrl,
        public string $timeoutUrl,
    ) {
        $this->eof = 'LARAVEL-TASK-RUNNER-'.strtoupper(Str::random(32));
    }

    public function getTimeout(): int
    {
        return $this->actualTask->getTimeout() + 30;
    }
}
