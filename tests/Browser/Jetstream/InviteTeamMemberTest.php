<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Mail\TeamInvitation;
use ProtoneMedia\LaravelDuskFakes\Mails\PersistentMails;
use Tests\DuskTestCase;

class InviteTeamMemberTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentMails;

    public function test_team_members_can_be_invited_to_team(): void
    {
        if (! Features::sendsTeamInvitations()) {
            $this->markTestSkipped('Team invitations not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Add Team Member')
                ->within('@add-team-member', function (Browser $browser) {
                    $browser->type('email', 'test@example.com')
                        ->press('Add');
                })
                ->waitForText('Pending Team Invitations');

            Mail::assertSent(TeamInvitation::class);

            $this->assertCount(1, $user->currentTeam->fresh()->teamInvitations);
        });
    }

    public function test_team_member_invitations_can_be_cancelled(): void
    {
        if (! Features::sendsTeamInvitations()) {
            $this->markTestSkipped('Team invitations not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $invitation = $user->currentTeam->teamInvitations()->create([
                'email' => 'test@example.com',
                'role' => 'admin',
            ]);

            $browser->loginAs($user)
                ->visit('/teams/'.$user->currentTeam->id)
                ->waitForText('Pending Team Invitations')
                ->within('@team-member-invitations', function (Browser $browser) {
                    $browser->press('Cancel');
                })
                ->waitUntilMissingText('Pending Team Invitations');

            $this->assertCount(0, $user->currentTeam->fresh()->teamInvitations);
        });
    }
}
