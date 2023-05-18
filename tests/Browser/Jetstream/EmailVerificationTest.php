<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\URL;
use Laravel\Dusk\Browser;
use Laravel\Fortify\Features;
use Tests\DuskTestCase;

class EmailVerificationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->unverified()->create();

            $browser->loginAs($user)
                ->visit('/email/verify')
                ->assertSeeIn('button', 'Resend Verification Email');
        });
    }

    public function test_email_can_be_verified(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');

            return;
        }

        $user = User::factory()->withPersonalTeam()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->browse(function (Browser $browser) use ($user, $verificationUrl) {
            $browser->loginAs($user)
                ->visit($verificationUrl)
                ->assertPathIs(RouteServiceProvider::HOME)
                ->assertQueryStringHas('verified', 1);
        });

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_can_not_verified_with_invalid_hash(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');

            return;
        }

        $user = User::factory()->withPersonalTeam()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->browse(function (Browser $browser) use ($user, $verificationUrl) {
            $browser->loginAs($user)
                ->visit($verificationUrl);
        });

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
