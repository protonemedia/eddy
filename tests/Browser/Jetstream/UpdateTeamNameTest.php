<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateTeamNameTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_team_names_can_be_updated(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Name')
                ->within('@update-team-name-form', function (Browser $browser) {
                    $browser->type('name', 'Test Team')
                        ->press('Save')
                        ->waitForText('Saved.');
                });

            $this->assertCount(1, $user->fresh()->ownedTeams);
            $this->assertEquals('Test Team', $user->currentTeam->fresh()->name);
        });
    }
}
