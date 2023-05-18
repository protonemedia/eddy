<?php

namespace App\Jobs;

use App\Models\Server;
use App\Server\Software;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeSoftwareDefaultOnServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Server $server, public Software $software)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = $this->software->updateAlternativesTask();

        if (! $task) {
            return;
        }

        $this->server->runTask($task)
            ->asRoot()
            ->inBackground()
            ->throw()
            ->dispatch();
    }
}
