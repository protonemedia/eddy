<?php

namespace Tests\Browser\Jetstream;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateTeamMemberRoleTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_team_member_roles_can_be_updated(): void
    {
        $this->markTestSkipped("App currently doesn't have roles");

        return;

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $user->currentTeam->users()->attach(
                $otherUser = User::factory()->withPersonalTeam()->create(),
                ['role' => 'admin']
            );

            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Members')
                ->within('@manage-team-members', fn (Browser $browser) => $browser->clickLink('Administrator'))
                ->waitForText('Manage Role')
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser
                        ->press('Editor')
                        ->press('Save');
                })
                ->waitUntilMissingText('Manage Role');

            $this->assertTrue($otherUser->fresh()->hasTeamRole(
                $user->currentTeam->fresh(),
                'editor'
            ));
        });
    }

    public function test_only_team_owner_can_update_team_member_roles(): void
    {
        $this->markTestSkipped("App currently doesn't have roles");

        return;

        $user = User::factory()->withPersonalTeam()->create();

        $user->currentTeam->users()->attach(
            $otherUser = User::factory()->withPersonalTeam()->create(),
            ['role' => 'admin']
        );

        $this->browse(function (Browser $browser) use ($user, $otherUser) {
            $browser->loginAs($otherUser)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Team Members')
                ->within('@manage-team-members', function (Browser $browser) {
                    $browser->assertDontSeeLink('Administrator');
                });
        });

        // Try to update the role via the API...
        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->actingAs($otherUser)
            ->putJson('/teams/'.$user->currentTeam->id.'/members/'.$otherUser->id, [
                'role' => 'editor',
            ])
            ->assertForbidden();

        $this->assertTrue($otherUser->fresh()->hasTeamRole(
            $user->currentTeam->fresh(),
            'admin'
        ));
    }
}
