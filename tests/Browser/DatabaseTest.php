<?php

namespace Tests\Browser;

use App\Jobs\InstallDatabase;
use App\Jobs\InstallDatabaseUser;
use App\Jobs\UninstallDatabase;
use App\Models\Database;
use Database\Factories\DatabaseFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class DatabaseTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_a_database_without_creating_a_user()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->databases()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->clickLink('Add Database')
                ->waitForModal()
                ->type('name', 'my_database')
                ->uncheck('create_user')
                ->press('Submit')
                ->waitForText('The database will be created');

            $this->assertCount(1, $this->server->databases);

            Bus::assertDispatched(InstallDatabase::class, function (InstallDatabase $job) {
                return $job->database->is($this->server->databases->first());
            });

            $this->assertEquals('my_database', $this->server->databases->first()->name);
        });
    }

    /** @test */
    public function it_can_add_a_database_and_create_a_user()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->databases()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->clickLink('Add Database')
                ->waitForModal()
                ->type('name', 'my_database')
                ->check('create_user')
                ->type('user', 'my_user')
                ->type('password', 'my_password')
                ->press('Submit')
                ->waitForText('The database and user will be created');

            $this->assertCount(1, $this->server->databases);
            $this->assertCount(1, $this->server->databaseUsers);

            $this->assertEquals('my_database', $this->server->databases->first()->name);
            $this->assertEquals('my_user', $this->server->databaseUsers->first()->name);

            Bus::assertChained([
                new InstallDatabase($this->server->databases->first(), $this->user->fresh()),
                new InstallDatabaseUser($this->server->databaseUsers->first(), 'my_password', $this->user->fresh()),
            ]);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_database()
    {
        $this->browse(function (Browser $browser) {
            /** @var Database */
            $database = DatabaseFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->press('Delete Database')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The database will be uninstalled')
                ->assertRouteIs('servers.databases.index', $this->server);

            $this->assertNotNull($database->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallDatabase::class, function (UninstallDatabase $job) use ($database) {
                return $job->database->is($database);
            });
        });
    }
}
