<?php

namespace Tests\Browser;

use App\Jobs\MakeSoftwareDefaultOnServer;
use App\Jobs\RestartSoftwareOnServer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SoftwareTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_restart_a_service()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(route('servers.software.index', $this->server))
                ->clickLink('Restart', 'button')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('will be restarted on the server.');

            Bus::assertDispatched(RestartSoftwareOnServer::class);
        });
    }

    /** @test */
    public function it_can_mark_software_as_the_cli_default()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(route('servers.software.index', $this->server))
                ->clickLink('Make CLI default', 'button')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('will now be the CLI default on the server.');

            Bus::assertDispatched(MakeSoftwareDefaultOnServer::class);
        });
    }
}
