<?php

namespace Tests\Unit\Policies;

use App\Policies\DatabaseUserPolicy;
use Database\Factories\DatabaseUserFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $databaseUser = DatabaseUserFactory::new()->forServer($server)->create();

        $policy = new DatabaseUserPolicy;

        $this->assertTrue($policy->viewAny($user, $databaseUser));
        $this->assertTrue($policy->create($user, $databaseUser));
        $this->assertTrue($policy->view($user, $databaseUser));
        $this->assertTrue($policy->update($user, $databaseUser));
        $this->assertTrue($policy->delete($user, $databaseUser));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $databaseUser = DatabaseUserFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new DatabaseUserPolicy;

        $this->assertFalse($policy->view($otherUser, $databaseUser));
        $this->assertFalse($policy->update($otherUser, $databaseUser));
        $this->assertFalse($policy->delete($otherUser, $databaseUser));
    }
}
