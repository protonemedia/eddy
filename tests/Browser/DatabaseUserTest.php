<?php

namespace Tests\Browser;

use App\Jobs\InstallDatabaseUser;
use App\Jobs\UninstallDatabaseUser;
use App\Jobs\UpdateDatabaseUser;
use App\Models\Database;
use App\Models\DatabaseUser;
use Database\Factories\DatabaseFactory;
use Database\Factories\DatabaseUserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class DatabaseUserTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_a_database_user()
    {
        $this->browse(function (Browser $browser) {
            $database = DatabaseFactory::new()->forServer($this->server)->create();

            $this->assertEquals(0, $this->server->databaseUsers()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->clickLink('Add User')
                ->waitForModal()
                ->type('name', 'my_user')
                ->type('password', 'my_password')
                ->check('databases[]', $database->id)
                ->press('Submit')
                ->waitForText('The database user will be created shortly');

            $this->assertCount(1, $this->server->databaseUsers);
            $this->assertEquals('my_user', $this->server->databaseUsers->first()->name);
            $this->assertTrue($this->server->databaseUsers->first()->databases->contains($database));

            Bus::assertDispatched(InstallDatabaseUser::class, function (InstallDatabaseUser $job) {
                return $job->databaseUser->is($this->server->databaseUsers->first());
            });
        });
    }

    /** @test */
    public function it_can_add_update_a_database_users_password()
    {
        $this->browse(function (Browser $browser) {
            $databaseUser = DatabaseUserFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->within('@users', function ($browser) {
                    $browser->click('tbody td');
                })
                ->waitForModal()
                ->type('password', 'my_password')
                ->press('Submit')
                ->waitForText('The database user will be updated shortly');

            Bus::assertDispatched(UpdateDatabaseUser::class, function (UpdateDatabaseUser $job) use ($databaseUser) {
                return $job->databaseUser->is($databaseUser)
                    && $job->password === 'my_password';
            });
        });
    }

    /** @test */
    public function it_can_grant_user_access_to_different_databases()
    {
        $this->browse(function (Browser $browser) {
            /** @var Database */
            $databaseA = DatabaseFactory::new()->forServer($this->server)->create();
            $databaseB = DatabaseFactory::new()->forServer($this->server)->create();
            $databaseC = DatabaseFactory::new()->forServer($this->server)->create();

            /** @var DatabaseUser */
            $databaseUser = DatabaseUserFactory::new()->forServer($this->server)->create();
            $databaseUser->databases()->attach($databaseA);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->within('@users', function ($browser) {
                    $browser->click('tbody td');
                })
                ->waitForModal()
                ->uncheck('databases[]', $databaseA->id)
                ->check('databases[]', $databaseB->id)
                ->press('Submit')
                ->waitForText('The database user will be updated shortly');

            $this->assertCount(1, $databaseUser->fresh()->databases);
            $this->assertTrue($databaseUser->fresh()->databases->contains($databaseB));

            Bus::assertDispatched(UpdateDatabaseUser::class, function (UpdateDatabaseUser $job) use ($databaseUser) {
                return $job->databaseUser->is($databaseUser)
                    && $job->password === null;
            });
        });
    }

    /** @test */
    public function it_can_delete_an_existing_database_user()
    {
        $this->browse(function (Browser $browser) {
            $databaseUser = DatabaseUserFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.databases.index', $this->server))
                ->within('@users', function ($browser) {
                    $browser->click('tbody td');
                })
                ->waitForModal()
                ->press('Delete Database')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The database user will be uninstalled')
                ->assertRouteIs('servers.databases.index', $this->server);

            $this->assertNotNull($databaseUser->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallDatabaseUser::class, function (UninstallDatabaseUser $job) use ($databaseUser) {
                return $job->databaseUser->is($databaseUser);
            });
        });
    }
}
