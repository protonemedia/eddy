<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanupPendingSiteDeployment;
use App\Models\DeploymentStatus;
use App\Notifications\DeploymentFailed;
use Database\Factories\DeploymentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CleanupPendingSiteDeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_changes_pending_deployment_status_to_timeout_if_created_more_than_10_minutes_ago()
    {
        $deployment = DeploymentFactory::new()->create([
            'status' => DeploymentStatus::Pending,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        (new CleanupPendingSiteDeployment($deployment))->handle();

        $this->assertEquals(DeploymentStatus::Timeout, $deployment->fresh()->status);
    }

    /** @test */
    public function it_does_not_change_deployment_status_if_created_less_than_10_minutes_ago()
    {
        $deployment = DeploymentFactory::new()->create([
            'status' => DeploymentStatus::Pending,
            'created_at' => Carbon::now()->subMinutes(9),
        ]);

        (new CleanupPendingSiteDeployment($deployment))->handle();

        $this->assertEquals(DeploymentStatus::Pending, $deployment->fresh()->status);
    }

    /** @test */
    public function it_does_not_change_deployment_status_if_not_pending()
    {
        $deployment = DeploymentFactory::new()->create([
            'status' => DeploymentStatus::Finished,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        (new CleanupPendingSiteDeployment($deployment))->handle();

        $this->assertEquals(DeploymentStatus::Finished, $deployment->fresh()->status);
    }

    /** @test */
    public function it_notifies_user_about_failed_deployment_if_status_changed_to_timeout()
    {
        $deployment = DeploymentFactory::new()->create([
            'status' => DeploymentStatus::Pending,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        (new CleanupPendingSiteDeployment($deployment))->handle();

        Notification::assertSentTo($deployment->user, DeploymentFailed::class);
    }
}
