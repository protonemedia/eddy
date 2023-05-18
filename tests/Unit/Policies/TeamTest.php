<?php

namespace Tests\Unit\Policies;

use App\Models\Team;
use App\Models\User;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_any_teams()
    {
        $user = User::factory()->create();
        $this->assertTrue((new TeamPolicy)->viewAny($user));
    }

    /** @test */
    public function user_can_view_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->view($user, $team));
    }

    /** @test */
    public function user_cannot_view_other_teams()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertFalse((new TeamPolicy)->view($user, $team));
    }

    /** @test */
    public function user_can_create_team()
    {
        $user = User::factory()->create();
        $this->assertTrue((new TeamPolicy)->create($user));
    }

    /** @test */
    public function user_can_update_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->update($user, $team));
    }

    /** @test */
    public function user_cannot_update_other_teams()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Test Team', 'personal_team' => false]);
        $this->assertFalse((new TeamPolicy)->update($user, $team));
    }

    /** @test */
    public function user_can_add_team_member_to_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->addTeamMember($user, $team));
    }

    /** @test */
    public function user_can_update_team_member_permissions_in_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->updateTeamMember($user, $team));
    }

    /** @test */
    public function user_can_remove_team_member_from_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team', 'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->removeTeamMember($user, $team));
    }

    /** @test */
    public function user_cannot_add_team_member_to_other_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertFalse((new TeamPolicy())->addTeamMember($user, $team));
    }

    /** @test */
    public function user_cannot_update_team_member_permissions_in_other_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertFalse((new TeamPolicy())->updateTeamMember($user, $team));
    }

    /** @test */
    public function user_cannot_remove_team_member_from_other_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertFalse((new TeamPolicy())->removeTeamMember($user, $team));
    }

    /** @test */
    public function user_can_delete_own_team()
    {
        $user = User::factory()->create();
        $team = $user->ownedTeams()->create(['name' => 'Test Team',  'personal_team' => true]);
        $this->assertTrue((new TeamPolicy)->delete($user, $team));
    }

    /** @test */
    public function user_cannot_delete_other_teams()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Test Team',  'personal_team' => true]);
        $this->assertFalse((new TeamPolicy)->delete($user, $team));
    }
}
