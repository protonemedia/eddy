<?php

namespace App\Infrastructure;

use App\Infrastructure\Entities\Distribution;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\Region;
use App\Infrastructure\Entities\Server;
use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\ServerType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HetznerCloud implements ServerProvider, HasCredentials
{
    /**
     * The Hetzner Cloud API base URL.
     */
    private const API_URL = 'https://api.hetzner.cloud/v1/';

    /**
     * The Laravel HTTP client instance.
     */
    private PendingRequest $http;

    /**
     * Create a new Hetzner Cloud instance using the given authentication token.
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
     * Helper method to get all results from a HetznerCloud API using pagination.
     */
    private function getAll(string $uri, array $query = [], string $resource = null): Collection
    {
        $data = collect();

        $resource = $resource ?? Str::of($uri)->before('?')->explode('/')->last();

        $nextPage = 1;

        do {
            $response = $this->http->get(self::API_URL.$uri, $query);

            $json = $response->json();

            $data = $data->merge($json[$resource] ?? []);

            $nextPage = $json['meta']['pagination']['next_page'] ?? null;

            if ($nextPage) {
                $query['page'] = $nextPage;
            }
        } while ($nextPage);

        return $data;
    }

    /**
     * Check if the Hetzner Cloud client can connect to the API.
     */
    public function canConnect(): bool
    {
        try {
            return $this->findAvailableServerRegions()->isNotEmpty();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Map a HetznerCloud datacenter to a Region object.
     */
    private function mapDatacenter(array $datacenter): Region
    {
        return new Region($datacenter['id'], $datacenter['description']);
    }

    /**
     * Get all available server regions.
     *
     * @see https://docs.hetzner.cloud/#datacenters-get-all-datacenters
     */
    public function findAvailableServerRegions(): Collection
    {
        return $this->getAll('datacenters')
            ->map(fn ($datacenter) => $this->mapDatacenter($datacenter))
            ->sortBy('name')
            ->keyBy('id');
    }

    /**
     * Map a HetznerCloud server type to an Entities\ServerType.
     */
    public function mapServerType(array $serverType, string $datacenterName = null): ServerType
    {
        if ($datacenterName) {
            $datacenterName = Str::before($datacenterName, '-');
            $prices = Collection::make($serverType['prices'] ?? [])->firstWhere('location', $datacenterName);
        }

        $monthlyPriceAmount = $prices['price_monthly']['net'] ?? null;

        return new ServerType(
            $serverType['name'],
            $serverType['cores'],
            $serverType['memory'] * 1024,
            $serverType['disk'],
            ! is_null($monthlyPriceAmount) ? (int) ($monthlyPriceAmount * 100) : null,
            'EUR',
        );
    }

    /**
     * Get all available server types by region.
     *
     * @see https://docs.hetzner.cloud/#server-types-get-all-server-types
     */
    public function findAvailableServerTypesByRegion(string $regionId): Collection
    {
        $datacenter = $this->http->get(self::API_URL."datacenters/{$regionId}");

        $datacenterName = $datacenter->json('datacenter.name');
        $availableServerTypes = $datacenter->json('datacenter.server_types.available');
        $supportedServerTypes = $datacenter->json('datacenter.server_types.supported');

        return $this->getAll('server_types')
            ->where('architecture', 'x86')
            ->whereIn('id', $availableServerTypes)
            ->whereIn('id', $supportedServerTypes)
            ->map(fn (array $serverType) => $this->mapServerType($serverType, $datacenterName))
            ->sortBy->name
            ->keyBy->id;
    }

    /**
     * Map a Hetzner Cloud Image to an Entities\Image.
     *
     * @param  array  $image The Hetzner Cloud Image to map.
     * @return \App\Infrastructure\Entities\Image The mapped Image.
     */
    public function mapImage(array $image): Entities\Image
    {
        $distribution = match ($image['os_flavor']) {
            'ubuntu' => Distribution::Ubuntu,
            default => Distribution::Unknown,
        };

        $osFlavorVersion = "{$image['os_flavor']} {$image['os_version']}";

        $operatingSystem = match (true) {
            Str::startsWith($osFlavorVersion, 'ubuntu 22.04') => OperatingSystem::Ubuntu2204,

            default => OperatingSystem::Unknown,
        };

        return new Entities\Image((string) $image['id'], $distribution, $operatingSystem);
    }

    /**
     * Get all available server images by region.
     *
     * @see https://docs.hetzner.cloud/#images-get-all-images
     */
    public function findAvailableServerImagesByRegion(string $regionId): Collection
    {
        return $this->getAll('images', [
            'architecture' => 'x86',
            'status' => 'available',
            'type' => 'system',
        ])->map([$this, 'mapImage']);
    }

    /**
     * Find an SSH key by its public key.
     *
     * @see https://docs.hetzner.cloud/#ssh-keys-get-all-ssh-keys
     *
     * @return \App\Infrastructure\Entities\SshKey|null
     */
    public function findSshKeyByPublicKey(string $publicKey): ?Entities\SshKey
    {
        $key = $this->getAll('ssh_keys')
            ->first(fn ($key) => trim($key['public_key']) === trim($publicKey));

        if (! $key) {
            return null;
        }

        return new Entities\SshKey($key['id'], $key['public_key']);
    }

    /**
     * Create a new SSH key.
     *
     * @see https://docs.hetzner.cloud/#ssh-keys-create-an-ssh-key
     *
     * @return \App\Infrastructure\Entities\SshKey
     */
    public function createSshKey(string $publicKey): Entities\SshKey
    {
        $key = $this->http->post(self::API_URL.'ssh_keys', [
            'name' => config('app.name').Str::random(),
            'public_key' => $publicKey,
        ])->json('ssh_key');

        return new Entities\SshKey($key['id'], $key['public_key']);
    }

    /**
     * Create a new server.
     *
     * @see https://docs.hetzner.cloud/#servers-create-a-server
     *
     * @param  array  $sshKeyIds
     */
    public function createServer(string $name, string $regionId, string $typeId, string $imageId, array|string|Collection $sshKeyIds): string
    {
        return (string) $this->http->post(self::API_URL.'servers', [
            'name' => $name,
            'server_type' => $typeId,
            'location' => $regionId,
            'start_after_create' => true,
            'ssh_keys' => Collection::wrap($sshKeyIds)->map(fn ($sshKeyId) => (int) $sshKeyId)->unique()->values()->all(),
            'image' => $imageId,
            'user_data' => '',
            'volumes' => [],
            'automount' => false,
            'networks' => [],
        ])->json('server.id');
    }

    /**
     * Get a server by its ID.
     *
     * @see https://docs.hetzner.cloud/#servers-get-a-server
     */
    public function getServer(string $id): Server
    {
        $server = $this->http->get(self::API_URL."servers/{$id}")->json('server');

        $status = match ($server['status']) {
            'initializing' => ServerStatus::New,
            'running' => ServerStatus::Running,
            'off' => ServerStatus::Stopped,
            default => ServerStatus::Unknown,
        };

        return new Server(
            id: $server['id'],
            region: $this->mapDatacenter($server['datacenter']),
            type: $this->mapServerType($server['server_type']),
            image: $this->mapImage($server['image']),
            status: $status
        );
    }

    /**
     * Delete a server by its ID.
     *
     * @see https://docs.hetzner.cloud/#servers-delete-a-server
     */
    public function deleteServer(string $id): void
    {
        $this->http->delete(self::API_URL."servers/{$id}");
    }

    /**
     * Get the public IPv4 address of a server.
     *
     * @see https://docs.hetzner.cloud/#servers-get-a-server
     *
     * @return string
     */
    public function getPublicIpv4OfServer(string $id): ?string
    {
        return $this->http->get(self::API_URL."servers/{$id}")->json('server.public_net.ipv4.ip');
    }
}
