<?php

namespace Tests\Unit\Policies;

use App\Policies\BackupPolicy;
use Database\Factories\BackupFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $backup = BackupFactory::new()->forServer($server)->createdByUser($user)->create();

        $policy = new BackupPolicy;

        $this->assertTrue($policy->viewAny($user, $backup));
        $this->assertTrue($policy->create($user, $backup));
        $this->assertTrue($policy->view($user, $backup));
        $this->assertTrue($policy->update($user, $backup));
        $this->assertTrue($policy->delete($user, $backup));
    }

    /** @test */
    public function it_allows_viewing_for_team_members()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $otherUser = UserFactory::new()->create();
        $user->currentTeam->users()->attach($otherUser);
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $backup = BackupFactory::new()->forServer($server)->createdByUser($user)->create();

        $policy = new BackupPolicy;

        $this->assertTrue($policy->viewAny($otherUser, $backup));
        $this->assertTrue($policy->create($otherUser, $backup));
        $this->assertTrue($policy->view($otherUser, $backup));
        $this->assertFalse($policy->update($otherUser, $backup));
        $this->assertFalse($policy->delete($otherUser, $backup));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $backup = BackupFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new BackupPolicy;

        $this->assertFalse($policy->view($otherUser, $backup));
        $this->assertFalse($policy->update($otherUser, $backup));
        $this->assertFalse($policy->delete($otherUser, $backup));
    }
}
