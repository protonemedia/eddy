<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\SshKey;
use App\Tasks\AuthorizePublicKey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddSshKeyToServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public SshKey $sshKey, public Server $server)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->server->runTask(
            new AuthorizePublicKey($this->server, $this->sshKey->public_key)
        )->asUser()->inBackground()->dispatch();
    }
}
