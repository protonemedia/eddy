<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class WelcomeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_renders_the_welcome_page()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->waitForText('Eddy\'s Features')
                ->assertSee('Server Provisioning');
        });
    }
}
