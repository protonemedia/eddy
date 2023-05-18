<?php

namespace App\Infrastructure;

use App\Infrastructure\Entities\Distribution;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\ServerStatus;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class DigitalOcean implements ServerProvider, HasCredentials
{
    /**
     * The DigitalOcean API base URL.
     */
    private const API_URL = 'https://api.digitalocean.com/v2/';

    /**
     * The Laravel HTTP client instance.
     */
    private PendingRequest $http;

    /**
     * Create a new DigitalOcean instance using the given authentication token.
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
     * Helper method to get all results from a DigitalOcean API using pagination.
     */
    private function getAll(string $uri, array $query = [], string $resource = null): Collection
    {
        $data = collect();
        $url = self::API_URL.$uri;

        $resource = $resource ?? Str::of($uri)->before('?')->explode('/')->last();

        $firstPage = true;

        do {
            $response = $firstPage ? $this->http->get($url, $query) : $this->http->get($url);

            $json = $response->json();

            $data = $data->merge($json[$resource] ?? []);

            $url = $json['links']['pages']['next'] ?? null;
            $firstPage = false;
        } while ($url !== null);

        return $data;
    }

    /**
     * Check if the DigitalOcean client can connect to the API.
     */
    public function canConnect(): bool
    {
        try {
            return $this->findAvailableServerRegions()->isNotEmpty();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Map a DigitalOcean region to an Entities\Region.
     *
     * @return \App\Infrastructure\Entities\Region
     */
    public function mapRegion(array $region): Entities\Region
    {
        return new Entities\Region($region['slug'], $region['name']);
    }

    /**
     * Get all available server regions.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#tag/Regions
     */
    public function findAvailableServerRegions(): Collection
    {
        $regions = $this->getAll('regions');

        return $regions
            ->filter(function (array $region) {
                return $region['available'] && ! empty($region['sizes']);
            })
            ->map([$this, 'mapRegion'])
            ->sortBy('name')
            ->keyBy('id');
    }

    /**
     * Map a DigitalOcean size to an Entities\ServerType.
     *
     * @return \App\Infrastructure\Entities\ServerType
     */
    public function mapSize(array $size): Entities\ServerType
    {
        $monthlyPriceAmount = $size['price_monthly'] ?? null;

        return new Entities\ServerType(
            $size['slug'],
            $size['vcpus'],
            $size['memory'],
            $size['disk'],
            ! is_null($monthlyPriceAmount) ? (int) ($monthlyPriceAmount * 100) : null,
            'USD'
        );
    }

    /**
     * Get all available server types by region.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#tag/Sizes
     */
    public function findAvailableServerTypesByRegion(string $regionId): Collection
    {
        $sizes = $this->getAll('sizes', ['region' => $regionId]);

        return $sizes
            ->filter(function (array $size) {
                return $size['available'];
            })
            ->map([$this, 'mapSize'])
            ->sortBy('name')
            ->keyBy('id');
    }

    /**
     * Map a DigitalOcean image to an Entities\Image.
     *
     * @return \App\Infrastructure\Entities\Image
     */
    public function mapImage(array $image): Entities\Image
    {
        $distribution = match ($image['distribution']) {
            'Ubuntu' => Distribution::Ubuntu,
            default => Distribution::Unknown,
        };

        $operatingSystem = match (true) {
            Str::startsWith($image['description'], 'Ubuntu 22.04') => OperatingSystem::Ubuntu2204,
            default => OperatingSystem::Unknown,
        };

        return new Entities\Image((string) $image['id'], $distribution, $operatingSystem);
    }

    /**
     * Get all available server images by region.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#tag/Images
     */
    public function findAvailableServerImagesByRegion(string $regionId): Collection
    {
        $images = $this->getAll('images', [
            'type' => 'distribution',
            'public' => true,
        ])->filter(function ($image) use ($regionId) {
            return in_array($regionId, $image['regions']);
        })->map([$this, 'mapImage']);

        return $images;
    }

    /**
     * Find an SSH key by its public key.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#operation/sshKeys_list
     *
     * @return \App\Infrastructure\Entities\SshKey|null
     */
    public function findSshKeyByPublicKey(string $publicKey): ?Entities\SshKey
    {
        $key = $this->getAll('account/keys', resource: 'ssh_keys')
            ->first(fn ($key) => trim($key['public_key']) === trim($publicKey));

        if (! $key) {
            return null;
        }

        return new Entities\SshKey((string) $key['id'], $key['public_key']);
    }

    /**
     * Create a new SSH key.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#operation/sshKeys_create
     *
     * @return \App\Infrastructure\Entities\SshKey
     */
    public function createSshKey(string $publicKey): Entities\SshKey
    {
        $response = $this->http->post(self::API_URL.'account/keys', [
            'name' => config('app.name').Str::random(),
            'public_key' => $publicKey,
        ]);

        $key = $response->json('ssh_key');

        return new Entities\SshKey((string) $key['id'], $key['public_key']);
    }

    /**
     * Create a new server.
     *
     * @see https://docs.digitalocean.com/reference/api/api-reference/#operation/droplets_create
     *
     * @param  array  $sshKeyIds
     */
    public function createServer(string $name, string $regionId, string $typeId, string $imageId, array|string|Collection $sshKeyIds): string
    {
        return (string) $this->http->post(self::API_URL.'droplets', [
            'name' => $name,
            'region' => $regionId,
            'size' => $typeId,
            'image' => $imageId,
            'backups' => false,
            'ipv6' => true,
            'ssh_keys' => Collection::wrap($sshKeyIds)->unique()->values()->all(),
            'monitoring' => true,
        ])->json('droplet.id');
    }

    /**
     * Get a server by its ID.
     *
     * @return \App\Infrastructure\Entities\Server
     */
    public function getServer(string $id): Entities\Server
    {
        $droplet = $this->http->get(self::API_URL."droplets/{$id}")->json('droplet');

        $status = match ($droplet['status']) {
            'new' => ServerStatus::New,
            'active' => ServerStatus::Running,
            'off' => ServerStatus::Stopped,
            'archive' => ServerStatus::Archived,
            default => ServerStatus::Unknown,
        };

        return new Entities\Server(
            id: (string) $droplet['id'],
            region: $this->mapRegion($droplet['region']),
            type: $this->mapSize($droplet['size']),
            image: $this->mapImage($droplet['image']),
            status: $status
        );
    }

    /**
     * Delete a server by its ID.
     */
    public function deleteServer(string $id): void
    {
        $this->http->delete(self::API_URL."droplets/{$id}");
    }

    /**
     * Get the public IPv4 address of a server.
     *
     * @return string
     */
    public function getPublicIpv4OfServer(string $id): ?string
    {
        $publicIpv4Network = $this->http->get(self::API_URL."droplets/{$id}")
            ->collect('droplet.networks.v4')
            ->first(function (array $network) {
                return $network['type'] === 'public';
            });

        return $publicIpv4Network['ip_address'] ?? null;
    }
}
