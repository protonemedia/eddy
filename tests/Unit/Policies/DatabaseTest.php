<?php

namespace Tests\Unit\Policies;

use App\Policies\DatabasePolicy;
use Database\Factories\DatabaseFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $database = DatabaseFactory::new()->forServer($server)->create();

        $policy = new DatabasePolicy;

        $this->assertTrue($policy->viewAny($user, $database));
        $this->assertTrue($policy->create($user, $database));
        $this->assertTrue($policy->view($user, $database));
        $this->assertTrue($policy->update($user, $database));
        $this->assertTrue($policy->delete($user, $database));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $database = DatabaseFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new DatabasePolicy;

        $this->assertFalse($policy->view($otherUser, $database));
        $this->assertFalse($policy->update($otherUser, $database));
        $this->assertFalse($policy->delete($otherUser, $database));
    }
}
