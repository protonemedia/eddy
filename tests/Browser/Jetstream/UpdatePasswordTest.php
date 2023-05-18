<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdatePasswordTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_password_can_be_updated(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Update Password')
                ->within('@update-password-form', function (Browser $browser) {
                    $browser->type('current_password', 'password')
                        ->type('password', 'new-password')
                        ->type('password_confirmation', 'new-password')
                        ->press('Save')
                        ->waitForText('Saved.');
                });

            $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        });
    }

    public function test_current_password_must_be_correct(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Update Password')
                ->within('@update-password-form', function (Browser $browser) {
                    $browser->type('current_password', 'wrong-password')
                        ->type('password', 'new-password')
                        ->type('password_confirmation', 'new-password')
                        ->press('Save')
                        ->waitForText('The provided password does not match your current password.');
                });
            $this->assertTrue(Hash::check('password', $user->fresh()->password));
        });
    }

    public function test_new_passwords_must_match(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Update Password')
                ->within('@update-password-form', function (Browser $browser) {
                    $browser->type('current_password', 'password')
                        ->type('password', 'new-password')
                        ->type('password_confirmation', 'wrong-password')
                        ->press('Save')
                        ->waitForText('The password field confirmation does not match.');
                });

            $this->assertTrue(Hash::check('password', $user->fresh()->password));
        });
    }
}
