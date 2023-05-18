<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanupPendingSiteDeployment;
use App\Jobs\DeploySite;
use App\Models\DeploymentStatus;
use App\Models\Site;
use App\Tasks;
use App\Tasks\TrackTaskInBackground;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class DeploySiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_deploy_a_site()
    {
        TaskRunner::fake();
        Queue::fake();

        /** @var Site */
        $site = SiteFactory::new()->create(['zero_downtime_deployment' => false]);

        $deployment = $site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $job = new DeploySite($deployment);
        $job->handle();

        TaskRunner::assertDispatched(function (TrackTaskInBackground $task) use ($deployment) {
            return $task->actualTask->deployment->is($deployment);
        });

        Queue::assertPushed(CleanupPendingSiteDeployment::class);
    }

    /** @test */
    public function it_can_deploy_a_site_with_zero_downtime()
    {
        TaskRunner::fake();
        Queue::fake();

        /** @var Site */
        $site = SiteFactory::new()->create(['zero_downtime_deployment' => true]);

        $deployment = $site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $job = new DeploySite($deployment);
        $job->handle();

        TaskRunner::assertDispatched(function (TrackTaskInBackground $task) use ($deployment) {
            return $task->actualTask instanceof Tasks\DeploySiteWithoutDowntime
                && $task->actualTask->deployment->is($deployment);
        });

        Queue::assertPushed(CleanupPendingSiteDeployment::class);
    }
}
