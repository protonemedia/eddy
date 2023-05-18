<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\DigitalOcean;
use App\Infrastructure\Entities\Distribution;
use App\Infrastructure\Entities\Image;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\ServerType;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DigitalOceanTest extends TestCase
{
    /** @test */
    public function it_knows_if_it_cant_connect()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/regions' => Http::response([], 403),
        ]);

        $this->assertFalse((new DigitalOcean(''))->canConnect());
    }

    /** @test */
    public function it_can_check_if_it_can_connect()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/regions' => [
                'regions' => [
                    ['slug' => 'nyc1', 'name' => 'New York 1', 'available' => true, 'sizes' => ['s-1vcpu-1gb']],
                ],
            ],
        ]);

        $this->assertTrue((new DigitalOcean(''))->canConnect());
    }

    /** @test */
    public function it_has_available_server_regions()
    {
        $regions = [
            ['slug' => 'nyc1', 'name' => 'New York 1', 'available' => true, 'sizes' => ['s-1vcpu-1gb']],
            ['slug' => 'nyc2', 'name' => 'New York 2', 'available' => false, 'sizes' => []],
            ['slug' => 'sfo1', 'name' => 'San Francisco 1', 'available' => true, 'sizes' => ['s-1vcpu-1gb']],
        ];

        Http::fake([
            'https://api.digitalocean.com/v2/regions' => [
                'regions' => $regions,
                'links' => [
                    'pages' => [
                        'next' => 'https://api.digitalocean.com/v2/regions?page=2',
                    ],
                ],
            ],
            'https://api.digitalocean.com/v2/regions?page=2' => [
                'regions' => [],
                'links' => [
                    'pages' => [
                        'next' => null,
                    ],
                ],
            ],
        ]);

        $result = (new DigitalOcean(''))->findAvailableServerRegions();

        $this->assertCount(2, $result);
        $this->assertTrue($result->has('nyc1'));
        $this->assertTrue($result->has('sfo1'));
        $this->assertSame('New York 1', $result->get('nyc1')->name);
        $this->assertSame('San Francisco 1', $result->get('sfo1')->name);
    }

    /** @test */
    public function it_has_available_server_types_by_region()
    {
        $servers = [
            ['slug' => 's-2vcpu-2gb', 'available' => true, 'vcpus' => '2', 'disk' => '20', 'memory' => 2048, 'regions' => ['nyc1', 'sfo1']],
            ['slug' => 's-4vcpu-4gb', 'available' => false, 'vcpus' => '4', 'disk' => '50', 'memory' => 4096, 'regions' => ['nyc1', 'sfo1']],
        ];

        Http::fake([
            'https://api.digitalocean.com/v2/sizes?region=sfo1' => [
                'sizes' => $servers,
                'links' => [
                    'pages' => [
                        'next' => null,
                    ],
                ],
            ],
        ]);

        $serverTypes = (new DigitalOcean(''))->findAvailableServerTypesByRegion('sfo1');

        $this->assertCount(1, $serverTypes);

        /** @var ServerType $serverType */
        $serverType = $serverTypes->first();

        $this->assertEquals('s-2vcpu-2gb', $serverType->id);
        $this->assertEquals(2, $serverType->cpuCores);
        $this->assertEquals(2048, $serverType->memoryInMb);
        $this->assertEquals(20, $serverType->storageInGb);
        $this->assertEquals('s-2vcpu-2gb: 2 CPU, 2 GB RAM, 20 GB', $serverType->name);
    }

    /** @test */
    public function it_has_available_server_images()
    {
        $images = [
            [
                'id' => '1',
                'distribution' => 'Ubuntu',
                'description' => 'Ubuntu 22.04 LTS',
                'regions' => ['nyc1', 'ams3'],
            ],
            [
                'id' => '2',
                'distribution' => 'Ubuntu',
                'description' => 'Ubuntu 22.04 LTS',
                'regions' => ['nyc1', 'ams3', 'sfo2'],
            ],
            [
                'id' => '3',
                'distribution' => 'Debian',
                'description' => 'Debian 10 x64',
                'regions' => ['nyc1', 'ams3', 'sfo2'],
            ],
        ];

        Http::fake([
            'https://api.digitalocean.com/v2/images?type=distribution&public=1' => [
                'images' => $images,
                'links' => [
                    'pages' => [
                        'next' => null,
                    ],
                ],
            ],
        ]);

        $images = (new DigitalOcean(''))->findAvailableServerImagesByRegion('sfo2');

        $this->assertCount(2, $images);

        /** @var Image $ubuntu */
        $ubuntu = $images->first();

        $this->assertEquals(2, $ubuntu->id);
        $this->assertEquals(Distribution::Ubuntu, $ubuntu->distribution);
        $this->assertEquals(OperatingSystem::Ubuntu2204, $ubuntu->operatingSystem);

        /** @var Image $debian */
        $cent = $images->last();

        $this->assertEquals(3, $cent->id);
        $this->assertEquals(Distribution::Unknown, $cent->distribution);
        $this->assertEquals(OperatingSystem::Unknown, $cent->operatingSystem);
    }

    /** @test */
    public function it_can_find_ssh_key_by_public_key()
    {
        $sshKeys = [
            [
                'id' => 1,
                'public_key' => 'ssh-rsa AAAAB3N',
            ],
            [
                'id' => 2,
                'public_key' => 'my-public-key',
            ],

        ];

        Http::fake([
            'https://api.digitalocean.com/v2/account/keys' => [
                'ssh_keys' => $sshKeys,
                'links' => [
                    'pages' => [
                        'next' => null,
                    ],
                ],
            ],
        ]);

        $sshKey = (new DigitalOcean(''))->findSshKeyByPublicKey('my-public-key');

        $this->assertNotNull($sshKey);
        $this->assertEquals(2, $sshKey->id);
        $this->assertEquals('my-public-key', $sshKey->publicKey);
    }

    /** @test */
    public function it_can_create_ssh_key()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/account/keys' => function (Request $request) {
                $this->assertEquals('my-public-key', $request['public_key']);

                return Http::response(['ssh_key' => ['id' => 1, 'public_key' => 'my-public-key']]);
            },
        ]);

        $sshKey = (new DigitalOcean(''))->createSshKey('my-public-key');

        $this->assertEquals(1, $sshKey->id);
        $this->assertEquals('my-public-key', $sshKey->publicKey);
    }

    /** @test */
    public function it_can_create_server()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/droplets' => function (Request $request) {
                $this->assertEquals('my-server', $request['name']);
                $this->assertEquals('sfo1', $request['region']);
                $this->assertEquals('s-2vcpu-2gb', $request['size']);
                $this->assertEquals('ubuntu-20-04-x64', $request['image']);
                $this->assertEquals([1], $request['ssh_keys']);

                return Http::response(['droplet' => ['id' => 1, 'name' => 'my-server']]);
            },
        ]);

        $server = (new DigitalOcean(''))->createServer(
            'my-server',
            'sfo1',
            's-2vcpu-2gb',
            'ubuntu-20-04-x64',
            [1],
        );

        $this->assertEquals(1, $server);
    }

    /** @test */
    public function it_can_get_server()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/droplets/1' => [
                'droplet' => [
                    'id' => 1,
                    'name' => 'my-server',
                    'status' => 'active',
                    'region' => [
                        'slug' => 'sfo1',
                        'name' => 'San Francisco 1',

                    ],
                    'size' => [
                        'slug' => 's-2vcpu-2gb',
                        'vcpus' => 2,
                        'memory' => 2048,
                        'disk' => 20,
                    ],

                    'image' => [
                        'distribution' => 'Ubuntu',
                        'description' => 'Ubuntu 22.04 LTS',
                        'id' => 1,
                    ],
                ],
            ],
        ]);

        $server = (new DigitalOcean(''))->getServer(1);

        $this->assertEquals(1, $server->id);
        $this->assertEquals('San Francisco 1', $server->region->name);
        $this->assertEquals(Distribution::Ubuntu, $server->image->distribution);
        $this->assertEquals(2048, $server->type->memoryInMb);
        $this->assertEquals(2, $server->type->cpuCores);
        $this->assertEquals(20, $server->type->storageInGb);
        $this->assertEquals(ServerStatus::Running, $server->status);
    }

    /** @test */
    public function it_can_delete_server()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/droplets/1' => Http::response([], 204),
        ]);

        $this->assertNull((new DigitalOcean(''))->deleteServer(1));
    }

    /** @test */
    public function it_can_get_public_ipv4_of_server()
    {
        Http::fake([
            'https://api.digitalocean.com/v2/droplets/1' => [
                'droplet' => [
                    'networks' => [
                        'v4' => [
                            [
                                'type' => 'public',
                                'ip_address' => '1.2.3.4',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals('1.2.3.4', (new DigitalOcean(''))->getPublicIpv4OfServer(1));
    }
}
