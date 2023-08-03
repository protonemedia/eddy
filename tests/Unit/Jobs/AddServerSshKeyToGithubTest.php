<?php

namespace Tests\Unit\Jobs;

use App\Jobs\AddServerSshKeyToGithub;
use App\SourceControl\Github;
use App\SourceControl\ProviderFactory;
use Database\Factories\CredentialsFactory;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AddServerSshKeyToGithubTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_the_public_key()
    {
        $server = ServerFactory::new()->provisioned()->create();
        $credentials = CredentialsFactory::new()->forUser($server->createdByUser)->github()->create();

        $github = Mockery::mock(Github::class);
        $github->shouldReceive('addKey')->once()->withArgs([
            "{$server->name} (added by ".config('app.name').')',
            $server->user_public_key,
        ]);

        $providerFactory = Mockery::mock(ProviderFactory::class);
        $providerFactory->shouldReceive('forCredentials')->with($credentials)->andReturn($github);

        $job = new AddServerSshKeyToGithub($server, $credentials);
        $job->handle($providerFactory);
    }
}
