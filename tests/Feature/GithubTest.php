<?php

namespace Tests\Feature;

use App\Models\User;
use App\Provider;
use App\SourceControl\Entities\GitRepository;
use App\SourceControl\Github;
use App\SourceControl\ProviderFactory;
use Database\Factories\CredentialsFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as GithubUser;
use Mockery;
use Tests\TestCase;

class GithubTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirect(): void
    {
        // Create a user with no Github credentials.
        $user = UserFactory::new()->withPersonalTeam()->create();

        // Mock the Socialite provider and set it to return a fake redirect response.
        $provider = Mockery::mock(GithubProvider::class);
        $provider->shouldReceive('setScopes')->with(['repo', 'admin:public_key', 'admin:repo_hook'])->andReturn($provider);
        $provider->shouldReceive('redirect')->andReturn(new RedirectResponse('https://example.com'));

        // Swap the Socialite instance with our mock.
        app()->bind(GithubProvider::class, fn () => $provider);

        // Make the request to the controller.
        $response = $this->actingAs($user)->get(route('github.redirect'));

        // Assert that the user was redirected to the expected URL.
        $response->assertRedirect('https://example.com');
    }

    /** @test */
    public function it_redirects_to_credentials_index_if_user_already_has_github_credentials()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        CredentialsFactory::new()->github()->forUser($user)->create();

        $response = $this->actingAs($user)
            ->get(route('github.callback'));

        $response->assertRedirect(route('credentials.index'));
    }

    /** @test */
    public function it_will_save_the_github_token_to_the_database()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $token = 'test_token';
        $githubUser = new GithubUser;
        $githubUser->setToken($token);

        $githubProvider = $this->mock(GithubProvider::class, function ($mock) use ($githubUser) {
            $mock->shouldReceive('user')->andReturn($githubUser);
        });

        $github = $this->mock(Github::class, function ($mock) {
            $mock->shouldReceive('canConnect')->once()->andReturn(true);
        });

        $this->app->bind(Github::class, fn () => $github);

        $response = $this->actingAs($user)->get(route('github.callback', [], false));

        $response->assertRedirect(route('credentials.index'));

        $this->assertDatabaseHas('credentials', [
            'user_id' => $user->id,
            'name' => 'Github',
            'provider' => Provider::Github,
        ]);

        $this->assertEquals($token, $user->credentials()->where('name', 'Github')->first()->credentials['token']);
    }

    /** @test */
    public function it_may_fail_to_connect_to_github()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $token = 'invalid_token';
        $githubUser = new GithubUser;
        $githubUser->setToken($token);

        $githubProvider = $this->mock(GithubProvider::class, function ($mock) use ($githubUser) {
            $mock->shouldReceive('user')->andReturn($githubUser);
        });

        $github = $this->mock(Github::class, function ($mock) {
            $mock->shouldReceive('canConnect')->once()->andReturn(false);
        });

        $this->app->bind(Github::class, fn () => $github);

        $response = $this->actingAs($user)->get(route('github.callback', [], false));

        $response->assertRedirect(route('credentials.index'));

        $this->assertDatabaseMissing('credentials', [
            'user_id' => $user->id,
            'provider' => Provider::Github,
        ]);
    }

    /** @test */
    public function it_returns_empty_array_when_user_does_not_have_github_credentials()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->get(route('github.repositories'));

        $response->assertOk();
        $this->assertEmpty($response->json());
    }

    /** @test */
    public function it_returns_cached_github_repositories_if_available()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();

        $credentials = CredentialsFactory::new()->github()->forUser($user)->create();

        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(fn ($key) => $key === "github_repositories.{$credentials->id}")
            ->andReturn(['cached_repository']);

        $response = $this->actingAs($user)->get(route('github.repositories'));

        $response->assertOk();
        $this->assertSame(['cached_repository'], $response->json());
    }

    /** @test */
    public function it_returns_github_repositories()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $credentials = CredentialsFactory::new()->github()->forUser($user)->create();

        $repositories = collect([
            new GitRepository(
                'user/repo1',
                'https://github.com/user/repo1.git'
            ),
            new GitRepository(
                'user/repo2',
                'https://github.com/user/repo2.git',
            ),
        ]);

        $github = $this->mock(Github::class, function ($mock) use ($repositories) {
            $mock->shouldReceive('findRepositories')->once()->andReturn($repositories);
        });

        $factoryMock = Mockery::mock(ProviderFactory::class);
        $factoryMock->shouldReceive('forCredentials')
            ->andReturn($github);

        $this->app->bind(ProviderFactory::class, fn () => $factoryMock);

        $response = $this->actingAs($user)->get(route('github.repositories'));

        $response->assertOk();
        $this->assertSame([
            'https://github.com/user/repo1.git' => 'user/repo1',
            'https://github.com/user/repo2.git' => 'user/repo2',
        ], $response->json());
    }
}
