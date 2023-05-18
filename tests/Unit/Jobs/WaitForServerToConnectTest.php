<?php

namespace Tests\Unit\Jobs;

use App\Infrastructure\ProviderFactory;
use App\Jobs\WaitForServerToConnect;
use App\Tasks\Whoami;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use Tests\TestCase;

class WaitForServerToConnectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_releases_the_job_when_the_ipv4_address_is_still_empty()
    {
        $server = ServerFactory::new()->waitingToConnect()->create();

        $provider = $this->mock(ProviderFactory::class);

        $provider->shouldReceive('forServer->getPublicIpv4OfServer')
            ->with($server->provider_id)
            ->once()
            ->andReturn(null);

        $job = new WaitForServerToConnect($server);
        $this->assertFalse($job->handle());
    }

    /** @test */
    public function it_releases_the_job_when_the_ipv4_address_is_present_but_it_cant_connect()
    {
        $server = ServerFactory::new()->waitingToConnect()->create();

        $provider = $this->mock(ProviderFactory::class);

        $provider->shouldReceive('forServer->getPublicIpv4OfServer')
            ->with($server->provider_id)
            ->once()
            ->andReturn('1.2.3.4');

        TaskRunner::fake([
            Whoami::class => ProcessOutput::make()->setExitCode(127),
        ]);

        $job = new WaitForServerToConnect($server);
        $this->assertFalse($job->handle());
        $this->assertEquals('1.2.3.4', $server->public_ipv4);
    }

    /** @test */
    public function it_succeeds_when_whoami_returns_root()
    {
        $server = ServerFactory::new()->waitingToConnect()->create([
            'public_ipv4' => '1.2.3.4',
        ]);

        TaskRunner::fake([
            Whoami::class => 'root',
        ]);

        $job = new WaitForServerToConnect($server);
        $this->assertTrue($job->handle());
    }
}
