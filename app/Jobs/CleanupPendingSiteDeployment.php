<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\DeploymentStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupPendingSiteDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Deployment $deployment)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->deployment->created_at->diffInSeconds() >= 10 * 60 && $this->deployment->status === DeploymentStatus::Pending) {
            $this->deployment->forceFill([
                'status' => DeploymentStatus::Timeout,
            ])->save();

            $this->deployment->notifyUserAboutFailedDeployment();
        }
    }
}
