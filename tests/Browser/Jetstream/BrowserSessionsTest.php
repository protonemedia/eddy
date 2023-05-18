<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BrowserSessionsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_other_browser_sessions_can_be_logged_out(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Browser Sessions')
                ->within('@logout-other-browser-sessions-form', fn (Browser $browser) => $browser->press('Log Out Other Browser Sessions'))
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Log Out Other Browser Sessions');
                })
                ->within('@logout-other-browser-sessions-form', fn (Browser $browser) => $browser->waitForText('Done.'));
        });
    }
}
