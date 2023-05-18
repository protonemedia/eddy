<?php

namespace Tests\Browser;

use App\Jobs\AddSshKeyToServer;
use App\Jobs\RemoveSshKeyFromServer;
use Database\Factories\SshKeyFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Laravel\Dusk\Browser;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SshKeyServerTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_an_ssh_key_to_a_server()
    {
        $this->browse(function (Browser $browser) {
            /** @var SshKey */
            $sshKey = SshKeyFactory::new()->forUser($this->user)->create();

            $browser->loginAs($this->user)
                ->visit('/ssh-keys')
                ->press('Actions...')
                ->clickLink('Add To Servers')
                ->waitForModal()
                ->check('servers[]', $this->server->id)
                ->press('Submit')
                ->waitForText('The SSH Key will be added to the selected servers.');

            Bus::assertDispatched(AddSshKeyToServer::class, function (AddSshKeyToServer $job) use ($sshKey) {
                return $job->sshKey->is($sshKey) && $job->server->is($this->server);
            });
        });
    }

    /** @test */
    public function it_can_remove_an_ssh_key_from_a_server()
    {
        $this->browse(function (Browser $browser) {
            /** @var SshKey */
            $sshKey = SshKeyFactory::new()->forUser($this->user)->create();

            $browser->loginAs($this->user)
                ->visit('/ssh-keys')
                ->press('Actions...')
                ->clickLink('Remove From Servers')
                ->waitForModal()
                ->check('servers[]', $this->server->id)
                ->press('Submit')
                ->waitForText('The SSH Key will be removed from the selected servers.');

            Bus::assertDispatched(RemoveSshKeyFromServer::class, function (RemoveSshKeyFromServer $job) use ($sshKey) {
                return $job->publicKey === $sshKey->public_key && $job->server->is($this->server);
            });
        });
    }

    /** @test */
    public function it_can_delete_an_ssh_key_and_remove_it_from_all_servers()
    {
        $this->browse(function (Browser $browser) {
            /** @var SshKey */
            $sshKey = SshKeyFactory::new()->forUser($this->user)->create();

            $browser->loginAs($this->user)
                ->visit('/ssh-keys')
                ->press('Actions...')
                ->clickLink('Delete Key and Remove From Servers')
                ->waitForModal()
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The SSH Key will be deleted and removed from all servers')
                ->assertRouteIs('ssh-keys.index');

            $this->assertNull($sshKey->fresh());

            Bus::assertDispatched(RemoveSshKeyFromServer::class, function (RemoveSshKeyFromServer $job) use ($sshKey) {
                return $job->publicKey === $sshKey->public_key && $job->server->is($this->server);
            });
        });
    }
}
