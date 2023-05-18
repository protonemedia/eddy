<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use Laravel\Dusk\Browser;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/register')
                ->waitForInput('name')
                ->assertInputPresent('name')
                ->assertInputPresent('email')
                ->assertInputPresent('password')
                ->assertInputPresent('password_confirmation');
        });
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');

            return;
        }

        $this->assertFalse(Route::has('register'));
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/register')
                ->waitForInput('name')
                ->type('name', 'Test User')
                ->type('email', 'test@example.com')
                ->type('password', 'password')
                ->type('password_confirmation', 'password');

            if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
                $browser->check('terms');
            }

            $browser->press('Register');

            if (new User instanceof MustVerifyEmail) {
                $browser->waitForLocation('/email/verify');
            } else {
                $browser->waitForLocation(RouteServiceProvider::HOME)
                    ->assertAuthenticated();
            }
        });
    }
}
