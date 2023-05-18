<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\DigitalOcean;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\Vagrant;
use App\Provider;
use Database\Factories\ServerFactory;
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
    public function it_resolves_a_vagrant_instance()
    {
        $factory = new ProviderFactory(new ProcessRunner);

        $server = ServerFactory::new()->provider(Provider::Vagrant)->provisioned()->create();

        $digitalOcean = $factory->forServer($server);

        $this->assertInstanceOf(Vagrant::class, $digitalOcean);
    }
}
