<?php

namespace Tests\Browser\Jetstream;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LeaveTeamTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_users_can_leave_teams(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $user->currentTeam->users()->attach(
                $otherUser = User::factory()->withPersonalTeam()->create(),
                ['role' => 'admin']
            );

            $browser->loginAs($otherUser)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Members')
                ->within('@manage-team-members', fn (Browser $browser) => $browser->press('Leave'))
                ->within('#headlessui-portal-root', fn (Browser $browser) => $browser->press('Leave'))
                ->waitUntilMissingText('Team Members');

            $this->assertCount(0, $user->currentTeam->fresh()->users);
        });
    }

    public function test_team_owners_cant_leave_their_own_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Settings')
                ->assertDontSee('Team Members');
        });

        // Try to leave the team...
        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->deleteJson('/teams/'.$user->currentTeam->id.'/members/'.$user->id)
            ->assertJsonValidationErrorFor('team');

        $this->assertNotNull($user->currentTeam->fresh());
    }
}
