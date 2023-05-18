<?php

namespace Tests\Unit\SourceControl;

use App\Provider;
use App\SourceControl\Github;
use App\SourceControl\ProviderFactory;
use Database\Factories\CredentialsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resolves_a_github_client()
    {
        $factory = new ProviderFactory;

        $credentials = CredentialsFactory::new()->provider(Provider::Github)->create();

        $github = $factory->forCredentials($credentials);

        $this->assertInstanceOf(Github::class, $github);
    }
}
