<?php

namespace Tests\Browser\Jetstream;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PrivacyTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_privacy_page_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/privacy-policy')
                ->waitForText('Privacy & Cookie Statement')
                ->assertSee('At Eddy, we place a high value on your privacy');
        });
    }
}
