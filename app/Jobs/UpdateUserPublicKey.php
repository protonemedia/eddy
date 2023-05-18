<?php

namespace App\Jobs;

use App\Models\Server;
use App\Tasks\GetFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserPublicKey implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Server $server)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $publicKeyPath = "/home/{$this->server->username}/.ssh/id_rsa.pub";

        $output = $this->server->runTask(new GetFile($publicKeyPath))->asRoot()->dispatch();

        $this->server->user_public_key = trim($output->getBuffer());
        $this->server->save();
    }
}
