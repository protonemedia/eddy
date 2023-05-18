<?php

namespace App\SourceControl;

use App\SourceControl\Entities\GitRepository;
use Github\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class Github
{
    /**
     * The Github API base URL.
     */
    private const API_URL = 'https://api.github.com/';

    /**
     * The Laravel HTTP client instance.
     */
    private PendingRequest $http;

    /**
     * Create a new Github instance using the given authentication token.
     */
    public function __construct(string $token)
    {
        $this->http = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->throw();
    }

    /**
     * Check if the Github client can connect to the API.
     */
    public function canConnect(): bool
    {
        try {
            return $this->http->get(self::API_URL.'user')->successful();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get all repositories for the current user.
     */
    public function findRepositories(): Collection
    {
        $data = collect();

        $url = self::API_URL.'user/repos';

        $query = [
            'type' => 'all',
            'per_page' => 100,
            'page' => 1,
        ];

        $firstPage = true;

        do {
            $result = $firstPage ? $this->http->get($url, $query) : $this->http->get($url);

            $firstPage = false;

            $items = $result->json() ?? [];

            $data = $data->merge($items);

            $links = Str::of($result->header('Link'))->explode(', ');

            $nextPageLink = $links->first(fn (string $link) => Str::of($link)->contains('rel="next"'));

            $url = Str::of($nextPageLink)->between('<', '>')->toString() ?: null;
        } while (! empty($items) && $url !== null);

        return $data->map(function (array $repository) {
            return new GitRepository(
                name: $repository['full_name'],
                url: $repository['private'] ? $repository['ssh_url'] : $repository['clone_url']
            );
        })
            ->sortBy(fn (GitRepository $repository) => $repository->name, SORT_STRING | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Add a new SSH key to the current user's account.
     */
    public function addKey(string $title, string $key): bool
    {
        $this->http->post(self::API_URL.'user/keys', [
            'title' => $title,
            'key' => $key,
        ]);

        return true;
    }
}
