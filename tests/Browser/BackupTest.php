<?php

namespace Tests\Browser;

use App\Jobs\InstallBackup;
use App\Jobs\UninstallBackup;
use App\Models\Cron;
use App\Models\Disk;
use Database\Factories\BackupFactory;
use Database\Factories\DatabaseFactory;
use Database\Factories\DiskFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class BackupTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    protected Disk $disk;

    protected Collection $databases;

    public function setUp(): void
    {
        parent::setUp();

        $this->disk = DiskFactory::new()->forUser($this->user)->create();
        $this->databases = DatabaseFactory::new()->forServer($this->server)->count(2)->create();
    }

    /** @test */
    public function it_can_add_a_backup_with_a_predefined_frequency()
    {
        $this->assertEquals(0, $this->server->backups()->count());

        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(route('servers.backups.index', $this->server))
                ->clickLink('Add Backup')
                ->waitForModal()
                ->type('name', 'Test Backup')
                ->select('disk_id', $this->disk->id)
                ->select('databases', $this->databases->pluck('id'))
                ->type('include_files', '/home/eddy/website')
                ->radio('frequency', '* * * * *')
                ->press('Deploy')
                ->waitForText('The backup will be installed shortly');
        });

        $this->assertCount(1, $this->server->backups);
        $backup = $this->server->backups()->first();

        Bus::assertDispatched(InstallBackup::class, function (InstallBackup $job) use ($backup) {
            return $job->backup->is($backup);
        });

        $this->assertEquals('Test Backup', $backup->name);
        $this->assertEquals($this->disk->id, $backup->disk_id);
        $this->assertEquals(14, $backup->retention);
        $this->assertEquals('* * * * *', $backup->cron_expression);
        $this->assertEquals(['/home/eddy/website'], $backup->include_files->toArray());
        $this->assertCount(2, $backup->databases);
    }

    /** @test */
    public function it_can_add_a_backup_with_a_custom_frequency()
    {
        $this->assertEquals(0, $this->server->backups()->count());

        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(route('servers.backups.index', $this->server))
                ->clickLink('Add Backup')
                ->waitForModal()
                ->type('name', 'Test Backup')
                ->select('disk_id', $this->disk->id)
                ->select('databases', $this->databases->pluck('id'))
                ->radio('frequency', 'custom')
                ->type('custom_expression', '1 2 3 4 5')
                ->press('Deploy')
                ->waitForText('The backup will be installed shortly');
        });

        $this->assertCount(1, $this->server->backups);

        $backup = $this->server->backups()->first();

        $this->assertEquals('1 2 3 4 5', $backup->cron_expression);
    }

    /** @test */
    public function it_can_update_an_existing_backup()
    {
        $this->browse(function (Browser $browser) {
            /** @var Cron */
            $backup = BackupFactory::new()
                ->forDatabases($this->databases)
                ->forDisk($this->disk)
                ->forServer($this->server)
                ->createdByUser($this->user)
                ->create();

            $newDatabase = DatabaseFactory::new()->forServer($this->server)->create();
            $newDisk = DiskFactory::new()->forUser($this->user)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.backups.index', $this->server))
                ->click('tbody td')
                ->waitForRoute('servers.backups.show', [$this->server, $backup])
                ->clickLink('Edit')
                ->waitForModal()
                ->clearAndType('name', 'Test Backup')
                ->select('disk_id', $newDisk->id)
                ->select('databases', $newDatabase->id)
                ->radio('frequency', 'custom')
                ->clearAndType('retention', '30')
                ->clearAndType('custom_expression', '1 2 3 4 5')
                ->check('notification_on_failure')
                ->check('notification_on_success')
                ->clearAndType('notification_email', 'notifications@example.com')
                ->press('Submit')
                ->waitForText('The backup will be deployed shortly');

            $backup = $this->server->backups()->first();

            Bus::assertDispatched(InstallBackup::class, function (InstallBackup $job) use ($backup) {
                return $job->backup->is($backup);
            });

            $this->assertEquals('Test Backup', $backup->name);
            $this->assertEquals($newDisk->id, $backup->disk_id);
            $this->assertEquals(30, $backup->retention);
            $this->assertEquals('1 2 3 4 5', $backup->cron_expression);
            $this->assertEquals('notifications@example.com', $backup->notification_email);
            $this->assertCount(3, $backup->databases);
            $this->assertTrue($backup->notification_on_failure);
            $this->assertTrue($backup->notification_on_success);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_backup()
    {
        $this->browse(function (Browser $browser) {
            /** @var Cron */
            $backup = BackupFactory::new()
                ->forDatabases($this->databases)
                ->forDisk($this->disk)
                ->forServer($this->server)
                ->createdByUser($this->user)
                ->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.backups.index', $this->server))
                ->click('tbody td')
                ->waitForRoute('servers.backups.show', [$this->server, $backup])
                ->clickLink('Edit')
                ->waitForModal()
                ->press('Delete Backup')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The Backup will be uninstalled')
                ->assertRouteIs('servers.backups.index', $this->server);

            $this->assertNotNull($backup->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallBackup::class, function (UninstallBackup $job) use ($backup) {
                return $job->backup->is($backup);
            });
        });
    }
}
