<?php

namespace Tests\Browser;

use App\Jobs\InstallCron;
use App\Jobs\UninstallCron;
use App\Models\Cron;
use Database\Factories\CronFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class CronTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_a_cron_with_a_predefined_frequency()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->crons()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.crons.index', $this->server))
                ->clickLink('Add Cron')
                ->waitForModal()
                ->type('command', 'whoami')
                ->clear('user')
                ->pause(50)
                ->type('user', 'root')
                ->radio('frequency', '* * * * *')
                ->press('Deploy')
                ->waitForText('The Cron has been created');

            $this->assertCount(1, $this->server->crons);

            Bus::assertDispatched(InstallCron::class, function (InstallCron $job) {
                return $job->cron->is($this->server->crons->first());
            });

            $this->assertEquals('whoami', $this->server->crons->first()->command);
            $this->assertEquals('root', $this->server->crons->first()->user);
            $this->assertEquals('* * * * *', $this->server->crons->first()->expression);
        });
    }

    /** @test */
    public function it_can_add_a_cron_with_a_custom_frequency()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->crons()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.crons.index', $this->server))
                ->clickLink('Add Cron')
                ->waitForModal()
                ->type('command', 'whoami')
                ->clear('user')
                ->pause(100)
                ->type('user', 'root')
                ->radio('frequency', 'custom')
                ->type('custom_expression', '1 2 3 4 5')
                ->press('Deploy')
                ->waitForText('The Cron has been created');

            $this->assertCount(1, $this->server->crons);

            Bus::assertDispatched(InstallCron::class, function (InstallCron $job) {
                return $job->cron->is($this->server->crons->first());
            });

            $this->assertEquals('whoami', $this->server->crons->first()->command);
            $this->assertEquals('root', $this->server->crons->first()->user);
            $this->assertEquals('1 2 3 4 5', $this->server->crons->first()->expression);
        });
    }

    /** @test */
    public function it_can_update_an_existing_cron()
    {
        $this->browse(function (Browser $browser) {
            /** @var Cron */
            $cron = CronFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.crons.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->type('command', 'whoami')
                ->clear('user')
                ->pause(50)
                ->type('user', 'root')
                ->radio('frequency', 'custom')
                ->type('custom_expression', '1 2 3 4 5')
                ->press('Deploy')
                ->waitForText('The Cron will be updated');

            $this->assertCount(1, $this->server->crons);

            Bus::assertDispatched(InstallCron::class, function (InstallCron $job) use ($cron) {
                return $job->cron->is($cron);
            });

            $this->assertEquals('whoami', $this->server->crons->first()->command);
            $this->assertEquals('root', $this->server->crons->first()->user);
            $this->assertEquals('1 2 3 4 5', $this->server->crons->first()->expression);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_cron()
    {
        $this->browse(function (Browser $browser) {
            /** @var Cron */
            $cron = CronFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.crons.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->press('Delete Cron')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The Cron will be uninstalled')
                ->assertRouteIs('servers.crons.index', $this->server);

            $this->assertNotNull($cron->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallCron::class, function (UninstallCron $job) use ($cron) {
                return $job->cron->is($cron);
            });
        });
    }
}
