<?php

namespace App\Jobs;

use App\Infrastructure\Entities\ServerStatus;
use App\Models\Server;
use App\Models\ServerTaskDispatcher;
use App\Models\Task;
use App\Tasks\ProvisionFreshServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProvisionServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Server $server,
        public ?Collection $sshKeys = null
    ) {
        $this->sshKeys ??= new Collection;
    }

    /**
     * Execute the job.
     *
     * @return \App\Models\Task
     */
    public function handle()
    {
        $this->server->forceFill(['status' => ServerStatus::Provisioning])->save();

        /** @var Task */
        return $this->server
            ->runTask(new ProvisionFreshServer($this->server, $this->sshKeys))
            ->asRoot()
            ->keepTrackInBackground()
            ->when(app()->environment('local'), fn (ServerTaskDispatcher $dispatcher) => $dispatcher->updateLogIntervalInSeconds(10))
            ->dispatch();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        dispatch(new CleanupFailedServerProvisioning($this->server));
    }
}
