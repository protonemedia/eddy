<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;
use Tests\DuskTestCase;

/**
 * @runInSeparateProcess
 */
class TwoFactorAuthenticationSettingsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_two_factor_authentication_can_be_enabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Two Factor Authentication')
                ->within('@two-factor-authentication-form', fn (Browser $browser) => $browser->press('Enable'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Confirm');
                })
                ->waitForText('Finish enabling two factor authentication.');

            $this->assertNotNull($user->fresh()->two_factor_secret);
            $this->assertCount(8, $user->fresh()->recoveryCodes());
        });
    }

    public function test_two_factor_authentication_can_be_confirmed(): void
    {
        if (! Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            $this->markTestSkipped('Two factor authentication confirmation is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Two Factor Authentication')
                ->within('@two-factor-authentication-form', fn (Browser $browser) => $browser->press('Enable'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Confirm');
                })
                ->waitForText('Finish enabling two factor authentication.');

            $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));

            $browser->type('code', $code)
                ->press('Confirm')
                ->waitForText('You have enabled two factor authentication.');

            $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        });
    }

    public function test_recovery_codes_can_be_regenerated(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create([
                'two_factor_confirmed_at' => now(),
            ]);

            app(EnableTwoFactorAuthentication::class)($user);

            $this->assertCount(8, $codes = $user->recoveryCodes());

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Two Factor Authentication')
                ->within('@two-factor-authentication-form', fn (Browser $browser) => $browser->press('Show Recovery Codes'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Confirm');
                })
                ->waitForText($codes[0])
                ->waitForText($codes[7])
                ->within('@two-factor-authentication-form', fn (Browser $browser) => $browser->press('Regenerate Recovery Codes'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Confirm');
                })
                ->waitUntilMissingText($codes[0])
                ->waitUntilMissingText($codes[7]);

            $this->assertCount(8, array_diff($codes, $newCodes = $user->fresh()->recoveryCodes()));

            $browser->assertSee($newCodes[0])
                ->assertSee($newCodes[7]);
        });
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create([
                'two_factor_confirmed_at' => now(),
            ]);

            app(EnableTwoFactorAuthentication::class)($user);

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Two Factor Authentication')
                ->within('@two-factor-authentication-form', fn (Browser $browser) => $browser->press('Disable'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Confirm');
                })
                ->waitForText('You have not enabled two factor authentication.');

            $this->assertNull($user->fresh()->two_factor_secret);
        });
    }
}
