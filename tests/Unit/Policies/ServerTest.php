<?php

namespace Tests\Unit\Policies;

use App\Policies\ServerPolicy;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();

        $policy = new ServerPolicy;

        $this->assertTrue($policy->manage($user, $server));
        $this->assertTrue($policy->viewAny($user, $server));
        $this->assertTrue($policy->create($user, $server));
        $this->assertTrue($policy->view($user, $server));
        $this->assertTrue($policy->update($user, $server));
        $this->assertTrue($policy->delete($user, $server));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $server = ServerFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new ServerPolicy;

        $this->assertFalse($policy->manage($otherUser, $server));
        $this->assertFalse($policy->view($otherUser, $server));
        $this->assertFalse($policy->update($otherUser, $server));
        $this->assertFalse($policy->delete($otherUser, $server));
    }
}
