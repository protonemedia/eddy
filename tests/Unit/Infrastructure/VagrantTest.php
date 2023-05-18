<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\Entities\ServerStatus;
use App\Infrastructure\Vagrant;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Process\PendingProcess;
use Mockery;
use Mockery\MockInterface;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;
use Tests\TestCase;

class VagrantTest extends TestCase
{
    private ProcessRunner&MockInterface $processRunner;

    private Vagrant $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->processRunner = Mockery::mock(ProcessRunner::class);

        $path = storage_path('framework/testing/vagrant');

        (new Filesystem)->ensureDirectoryExists($path);
        (new Filesystem)->cleanDirectory($path);

        $this->instance = new Vagrant($this->processRunner, $path);
    }

    /** @test */
    public function it_has_available_server_regions()
    {
        $regions = $this->instance->findAvailableServerRegions();

        $this->assertCount(1, $regions);
    }

    /** @test */
    public function it_has_available_server_types_by_region()
    {
        $serverTypes = $this->instance->findAvailableServerTypesByRegion('my-region');

        $this->assertCount(3, $serverTypes);
    }

    /** @test */
    public function it_has_available_server_images()
    {
        $images = $this->instance->findAvailableServerImagesByRegion('my-region');

        $this->assertCount(1, $images);
    }

    /** @test */
    public function it_can_create_and_find_ssh_key()
    {
        $sshKey = $this->instance->findSshKeyByPublicKey('my-public-key');

        $this->assertNull($sshKey);

        $sshKeyNew = $this->instance->createSshKey('my-public-key');

        $sshKeyFound = $this->instance->findSshKeyByPublicKey('my-public-key');

        $this->assertEquals(1, $sshKeyNew->id);
        $this->assertEquals($sshKeyFound->id, $sshKeyNew->id);
    }

    /** @test */
    public function it_can_create_server()
    {
        $this->processRunner->shouldReceive('run')
            ->withArgs(function (PendingProcess $pendingProcess) {
                return $pendingProcess->command === 'vagrant up'
                    && $pendingProcess->path === storage_path('framework/testing/vagrant/1')
                    && $pendingProcess->timeout == 300;
            })
            ->andReturn(ProcessOutput::make()->setExitCode(0));

        $sshKeyNew = $this->instance->createSshKey('my-public-key');

        $server = $this->instance->createServer(
            'my-server',
            'my-region',
            'my-server-type',
            'ubuntu-image',
            $sshKeyNew->id,
        );

        $this->assertEquals(1, $server);
    }

    /** @test */
    public function it_can_get_server()
    {
        $this->processRunner->shouldReceive('run')
            ->withArgs(function (PendingProcess $pendingProcess) {
                return $pendingProcess->command === 'vagrant port --machine-readable'
                    && $pendingProcess->path === storage_path('framework/testing/vagrant/1');
            })
            ->andReturn(ProcessOutput::make("
1672304025,default,metadata,provider,virtualbox
1672304025,,ui,info,The forwarded ports for the machine are listed below. Please note that\nthese values may differ from values configured in the Vagrantfile if the\nprovider supports automatic port collision detection and resolution.
1672304025,,ui,info,
1672304025,,ui,info,    80 (guest) => 8000 (host)
1672304025,default,forwarded_port,80,8000
1672304025,,ui,info,   443 (guest) => 4433 (host)
1672304025,default,forwarded_port,443,4433
1672304025,,ui,info,    22 (guest) => 2222 (host)
1672304025,default,forwarded_port,22,2222
")->setExitCode(0));

        $this->processRunner->shouldReceive('run')
            ->withArgs(function (PendingProcess $pendingProcess) {
                return $pendingProcess->command === 'vagrant status --machine-readable'
                    && $pendingProcess->path === storage_path('framework/testing/vagrant/1');
            })
            ->andReturn(ProcessOutput::make("
1672304025,default,metadata,provider,virtualbox
1672304025,,ui,info,The forwarded ports for the machine are listed below. Please note that\nthese values may differ from values configured in the Vagrantfile if the\nprovider supports automatic port collision detection and resolution.
1672304025,,ui,info,
1672304025,,ui,info,    80 (guest) => 8000 (host)
1672304025,default,forwarded_port,80,8000
1672304025,,ui,info,   443 (guest) => 4433 (host)
1672304025,default,forwarded_port,443,4433
1672304025,,ui,info,    22 (guest) => 2222 (host)
1672304025,default,forwarded_port,22,2222
iMac-van-Pascal:1 pascalbaljet$ vagrant status --machine-readable
1672304063,default,metadata,provider,virtualbox
1672304063,default,provider-name,virtualbox
1672304063,default,state,running
1672304063,default,state-human-short,running
1672304063,default,state-human-long,The VM is running. To stop this VM%!(VAGRANT_COMMA) you can run `vagrant halt` to\nshut it down forcefully%!(VAGRANT_COMMA) or you can run `vagrant suspend` to simply\nsuspend the virtual machine. In either case%!(VAGRANT_COMMA) to restart it again%!(VAGRANT_COMMA)\nsimply run `vagrant up`.
1672304064,,ui,info,Current machine states:\n\ndefault                   running (virtualbox)\n\nThe VM is running. To stop this VM%!(VAGRANT_COMMA) you can run `vagrant halt` to\nshut it down forcefully%!(VAGRANT_COMMA) or you can run `vagrant suspend` to simply\nsuspend the virtual machine. In either case%!(VAGRANT_COMMA) to restart it again%!(VAGRANT_COMMA)\nsimply run `vagrant up`.
")->setExitCode(0));

        $server = $this->instance->getServer(1);

        $this->assertEquals(1, $server->id);
        $this->assertEquals(ServerStatus::Running, $server->status);
        $this->assertEquals([80 => 8000, 443 => 4433, 22 => 2222], $server->metadata['ports']);
    }

    /** @test */
    public function it_can_delete_server()
    {
        (new Filesystem)->ensureDirectoryExists(storage_path('framework/testing/vagrant/1'));
        (new Filesystem)->put(storage_path('framework/testing/vagrant/1/Vagrantfile'), 'test');

        $this->processRunner->shouldReceive('run')
            ->withArgs(function (PendingProcess $pendingProcess) {
                return $pendingProcess->command === 'vagrant destroy --force'
                    && $pendingProcess->path === storage_path('framework/testing/vagrant/1');
            })
            ->andReturn(ProcessOutput::make()->setExitCode(0));

        $this->assertNull($this->instance->deleteServer(1));
        $this->assertDirectoryDoesNotExist(storage_path('framework/testing/vagrant/1'));
    }

    /** @test */
    public function it_can_get_public_ipv4_of_server()
    {
        $this->assertEquals('192.168.60.61', $this->instance->getPublicIpv4OfServer(1));
        $this->assertEquals('192.168.60.71', $this->instance->getPublicIpv4OfServer(11));
    }
}
