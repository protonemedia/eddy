<?php

namespace Tests\Unit\Models;

use App\Models\Deployment;
use App\Notifications\DeploymentFailed;
use Database\Factories\DeploymentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_can_return_short_git_hash()
    {
        $deployment = DeploymentFactory::new()->create([
            'git_hash' => '1234567890abcdef',
        ]);

        $this->assertEquals('1234567', $deployment->short_git_hash);
    }

    /** @test */
    public function it_sends_a_notification_to_the_associated_user()
    {
        /** @var Deployment */
        $deployment = DeploymentFactory::new()->create();
        $deployment->notifyUserAboutFailedDeployment();

        Notification::assertSentTo($deployment->user, DeploymentFailed::class);
        $this->assertNotNull($deployment->user_notified_at);
    }

    /** @test */
    public function it_sends_a_notification_to_the_site_setting_email()
    {
        /** @var Deployment */
        $deployment = DeploymentFactory::new()->create(['user_id' => null]);

        $deployment->site->deploy_notification_email = 'test@example.com';
        $deployment->notifyUserAboutFailedDeployment();

        Notification::assertSentTo($deployment->site->deployNotifiable(), DeploymentFailed::class);
        $this->assertNotNull($deployment->user_notified_at);
    }

    /** @test */
    public function it_doesnt_send_a_notification_if_the_user_is_already_notified()
    {
        /** @var Deployment */
        $deployment = DeploymentFactory::new()->create(['user_notified_at' => now()]);

        $deployment->notifyUserAboutFailedDeployment();

        Notification::assertNothingSent();
    }
}
