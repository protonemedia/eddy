<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Features;
use Tests\DuskTestCase;

class DeleteAccountTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_accounts_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Delete Account')
                ->within('@delete-user-form', fn (Browser $browser) => $browser->press('Delete Account'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Delete Account');
                })
                ->waitUntilMissingText('Delete Account')
                ->assertGuest();

            $this->assertNull($user->fresh());
        });
    }
}
