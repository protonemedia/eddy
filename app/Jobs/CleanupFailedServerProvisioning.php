<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\Task;
use App\Notifications\ServerProvisioningFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupFailedServerProvisioning implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public ?Task $task = null,
        public ?string $errorMessage = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        rescue(fn () => $this->task?->updateOutputWithoutCallbacks(), report: false);

        $this->server->createdByUser?->notify(
            new ServerProvisioningFailed(
                $this->server->name,
                $this->task?->tailOutput() ?: '',
                $this->errorMessage ?: ''
            )
        );

        $this->server->delete();
    }
}
