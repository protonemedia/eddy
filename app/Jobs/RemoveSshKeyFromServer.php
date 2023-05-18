<?php

namespace App\Jobs;

use App\Models\Server;
use App\Tasks\DeauthorizePublicKey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveSshKeyFromServer implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $publicKey, public Server $server)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->server->runTask(
            new DeauthorizePublicKey($this->server, $this->publicKey)
        )->asUser()->inBackground()->dispatch();
    }
}
