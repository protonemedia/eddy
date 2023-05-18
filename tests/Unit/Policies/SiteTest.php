<?php

namespace Tests\Unit\Policies;

use App\Policies\SitePolicy;
use Database\Factories\ServerFactory;
use Database\Factories\SiteFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $site = SiteFactory::new()->forServer($server)->create();

        $policy = new SitePolicy;

        $this->assertTrue($policy->manage($user, $site));
        $this->assertTrue($policy->viewAny($user, $site));
        $this->assertTrue($policy->create($user, $site));
        $this->assertTrue($policy->view($user, $site));
        $this->assertTrue($policy->update($user, $site));
        $this->assertTrue($policy->delete($user, $site));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $site = SiteFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new SitePolicy;

        $this->assertFalse($policy->manage($otherUser, $site));
        $this->assertFalse($policy->view($otherUser, $site));
        $this->assertFalse($policy->update($otherUser, $site));
        $this->assertFalse($policy->delete($otherUser, $site));
    }
}
