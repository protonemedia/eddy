<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerDeletionFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteServerFromInfrastructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Server $server, public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->server->provider_id) {
            $this->server->getProvider()->deleteServer($this->server->provider_id);
        }

        $this->server->delete();
    }

    /**
     * The job failed to process.
     */
    public function failed(): void
    {
        $this->user->notify(new ServerDeletionFailed($this->server->name));

        $this->server->delete();
    }
}
