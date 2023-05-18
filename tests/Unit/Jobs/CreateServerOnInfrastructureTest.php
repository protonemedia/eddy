<?php

namespace Tests\Unit\Jobs;

use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\SshKey;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\ServerProvider;
use App\Jobs\CleanupFailedServerProvisioning;
use App\Jobs\CreateServerOnInfrastructure;
use App\Provider;
use Database\Factories\ServerFactory;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

class CreateServerOnInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_early_when_the_server_is_already_provisoned()
    {
        $server = ServerFactory::new()->provisioned()->create();

        $provider = $this->mock(ProviderFactory::class);
        $provider->shouldNotHaveBeenCalled();

        $job = new CreateServerOnInfrastructure($server);
        $job->handle();
    }

    /** @test */
    public function it_updates_the_status_to_starting_if_its_a_custom_provider()
    {
        $server = ServerFactory::new()->provider(Provider::CustomServer)->create();

        $provider = $this->mock(ProviderFactory::class);
        $provider->shouldNotHaveBeenCalled();

        $job = new CreateServerOnInfrastructure($server);
        $job->handle();

        $this->assertEquals(ServerStatus::Starting, $server->status);
    }

    /** @test */
    public function it_may_add_a_test_ssh_key_when_configured()
    {
        $server = ServerFactory::new()->notProvisioned()->create();

        $client = $this->mock(ServerProvider::class);

        config(['services.test_public_key' => 'public-key']);

        $client->shouldReceive('findSshKeyByPublicKey')
            ->with('public-key')
            ->andReturn(new SshKey('test-key', 'public-key'));

        $client->shouldReceive('findSshKeyByPublicKey')
            ->with($server->public_key)
            ->andReturn(new SshKey('server-key', $server->public_key));

        $job = new CreateServerOnInfrastructure($server);
        $this->assertEquals(['test-key', 'server-key'], $job->sshKeys($client));
    }

    /** @test */
    public function it_finds_the_existing_ssh_key_and_creates_the_server()
    {
        $server = ServerFactory::new()->notProvisioned()->create();

        $provider = $this->mock(ProviderFactory::class);

        $provider->shouldReceive('forServer->findSshKeyByPublicKey')
            ->with($server->public_key)
            ->andReturn($sshKey = new SshKey('1', 'public-key'));

        $provider->shouldReceive('forServer->createServer')
            ->with('test-server', 18, 20, 9, $sshKey->id)
            ->andReturn(2);

        $job = new CreateServerOnInfrastructure($server);
        $job->handle();

        $this->assertEquals(2, $server->provider_id);
        $this->assertEquals(ServerStatus::Starting, $server->status);
    }

    /** @test */
    public function it_creates_the_ssh_key_if_its_not_found()
    {
        $server = ServerFactory::new()->notProvisioned()->create();

        $provider = $this->mock(ProviderFactory::class);

        $provider->shouldReceive('forServer->findSshKeyByPublicKey')
            ->with($server->public_key)
            ->andReturnNull();

        $provider->shouldReceive('forServer->createSshKey')
            ->with($server->public_key)
            ->andReturn($sshKey = new SshKey('1', 'public-key'));

        $provider->shouldReceive('forServer->createServer')
            ->with('test-server', 18, 20, 9, $sshKey->id)
            ->andReturn(2);

        $job = new CreateServerOnInfrastructure($server);
        $job->handle();

        $this->assertEquals(2, $server->provider_id);
        $this->assertEquals(ServerStatus::Starting, $server->status);
    }

    /** @test */
    public function it_handles_a_failure()
    {
        Bus::fake();

        $server = ServerFactory::new()->notProvisioned()->create();

        $job = new CreateServerOnInfrastructure($server);
        $job->failed(new Exception('test'));

        Bus::assertDispatched(function (CleanupFailedServerProvisioning $job) use ($server) {
            return $job->server->is($server);
        });
    }

    /** @test */
    public function it_sends_the_hetzner_error_code_to_the_cleanup_job()
    {
        Bus::fake();

        $server = ServerFactory::new()->notProvisioned()->provider(Provider::HetznerCloud)->create();

        $guzzleException = Mockery::mock(ClientException::class);
        $guzzleException->shouldReceive('getResponse->getBody')->andReturn(
            Mockery::mock(StreamInterface::class)
                ->shouldReceive('__toString')
                ->andReturn('{"error":{"message":"resource_unavailable"}}')
                ->getMock()
        );

        $job = new CreateServerOnInfrastructure($server);
        $job->failed($guzzleException);

        Bus::assertDispatched(function (CleanupFailedServerProvisioning $job) use ($server) {
            $this->assertEquals('The requested resource is currently unavailable', $job->errorMessage);

            return $job->server->is($server);
        });
    }
}
