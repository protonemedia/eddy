<?php

namespace Tests\Browser\Jetstream;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TermsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_terms_page_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/terms-of-service')
                ->waitForText('Terms of Service')
                ->assertSee('Download PDF');
        });
    }
}
