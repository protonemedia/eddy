<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Factories\Dummies;
use Database\Factories\SshKeyFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SshKeyTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_add_ssh_key()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->sshKeys()->count());

            $browser->loginAs($user)
                ->visit('/ssh-keys')
                ->clickLink('Add SSH Key')
                ->waitForModal()
                ->type('name', 'My SSH Key')
                ->type('public_key', $publicKey = Dummies::ed25519KeyPair()->publicKey)
                ->press('Submit')
                ->waitForText('SSH Key added.');

            $this->assertCount(1, $user->sshKeys);

            $this->assertEquals('My SSH Key', $user->sshKeys->first()->name);
            $this->assertEquals($publicKey, $user->sshKeys->first()->public_key);
        });
    }

    /** @test */
    public function it_can_delete_ssh_key()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var SshKey */
            $sshKey = SshKeyFactory::new()->forUser($user)->create();

            $browser->loginAs($user)
                ->visit('/ssh-keys')
                ->waitForText($sshKey->name)
                ->clickLink('Actions...', 'button')
                ->clickLink('Delete Key', 'a')
                ->waitForModal()
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('SSH Key deleted.')
                ->assertRouteIs('ssh-keys.index');

            $this->assertNull($sshKey->fresh());
        });
    }
}
