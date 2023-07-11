<?php

namespace Tests\Browser;

use App\Jobs\InstallDaemon;
use App\Jobs\UninstallDaemon;
use App\Models\Daemon;
use App\Signal;
use Database\Factories\DaemonFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class DaemonTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_a_daemon()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->daemons()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.daemons.index', $this->server))
                ->clickLink('Add Daemon')
                ->waitForModal()
                ->type('command', 'whoami')
                ->type('directory', '/var/log')
                ->clearAndType('user', 'root')
                ->clearAndType('processes', 2)
                ->pause(100)
                ->select('stop_signal', 'KILL')
                ->pause(100)
                ->press('Deploy')
                ->waitForText('The Daemon has been created');

            $this->assertCount(1, $this->server->daemons);

            Bus::assertDispatched(InstallDaemon::class, function (InstallDaemon $job) {
                return $job->daemon->is($this->server->daemons->first());
            });

            $this->assertEquals('whoami', $this->server->daemons->first()->command);
            $this->assertEquals('/var/log', $this->server->daemons->first()->directory);
            $this->assertEquals('root', $this->server->daemons->first()->user);
            $this->assertEquals(2, $this->server->daemons->first()->processes);
            $this->assertEquals(Signal::KILL, $this->server->daemons->first()->stop_signal);
        });
    }

    /** @test */
    public function it_can_update_an_existing_daemon()
    {
        $this->browse(function (Browser $browser) {
            /** @var Daemon */
            $daemon = DaemonFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.daemons.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('command', 'whoami')
                ->clearAndType('directory', '/var/log')
                ->clearAndType('user', 'root')
                ->clearAndType('processes', 2)
                ->select('stop_signal', 'KILL')
                ->press('Deploy')
                ->waitForText('The Daemon will be updated');

            $this->assertCount(1, $this->server->daemons);

            Bus::assertDispatched(InstallDaemon::class, function (InstallDaemon $job) use ($daemon) {
                return $job->daemon->is($daemon);
            });

            $this->assertEquals('whoami', $this->server->daemons->first()->command);
            $this->assertEquals('/var/log', $this->server->daemons->first()->directory);
            $this->assertEquals('root', $this->server->daemons->first()->user);
            $this->assertEquals(2, $this->server->daemons->first()->processes);
            $this->assertEquals(Signal::KILL, $this->server->daemons->first()->stop_signal);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_daemon()
    {
        $this->browse(function (Browser $browser) {
            /** @var Daemon */
            $daemon = DaemonFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.daemons.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->press('Delete Daemon')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The Daemon will be uninstalled')
                ->assertRouteIs('servers.daemons.index', $this->server);

            $this->assertNotNull($daemon->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallDaemon::class, function (UninstallDaemon $job) use ($daemon) {
                return $job->daemon->is($daemon);
            });
        });
    }
}
