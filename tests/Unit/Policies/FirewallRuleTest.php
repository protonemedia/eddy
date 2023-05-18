<?php

namespace Tests\Unit\Policies;

use App\Policies\FirewallRulePolicy;
use Database\Factories\FirewallRuleFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirewallRuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $firewallRule = FirewallRuleFactory::new()->forServer($server)->create();

        $policy = new FirewallRulePolicy;

        $this->assertTrue($policy->viewAny($user, $firewallRule));
        $this->assertTrue($policy->create($user, $firewallRule));
        $this->assertTrue($policy->view($user, $firewallRule));
        $this->assertTrue($policy->update($user, $firewallRule));
        $this->assertTrue($policy->delete($user, $firewallRule));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $firewallRule = FirewallRuleFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new FirewallRulePolicy;

        $this->assertFalse($policy->view($otherUser, $firewallRule));
        $this->assertFalse($policy->update($otherUser, $firewallRule));
        $this->assertFalse($policy->delete($otherUser, $firewallRule));
    }
}
