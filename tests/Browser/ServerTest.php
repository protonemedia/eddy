<?php

namespace Tests\Browser;

use App\Infrastructure\Entities\ServerStatus;
use App\Jobs\CreateServerOnInfrastructure;
use App\Jobs\DeleteServerFromInfrastructure;
use App\Jobs\ProvisionServer;
use App\Jobs\WaitForServerToConnect;
use App\Models\Credentials;
use App\Models\Server;
use App\Models\SshKey;
use App\Models\Team;
use App\Models\User;
use App\Provider;
use App\Tasks\GenerateEd25519KeyPair;
use Database\Factories\CredentialsFactory;
use Database\Factories\ServerFactory;
use Database\Factories\SshKeyFactory;
use Database\Factories\TeamFactory;
use Database\Factories\UserFactory;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\DuskTestCase;

class ServerTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;

    /** @test */
    public function it_can_create_a_new_server()
    {
        $this->browse(function (Browser $browser) {
            TaskRunner::dontFake(GenerateEd25519KeyPair::class);

            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Credentials */
            $credentials = CredentialsFactory::new()->forUser($user)->vagrant()->create();

            /** @var SshKey */
            $sshKey = SshKeyFactory::new()->forUser($user)->create();

            $this->assertEquals(0, $user->currentTeam->servers()->count());

            $browser
                ->loginAs($user)
                ->visit(route('servers.index'))
                ->clickLink('New Server')
                ->waitForModal()
                ->type('name', 'My Server')
                ->select('credentials_id', $credentials->getKey())
                ->waitUsing(10, 100, function () use ($browser) {
                    return $browser->resolver->findOrFail('[data-value="localhost"]')->isDisplayed()
                        && $browser->resolver->findOrFail('[data-value="ubuntu-2204"]')->isDisplayed()
                        && $browser->resolver->findOrFail('[data-value="ubuntu-2204-1"]')->isDisplayed();
                })
                ->select('ssh_keys[]', $sshKey->getKey())
                ->press('Submit')
                ->waitForText('The server is currently being created');

            $this->assertEquals(1, $user->currentTeam->servers()->count());

            $server = $user->currentTeam->servers()->first();
            $browser->assertRouteIs('servers.show', $server);

            $this->assertEquals('My Server', $server->name);
            $this->assertEquals($credentials->getKey(), $server->credentials_id);
            $this->assertEquals(Provider::Vagrant, $server->provider);
            $this->assertEquals(ServerStatus::New, $server->status);
            $this->assertEquals('localhost', $server->region);
            $this->assertEquals('ubuntu-2204-1', $server->type);
            $this->assertEquals('ubuntu-2204', $server->image);

            $this->assertNull($server->provider_id);
            $this->assertNull($server->public_ipv4);

            $this->assertNotNull($server->public_key);
            $this->assertNotNull($server->private_key);
            $this->assertEquals(config('eddy.server_defaults.working_directory'), $server->working_directory);
            $this->assertEquals(config('eddy.server_defaults.ssh_port'), $server->ssh_port);
            $this->assertEquals(config('eddy.server_defaults.username'), $server->username);
            $this->assertNotNull($server->password);
            $this->assertNotNull($server->database_password);
            $this->assertEquals($user->getKey(), $server->created_by_user_id);

            Bus::assertChained([
                new CreateServerOnInfrastructure($server),
                new WaitForServerToConnect($server),
                new ProvisionServer($server, Collection::wrap($sshKey)),
            ]);
        });
    }

    /** @test */
    public function it_can_create_a_new_server_with_a_custom_provider_and_add_ssh_key_to_github()
    {
        $this->browse(function (Browser $browser) {
            TaskRunner::dontFake(GenerateEd25519KeyPair::class);

            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            CredentialsFactory::new()->forUser($user)->vagrant()->create();

            $github = CredentialsFactory::new()->forUser($user)->github()->create();

            $this->assertEquals(0, $user->currentTeam->servers()->count());

            $browser
                ->loginAs($user)
                ->visit(route('servers.index'))
                ->clickLink('New Server')
                ->waitForModal()
                ->check('custom_server')
                ->type('name', 'My Server')
                ->type('public_ipv4', '8.8.8.8')
                ->check('add_key_to_github')
                ->press('Submit')
                ->waitForText('The server is currently being created');

            $this->assertEquals(1, $user->currentTeam->servers()->count());

            $server = $user->currentTeam->servers()->first();
            $browser->assertRouteIs('servers.show', $server);

            $this->assertEquals('My Server', $server->name);
            $this->assertEquals('8.8.8.8', $server->public_ipv4);
            $this->assertEquals(Provider::CustomServer, $server->provider);
            $this->assertEquals(ServerStatus::New, $server->status);
            $this->assertNull($server->region);
            $this->assertNull($server->type);
            $this->assertNull($server->image);

            $this->assertNull($server->provider_id);

            $this->assertNotNull($server->public_key);
            $this->assertNotNull($server->private_key);
            $this->assertEquals(config('eddy.server_defaults.working_directory'), $server->working_directory);
            $this->assertEquals(config('eddy.server_defaults.ssh_port'), $server->ssh_port);
            $this->assertEquals(config('eddy.server_defaults.username'), $server->username);
            $this->assertNotNull($server->password);
            $this->assertNotNull($server->database_password);
            $this->assertEquals($user->getKey(), $server->created_by_user_id);
            $this->assertEquals($github->getKey(), $server->github_credentials_id);

            Bus::assertChained([
                new CreateServerOnInfrastructure($server),
                new WaitForServerToConnect($server),
                new ProvisionServer($server, Collection::make()),
            ]);
        });
    }

    /** @test */
    public function it_cant_create_a_server_with_credentials_that_are_bound_to_another_team()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();
            $personalTeam = $user->currentTeam;

            /** @var Team */
            $anotherTeam = TeamFactory::new()->create();
            $anotherTeam->users()->attach($user);

            $this->assertTrue($user->fresh()->currentTeam->is($personalTeam));

            $availableCredentials = CredentialsFactory::new()->forUser($user)->forTeam($personalTeam)->vagrant()->create();
            $unavailableCredentials = CredentialsFactory::new()->forUser($user)->forTeam($anotherTeam)->vagrant()->create();

            $browser
                ->loginAs($user)
                ->visit(route('servers.index'))
                ->clickLink('New Server')
                ->waitForModal()
                ->select('credentials_id', $availableCredentials->getKey());

            try {
                $browser->select('credentials_id', $unavailableCredentials->getKey());
            } catch (NoSuchElementException $e) {
                return;
            }

            $this->fail('The credentials should not be available');
        });
    }

    /** @test */
    public function it_can_delete_an_existing_server()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Server */
            $server = ServerFactory::new()->forTeam($user->currentTeam)->provisioned()->create();

            $browser
                ->loginAs($user)
                ->visit(route('servers.index'))
                ->click('tbody td')
                ->waitForRoute('servers.show', $server)
                ->press('Delete Server')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('Your server is being deleted')
                ->assertRouteIs('servers.index');

            $this->assertEquals(ServerStatus::Deleting, $server->fresh()->status);
            $this->assertNotNull($server->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(DeleteServerFromInfrastructure::class, function (DeleteServerFromInfrastructure $job) use ($server) {
                return $job->server->is($server);
            });
        });
    }
}
