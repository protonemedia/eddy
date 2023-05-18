<?php

namespace Tests\Unit\Policies;

use App\Policies\SshKeyPolicy;
use Database\Factories\SshKeyFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SshKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $sshKey = SshKeyFactory::new()->forUser($user)->create();

        $policy = new SshKeyPolicy;

        $this->assertTrue($policy->viewAny($user, $sshKey));
        $this->assertTrue($policy->create($user, $sshKey));
        $this->assertTrue($policy->manage($user, $sshKey));
        $this->assertTrue($policy->delete($user, $sshKey));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $sshKey = SshKeyFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new SshKeyPolicy;

        $this->assertFalse($policy->manage($otherUser, $sshKey));
        $this->assertFalse($policy->delete($otherUser, $sshKey));
    }
}
