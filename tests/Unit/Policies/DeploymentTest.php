<?php

namespace Tests\Unit\Policies;

use App\Policies\DeploymentPolicy;
use Database\Factories\DeploymentFactory;
use Database\Factories\ServerFactory;
use Database\Factories\SiteFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $site = SiteFactory::new()->forServer($server)->create();
        $deployment = DeploymentFactory::new()->forSite($site)->create();

        $policy = new DeploymentPolicy;

        $this->assertTrue($policy->viewAny($user, $deployment));
        $this->assertTrue($policy->create($user, $deployment));
        $this->assertTrue($policy->view($user, $deployment));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $deployment = DeploymentFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new DeploymentPolicy;

        $this->assertFalse($policy->view($otherUser, $deployment));
    }
}
