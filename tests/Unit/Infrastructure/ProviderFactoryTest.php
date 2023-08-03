<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\DigitalOcean;
use App\Infrastructure\HetznerCloud;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\Vagrant;
use App\Provider;
use Database\Factories\ServerFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;
use Tests\TestCase;

class ProviderFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resolves_a_digital_ocean_instance()
    {
        $factory = new ProviderFactory(new ProcessRunner);

        $server = ServerFactory::new()->provider(Provider::DigitalOcean)->provisioned()->create();

        $digitalOcean = $factory->forServer($server);

        $this->assertInstanceOf(DigitalOcean::class, $digitalOcean);
    }

    /** @test */
    public function it_resolves_a_hetzner_cloud_instance()
    {
        $factory = new ProviderFactory(new ProcessRunner);

        $server = ServerFactory::new()->provider(Provider::HetznerCloud)->provisioned()->create();

        $hetznerCloud = $factory->forServer($server);

        $this->assertInstanceOf(HetznerCloud::class, $hetznerCloud);
    }

    /** @test */
    public function it_throws_an_exception_when_the_credentials_are_missing()
    {
        $factory = new ProviderFactory(new ProcessRunner);

        $server = ServerFactory::new()->provider(Provider::HetznerCloud)->provisioned()->create();
        $server->credentials->delete();
        $server->refresh();

        try {
            $hetznerCloud = $factory->forServer($server);
        } catch (Exception $e) {
            return $this->assertEquals('No credentials found', $e->getMessage());
        }

        $this->fail('Expected an exception to be thrown');
    }

    /** @test */
    public function it_resolves_a_vagrant_instance()
    {
        $factory = new ProviderFactory(new ProcessRunner);

        $server = ServerFactory::new()->provider(Provider::Vagrant)->provisioned()->create();

        $digitalOcean = $factory->forServer($server);

        $this->assertInstanceOf(Vagrant::class, $digitalOcean);
    }
}
