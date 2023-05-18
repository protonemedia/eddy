<?php

namespace Tests\Unit\Policies;

use App\Policies\DaemonPolicy;
use Database\Factories\DaemonFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DaemonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $daemon = DaemonFactory::new()->forServer($server)->create();

        $policy = new DaemonPolicy;

        $this->assertTrue($policy->viewAny($user, $daemon));
        $this->assertTrue($policy->create($user, $daemon));
        $this->assertTrue($policy->view($user, $daemon));
        $this->assertTrue($policy->update($user, $daemon));
        $this->assertTrue($policy->delete($user, $daemon));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $daemon = DaemonFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new DaemonPolicy;

        $this->assertFalse($policy->view($otherUser, $daemon));
        $this->assertFalse($policy->update($otherUser, $daemon));
        $this->assertFalse($policy->delete($otherUser, $daemon));
    }
}
