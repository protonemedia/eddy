<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CreateTeamTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_teams_can_be_created(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/teams/create')
                ->waitForText('Team Details')
                ->type('name', 'Test Team')
                ->press('Create')
                ->waitForLocation(RouteServiceProvider::HOME);

            $this->assertCount(2, $user->fresh()->ownedTeams);
            $this->assertEquals('Test Team', $user->fresh()->ownedTeams()->latest('id')->first()->name);
        });
    }
}
