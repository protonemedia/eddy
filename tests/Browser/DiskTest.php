<?php

namespace Tests\Browser;

use App\FilesystemDriver;
use App\Models\User;
use Database\Factories\DiskFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;

class DiskTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;

    /** @test */
    public function it_can_add_an_s3_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->clickLink('Add Backup Disk')
                ->waitForModal()
                ->select('filesystem_driver', 's3')
                ->type('name', 'My S3 Disk')
                ->type('configuration.s3_bucket', 'my-bucket')
                ->type('configuration.s3_region', 'eu-west-1')
                ->type('configuration.s3_access_key', 'my-access-key')
                ->type('configuration.s3_secret_key', 'my-secret-key')
                ->press('Submit')
                ->waitForText('Backup disk created');

            $this->assertCount(1, $user->disks);

            $disk = $user->disks->first();

            $this->assertEquals('My S3 Disk', $disk->name);
            $this->assertEquals(FilesystemDriver::S3, $disk->filesystem_driver);
            $this->assertEquals('my-bucket', $disk->configuration['s3_bucket']);
            $this->assertEquals('eu-west-1', $disk->configuration['s3_region']);
            $this->assertEquals('my-access-key', $disk->configuration['s3_access_key']);
            $this->assertEquals('my-secret-key', $disk->configuration['s3_secret_key']);
        });
    }

    /** @test */
    public function it_can_add_an_s3_disk_with_a_custom_endpoint_and_path_style_endpoint()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->clickLink('Add Backup Disk')
                ->waitForModal()
                ->select('filesystem_driver', 's3')
                ->type('name', 'My S3 Disk')
                ->check('configuration.s3_custom_endpoint')
                ->check('configuration.s3_path_style_endpoint')
                ->type('configuration.s3_bucket', 'my-bucket')
                ->type('configuration.s3_region', 'eu-west-1')
                ->type('configuration.s3_access_key', 'my-access-key')
                ->type('configuration.s3_secret_key', 'my-secret-key')
                ->type('configuration.s3_endpoint', 'https://my-custom-endpoint.com')
                ->press('Submit')
                ->waitForText('Backup disk created');

            $this->assertCount(1, $user->disks);

            $disk = $user->disks->first();

            $this->assertEquals('My S3 Disk', $disk->name);
            $this->assertEquals(FilesystemDriver::S3, $disk->filesystem_driver);
            $this->assertEquals('my-bucket', $disk->configuration['s3_bucket']);
            $this->assertEquals('eu-west-1', $disk->configuration['s3_region']);
            $this->assertEquals('my-access-key', $disk->configuration['s3_access_key']);
            $this->assertEquals('my-secret-key', $disk->configuration['s3_secret_key']);
            $this->assertEquals('https://my-custom-endpoint.com', $disk->configuration['s3_endpoint']);
            $this->assertTrue($disk->configuration['s3_path_style_endpoint']);
        });
    }

    /** @test */
    public function it_can_edit_an_s3_disk_without_changing_the_secret()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->s3()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('name', 'My updated S3 Disk')
                ->clearAndType('configuration.s3_bucket', 'my-updated-bucket')
                ->clearAndType('configuration.s3_region', 'eu-west-2')
                ->clearAndType('configuration.s3_access_key', 'my-updated-access-key')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('My updated S3 Disk', $disk->name);
            $this->assertEquals('my-updated-bucket', $disk->configuration['s3_bucket']);
            $this->assertEquals('eu-west-2', $disk->configuration['s3_region']);
            $this->assertEquals('my-updated-access-key', $disk->configuration['s3_access_key']);
            $this->assertEquals('my-secret-key', $disk->configuration['s3_secret_key']);
        });
    }

    /** @test */
    public function it_can_change_the_secret_of_the_s3_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->s3()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('configuration.s3_secret_key', 'my-updated-secret-key')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('my-updated-secret-key', $disk->configuration['s3_secret_key']);
        });
    }

    /** @test */
    public function it_can_add_an_ftp_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->clickLink('Add Backup Disk')
                ->waitForModal()
                ->select('filesystem_driver', 'ftp')
                ->type('name', 'My FTP Disk')
                ->type('configuration.ftp_host', 'ftp.example.com')
                ->type('configuration.ftp_username', 'my-username')
                ->type('configuration.ftp_password', 'my-password')
                ->press('Submit')
                ->waitForText('Backup disk created');

            $this->assertCount(1, $user->disks);

            $disk = $user->disks->first();

            $this->assertEquals('My FTP Disk', $disk->name);
            $this->assertEquals(FilesystemDriver::FTP, $disk->filesystem_driver);
            $this->assertEquals('ftp.example.com', $disk->configuration['ftp_host']);
            $this->assertEquals('my-username', $disk->configuration['ftp_username']);
            $this->assertEquals('my-password', $disk->configuration['ftp_password']);
        });
    }

    /** @test */
    public function it_can_edit_a_ftp_disk_without_changing_the_password()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->ftp()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('name', 'My updated FTP Disk')
                ->clearAndType('configuration.ftp_host', 'updated-ftp.example.com')
                ->clearAndType('configuration.ftp_username', 'my-updated-username')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('My updated FTP Disk', $disk->name);
            $this->assertEquals('updated-ftp.example.com', $disk->configuration['ftp_host']);
            $this->assertEquals('my-updated-username', $disk->configuration['ftp_username']);
        });
    }

    /** @test */
    public function it_can_update_the_password_of_an_ftp_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->ftp()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('configuration.ftp_password', 'my-updated-password')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('my-updated-password', $disk->configuration['ftp_password']);
        });
    }

    /** @test */
    public function it_can_add_an_sftp_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->clickLink('Add Backup Disk')
                ->waitForModal()
                ->select('filesystem_driver', 'sftp')
                ->type('name', 'My SFTP Disk')
                ->type('configuration.sftp_host', 'sftp.example.com')
                ->type('configuration.sftp_username', 'my-username')
                ->type('configuration.sftp_password', 'my-password')
                ->press('Submit')
                ->waitForText('Backup disk created');

            $this->assertCount(1, $user->disks);

            $disk = $user->disks->first();

            $this->assertEquals('My SFTP Disk', $disk->name);
            $this->assertEquals(FilesystemDriver::SFTP, $disk->filesystem_driver);
            $this->assertEquals('sftp.example.com', $disk->configuration['sftp_host']);
            $this->assertEquals('my-username', $disk->configuration['sftp_username']);
            $this->assertEquals('my-password', $disk->configuration['sftp_password']);
            $this->assertFalse($disk->configuration['sftp_use_ssh_key']);
        });
    }

    /** @test */
    public function it_can_add_an_sftp_disk_without_a_password()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->clickLink('Add Backup Disk')
                ->waitForModal()
                ->select('filesystem_driver', 'sftp')
                ->type('name', 'My SFTP Disk')
                ->type('configuration.sftp_host', 'sftp.example.com')
                ->type('configuration.sftp_username', 'my-username')
                ->check('configuration.sftp_use_ssh_key')
                ->press('Submit')
                ->waitForText('Backup disk created');

            $this->assertCount(1, $user->disks);

            $disk = $user->disks->first();

            $this->assertEquals('My SFTP Disk', $disk->name);
            $this->assertEquals(FilesystemDriver::SFTP, $disk->filesystem_driver);
            $this->assertEquals('sftp.example.com', $disk->configuration['sftp_host']);
            $this->assertEquals('my-username', $disk->configuration['sftp_username']);
            $this->assertTrue($disk->configuration['sftp_use_ssh_key']);
            $this->assertNull($disk->configuration['sftp_password']);
        });
    }

    /** @test */
    public function it_can_edit_a_sftp_disk_without_changing_the_password()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->sftp()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('name', 'My updated SFTP Disk')
                ->clearAndType('configuration.sftp_host', 'updated-sftp.example.com')
                ->clearAndType('configuration.sftp_username', 'my-updated-username')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('My updated SFTP Disk', $disk->name);
            $this->assertEquals('updated-sftp.example.com', $disk->configuration['sftp_host']);
            $this->assertEquals('my-updated-username', $disk->configuration['sftp_username']);
        });
    }

    /** @test */
    public function it_can_update_the_password_of_an_sftp_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $disk = DiskFactory::new()->forUser($user)->sftp()->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->clearAndType('configuration.sftp_password', 'my-updated-password')
                ->press('Submit')
                ->waitForText('Backup disk updated');

            $disk = $disk->fresh();

            $this->assertEquals('my-updated-password', $disk->configuration['sftp_password']);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_disk()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Disk */
            $disk = DiskFactory::new()->forUser($user)->create();

            $browser
                ->loginAs($user)
                ->visit(route('disks.index'))
                ->click('tbody td')
                ->waitForModal()
                ->press('Delete Disk')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('Backup disk deleted')
                ->assertRouteIs('disks.index');

            $this->assertNull($disk->fresh());
        });
    }
}
