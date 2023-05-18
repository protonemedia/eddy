<?php

namespace Tests\Browser\Jetstream;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RemoveTeamMemberTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_team_members_can_be_removed_from_teams(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $user->currentTeam->users()->attach(
                $otherUser = User::factory()->withPersonalTeam()->create(),
                ['role' => 'admin']
            );

            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Members')
                ->within('@manage-team-members', fn (Browser $browser) => $browser->press('Remove'))
                ->within('#headlessui-portal-root', fn (Browser $browser) => $browser->press('Remove'))
                ->waitUntilMissingText('Team Members');

            $this->assertCount(0, $user->currentTeam->fresh()->users);
        });
    }

    public function test_only_team_owner_can_remove_team_members(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->withPersonalTeam()->create(),
            ['role' => 'admin']
        );

        $this->browse(function (Browser $browser) use ($user, $otherUser) {
            $browser->loginAs($otherUser)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Members')
                ->within('@manage-team-members', fn (Browser $browser) => $browser->assertMissing('Remove'));
        });

        // Try to remove the team owner from the team...
        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($otherUser)
            ->deleteJson('/teams/'.$user->currentTeam->id.'/members/'.$user->id)
            ->assertForbidden();
    }
}
