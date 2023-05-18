<?php

namespace Tests\Browser\Jetstream;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DeleteTeamTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_teams_can_be_deleted(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $user->ownedTeams()->save($team = Team::factory()->make([
                'personal_team' => false,
            ]));

            $team->users()->attach(
                $otherUser = User::factory()->create(),
                ['role' => 'editor']
            );

            $browser->loginAs($user)
                ->visit('/teams/'.$team->id)
                ->waitForText('Delete Team')
                ->within('@delete-team-form', fn (Browser $browser) => $browser->press('Delete Team'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->press('Delete Team');
                })
                ->waitUntilMissingText('Delete Team');

            $this->assertNull($team->fresh());
            $this->assertCount(0, $otherUser->fresh()->teams);
        });
    }

    public function test_personal_teams_cant_be_deleted(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Settings')
                ->assertDontSee('Delete Team');
        });

        // Try to delete the team via the API...
        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($user)
            ->deleteJson('/teams/'.$user->currentTeam->id)
            ->assertJsonValidationErrorFor('team');

        $this->assertNotNull($user->currentTeam->fresh());
    }
}
