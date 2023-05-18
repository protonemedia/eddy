<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\Entities\Distribution;
use App\Infrastructure\Entities\Image;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Entities\ServerType;
use App\Infrastructure\HetznerCloud;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HetznerCloudTest extends TestCase
{
    /** @test */
    public function it_knows_if_it_cant_connect()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/datacenters' => Http::response([], 403),
        ]);

        $this->assertFalse((new HetznerCloud(''))->canConnect());
    }

    /** @test */
    public function it_can_check_if_it_can_connect()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/datacenters' => [
                'datacenters' => [
                    [
                        'id' => 1,
                        'name' => 'nbg1-dc3',
                        'description' => 'Nuremberg 1 DC 3',
                        'location' => [
                            'city' => 'Nuremberg',
                            'country' => 'DE',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue((new HetznerCloud(''))->canConnect());
    }

    /** @test */
    public function it_has_available_server_regions()
    {
        $datacenters = [
            [
                'id' => 1,
                'name' => 'nbg1-dc3',
                'description' => 'Nuremberg 1 DC 3',
                'location' => [
                    'city' => 'Nuremberg',
                    'country' => 'DE',
                    'latitude' => 49.4521,
                    'longitude' => 11.0767,
                ],
            ],
            [
                'id' => 2,
                'name' => 'fsn1-dc8',
                'description' => 'Falkenstein 1 DC 8',
                'location' => [
                    'city' => 'Falkenstein',
                    'country' => 'DE',
                    'latitude' => 50.5667,
                    'longitude' => 12.5167,
                ],
            ],
        ];

        Http::fake([
            'https://api.hetzner.cloud/v1/datacenters' => [
                'datacenters' => $datacenters,
                'meta' => [
                    'pagination' => [
                        'next_page' => 2,
                    ],
                ],
            ],
            'https://api.hetzner.cloud/v1/datacenters?page=2' => [
                'datacenters' => [],
                'meta' => [
                    'pagination' => [
                        'next_page' => null,
                    ],
                ],
            ],
        ]);

        $result = (new HetznerCloud(''))->findAvailableServerRegions();

        $this->assertCount(2, $result);
        $this->assertTrue($result->has('1'));
        $this->assertTrue($result->has('2'));
        $this->assertSame('Nuremberg 1 DC 3', $result->get('1')->name);
        $this->assertSame('Falkenstein 1 DC 8', $result->get('2')->name);
    }

    /** @test */
    public function it_has_available_server_types_by_region()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/datacenters/1' => [
                'datacenter' => [
                    'id' => 1,
                    'name' => 'nbg1-dc3',
                    'server_types' => [
                        'available' => [
                            1, 2, 3,
                        ],
                        'supported' => [
                            2, 3, 4,
                        ],
                    ],
                ],
            ],
            'https://api.hetzner.cloud/v1/server_types' => [
                'server_types' => [
                    [
                        'id' => 1,
                        'architecture' => 'x86',
                    ],
                    [
                        'id' => 2,
                        'name' => 'cx11',
                        'description' => 'CX11',
                        'cores' => 1,
                        'memory' => 2.0,
                        'disk' => 20,
                        'deprecated' => null,
                        'prices' => [
                            [
                                'location' => 'fsn1',
                                'price_monthly' => [
                                    'net' => '1.7700000000',
                                ],
                            ],
                            [
                                'location' => 'nbg1',
                                'price_monthly' => [
                                    'net' => '3.2900000000',
                                ],
                            ],
                        ],
                        'storage_type' => 'local',
                        'cpu_type' => 'shared',
                        'architecture' => 'x86',
                    ],
                    [
                        'id' => 3,
                        'architecture' => 'arm',
                    ],
                    [
                        'id' => 4,
                        'architecture' => 'x86',
                    ],
                ],
            ],
        ]);

        $serverTypes = (new HetznerCloud(''))->findAvailableServerTypesByRegion('1');

        $this->assertCount(1, $serverTypes);

        /** @var ServerType $serverType */
        $serverType = $serverTypes->first();

        $this->assertEquals('cx11', $serverType->id);
        $this->assertEquals(1, $serverType->cpuCores);
        $this->assertEquals(2048, $serverType->memoryInMb);
        $this->assertEquals(20, $serverType->storageInGb);
        $this->assertEquals('cx11: 1 CPU, 2 GB RAM, 20 GB (€3.29/month)', $serverType->name);

        $this->assertEquals(329, $serverType->monthlyPriceAmount);
        $this->assertEquals('EUR', $serverType->monthlyPriceCurrency);
    }

    /** @test */
    public function it_has_available_server_images()
    {
        $images = [
            [
                'id' => 3,
                'type' => 'system',
                'status' => 'available',
                'name' => 'centos-7',
                'description' => 'CentOS 7',
                'image_size' => null,
                'disk_size' => 5,
                'created' => '2018-01-15T11:34:45+00:00',
                'created_from' => null,
                'bound_to' => null,
                'os_flavor' => 'centos',
                'os_version' => '7',
                'rapid_deploy' => true,
                'protection' => [
                    'delete' => false,
                ],
                'deprecated' => null,
                'labels' => [],
                'deleted' => null,
                'architecture' => 'x86',
            ],
            [
                'id' => 67794396,
                'type' => 'system',
                'status' => 'available',
                'name' => 'ubuntu-22.04',
                'description' => 'Ubuntu 22.04',
                'image_size' => null,
                'disk_size' => 5,
                'created' => '2022-04-21T13:32:38+00:00',
                'created_from' => null,
                'bound_to' => null,
                'os_flavor' => 'ubuntu',
                'os_version' => '22.04',
                'rapid_deploy' => true,
                'protection' => [
                    'delete' => false,
                ],
                'deprecated' => null,
                'labels' => [],
                'deleted' => null,
                'architecture' => 'x86',
            ],
        ];

        Http::fake([
            'https://api.hetzner.cloud/v1/images?architecture=x86&status=available&type=system' => [
                'images' => $images,

            ],
        ]);

        $images = (new HetznerCloud(''))->findAvailableServerImagesByRegion('2');

        $this->assertCount(2, $images);

        /** @var Image $cent */
        $cent = $images->first();

        $this->assertEquals(3, $cent->id);
        $this->assertEquals(Distribution::Unknown, $cent->distribution);
        $this->assertEquals(OperatingSystem::Unknown, $cent->operatingSystem);

        /** @var Image $ubuntu */
        $ubuntu = $images->last();

        $this->assertEquals(67794396, $ubuntu->id);
        $this->assertEquals(Distribution::Ubuntu, $ubuntu->distribution);
        $this->assertEquals(OperatingSystem::Ubuntu2204, $ubuntu->operatingSystem);
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
            'https://api.hetzner.cloud/v1/ssh_keys' => [
                'ssh_keys' => $sshKeys,
            ],
        ]);

        $sshKey = (new HetznerCloud(''))->findSshKeyByPublicKey('my-public-key');

        $this->assertNotNull($sshKey);
        $this->assertEquals(2, $sshKey->id);
        $this->assertEquals('my-public-key', $sshKey->publicKey);
    }

    /** @test */
    public function it_can_create_ssh_key()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/ssh_keys' => function (Request $request) {
                $this->assertEquals('my-public-key', $request['public_key']);

                return Http::response(['ssh_key' => ['id' => 1, 'public_key' => 'my-public-key']]);
            },
        ]);

        $sshKey = (new HetznerCloud(''))->createSshKey('my-public-key');

        $this->assertEquals(1, $sshKey->id);
        $this->assertEquals('my-public-key', $sshKey->publicKey);
    }

    /** @test */
    public function it_can_create_server()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/servers' => function (Request $request) {
                $this->assertEquals('my-server', $request['name']);
                $this->assertEquals('1', $request['location']);
                $this->assertEquals('2', $request['server_type']);
                $this->assertEquals('3', $request['image']);
                $this->assertEquals([1], $request['ssh_keys']);
                $this->assertTrue($request['start_after_create']);

                return Http::response(['server' => ['id' => 1]]);
            },
        ]);

        $server = (new HetznerCloud(''))->createServer(
            'my-server',
            '1',
            '2',
            '3',
            [1],
        );

        $this->assertEquals(1, $server);
    }

    /** @test */
    public function it_can_get_server()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/servers/1' => [
                'server' => [
                    'id' => 1,
                    'name' => 'my-server',
                    'status' => 'running',
                    'datacenter' => [
                        'id' => 1,
                        'description' => 'Nuremberg 1 DC 3',
                    ],
                    'server_type' => [
                        'id' => 2,
                        'name' => 'cx11',
                        'description' => 'CX11',
                        'cores' => 1,
                        'memory' => 2.0,
                        'disk' => 20,
                        'deprecated' => null,
                        'storage_type' => 'local',
                        'cpu_type' => 'shared',
                        'architecture' => 'x86',
                    ],
                    'image' => [
                        'id' => 67794396,
                        'os_flavor' => 'ubuntu',
                        'os_version' => '22.04',
                    ],
                ],
            ],
        ]);

        $server = (new HetznerCloud(''))->getServer(1);

        $this->assertEquals(1, $server->id);
        $this->assertEquals('Nuremberg 1 DC 3', $server->region->name);
        $this->assertEquals(Distribution::Ubuntu, $server->image->distribution);
        $this->assertEquals(2048, $server->type->memoryInMb);
        $this->assertEquals(1, $server->type->cpuCores);
        $this->assertEquals(20, $server->type->storageInGb);
        $this->assertEquals(ServerStatus::Running, $server->status);
    }

    /** @test */
    public function it_can_delete_server()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/servers/1' => Http::response([], 204),
        ]);

        $this->assertNull((new HetznerCloud(''))->deleteServer(1));
    }

    /** @test */
    public function it_can_get_public_ipv4_of_server()
    {
        Http::fake([
            'https://api.hetzner.cloud/v1/servers/1' => [
                'server' => [
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.4',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals('1.2.3.4', (new HetznerCloud(''))->getPublicIpv4OfServer(1));
    }
}
