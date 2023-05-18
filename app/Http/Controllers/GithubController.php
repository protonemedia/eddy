<?php

namespace App\Http\Controllers;

use App\Provider;
use App\SourceControl\Entities\GitRepository;
use App\SourceControl\Github;
use App\SourceControl\ProviderFactory;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as GithubUser;
use ProtoneMedia\Splade\Facades\Toast;

class GithubController extends Controller
{
    /**
     * Redirects the user to the Github OAuth page.
     */
    public function redirect(GithubProvider $githubProvider): RedirectResponse
    {
        if ($this->user()->credentials()->where('provider', Provider::Github)->exists()) {
            Toast::warning(__('You already have a Github account connected.'));

            return to_route('credentials.index');
        }

        return $githubProvider->setScopes([
            'repo',
            'admin:public_key',
            'admin:repo_hook',
        ])->redirect();
    }

    /**
     * Handles the callback from Github.
     */
    public function callback(GithubProvider $githubProvider)
    {
        if ($this->user()->credentials()->where('provider', Provider::Github)->exists()) {
            Toast::warning(__('You already have a Github account connected.'));

            return to_route('credentials.index');
        }

        try {
            /** @var GithubUser */
            $user = $githubProvider->user();
        } catch (ClientException $e) {
            Toast::warning(__('Failed to connect to Github.'));

            return to_route('credentials.index');
        }

        if (! app()->makeWith(Github::class, ['token' => $user->token])->canConnect()) {
            Toast::warning(__('Failed to connect to Github.'));

            return to_route('credentials.index');
        }

        $this->user()->credentials()->create([
            'name' => Provider::Github->getDisplayName(),
            'provider' => Provider::Github,
            'credentials' => [
                'id' => $user->getId(),
                'token' => $user->token,
            ],
        ]);

        Toast::info(__('Successfully connected to Github.'));

        return to_route('credentials.index');
    }

    /**
     * Returns a list of all the repositories that the user has access to.
     */
    public function repositories(ProviderFactory $providerFactory)
    {
        $githubCredentials = $this->user()->githubCredentials;

        if (! $githubCredentials) {
            return [];
        }

        /** @var Github */
        $github = $providerFactory->forCredentials($githubCredentials);

        return Cache::remember("github_repositories.{$githubCredentials->id}", 5 * 60, function () use ($github) {
            /** @var Collection */
            $repositories = rescue(fn () => $github->findRepositories(), Collection::make(), false);

            return $repositories->mapWithKeys(function (GitRepository $repository) {
                return [$repository->url => $repository->name];
            })->all();
        });
    }
}
