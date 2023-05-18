<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Task;
use App\Tasks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeploySite implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Deployment $deployment, public array $environmentVariables = [])
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $taskClass = $this->deployment->site->zero_downtime_deployment
            ? Tasks\DeploySiteWithoutDowntime::class
            : Tasks\DeploySite::class;

        $task = new $taskClass($this->deployment, $this->environmentVariables);

        $server = $this->deployment->site->server;

        /** @var Task */
        $taskModel = $server->runTask($task)
            ->asUser()
            ->inBackground()
            ->keepTrackInBackground()
            ->updateLogIntervalInSeconds(10)
            ->dispatch();

        dispatch(new CleanupPendingSiteDeployment($this->deployment))->delay(10 * 60);

        $this->deployment->update(['task_id' => $taskModel->id]);
    }
}
