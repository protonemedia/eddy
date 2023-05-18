<?php

use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\ServerType;
use App\Jobs\AddServerSshKeyToGithub;
use App\Jobs\CreateServerOnInfrastructure;
use App\Jobs\ProvisionServer;
use App\Jobs\WaitForServerToConnect;
use App\Models\Server;
use App\Provider;
use App\Server\PhpVersion;
use App\Tasks\Whoami;
use Database\Factories\CredentialsFactory;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_return_name_with_ip_returns_correct_value()
    {
        $server = Server::factory()->create([
            'name' => 'My Server',
            'public_ipv4' => '192.168.0.1',
        ]);

        $this->assertEquals('My Server (192.168.0.1)', $server->nameWithIp);
    }

    /** @test */
    public function it_can_return_provider_name_returns_correct_value()
    {
        $server = Server::factory()->create([
            'provider' => Provider::DigitalOcean,
        ]);

        $this->assertEquals('DigitalOcean', $server->providerName);
    }

    /** @test */
    public function it_can_return_status_name_returns_correct_value()
    {
        $server = Server::factory()->create([
            'status' => ServerStatus::Provisioning,
        ]);

        $this->assertEquals('Provisioning', $server->statusName);
    }

    /** @test */
    public function it_can_update_type_updates_server_type()
    {
        $serverType1 = new ServerType('t2.micro', 1, 1024, 30);
        $serverType2 = new ServerType('t3.small', 2, 2048, 60);

        /** @var Server */
        $server = Server::factory()->create([
            'type' => $serverType1->id,
            'cpu_cores' => 1,
            'memory_in_mb' => 1024,
            'storage_in_gb' => 30,
        ]);

        $server->updateType($serverType2);

        $this->assertEquals($serverType2->id, $server->type);
        $this->assertEquals($serverType2->cpuCores, $server->cpu_cores);
        $this->assertEquals($serverType2->memoryInMb, $server->memory_in_mb);
        $this->assertEquals($serverType2->storageInGb, $server->storage_in_gb);
    }

    /** @test */
    public function it_can_dispatch_and_create_provision_jobs()
    {
        Bus::fake();

        $server = Server::factory()->create();
        $sshKeys = new \Illuminate\Database\Eloquent\Collection([
            \App\Models\SshKey::factory()->create(),
        ]);

        $server->dispatchCreateAndProvisionJobs($sshKeys);

        Bus::assertChained([
            new CreateServerOnInfrastructure($server),
            new WaitForServerToConnect($server),
            new ProvisionServer($server, $sshKeys),
        ]);
    }

    /** @test */
    public function it_can_dispatch_and_create_provision_jobs_with_optional_github_credentials()
    {
        Bus::fake();

        $server = Server::factory()->create();
        $credentials = CredentialsFactory::new()->forUser($server->createdByUser)->github()->create();

        $sshKeys = new \Illuminate\Database\Eloquent\Collection([
            \App\Models\SshKey::factory()->create(),
        ]);

        $server = $server->fresh();
        $server->dispatchCreateAndProvisionJobs($sshKeys, $credentials);

        Bus::assertChained([
            new CreateServerOnInfrastructure($server),
            new WaitForServerToConnect($server),
            new ProvisionServer($server, $sshKeys),
            new AddServerSshKeyToGithub($server, $credentials),
        ]);
    }

    /** @test */
    public function it_cant_connect_if_it_doesnt_have_a_public_ipv4()
    {
        $server = Server::factory()->create(['public_ipv4' => null]);

        $this->assertFalse($server->canConnectOverSsh());
    }

    /** @test */
    public function it_cant_connect_if_whoami_task_does_not_succeed()
    {
        $server = Server::factory()->create(['public_ipv4' => '123.456.789.0']);

        TaskRunner::fake([
            Whoami::class => ProcessOutput::make('')->setExitCode(1),
        ]);

        $this->assertFalse($server->canConnectOverSsh());
    }

    /** @test */
    public function it_cant_connect_if_whoami_task_does_not_return_root_user()
    {
        $server = Server::factory()->create(['public_ipv4' => '123.456.789.0']);

        TaskRunner::fake([
            Whoami::class => 'not root',
        ]);

        $this->assertFalse($server->canConnectOverSsh());
    }

    /** @test */
    public function it_can_connect_if_whoami_task_succeeds_and_returns_root_user()
    {
        $server = Server::factory()->create(['public_ipv4' => '123.456.789.0']);

        TaskRunner::fake([
            Whoami::class => 'root',
        ]);

        $this->assertTrue($server->canConnectOverSsh());
    }

    /** @test */
    public function it_can_return_installed_php_versions_as_options_array()
    {
        // Create a server with PHP 7.4 and 8.0 installed
        $server = ServerFactory::new()->make([
            'installed_software' => [PhpVersion::Php81->value, PhpVersion::Php82->value],
        ]);

        // Check that the installed PHP versions are correct
        $this->assertEquals(
            [
                'php81' => 'PHP 8.1',
                'php82' => 'PHP 8.2',
            ],
            $server->installedPhpVersions()
        );
    }
}
