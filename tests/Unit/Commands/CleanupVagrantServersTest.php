<?php

namespace Tests\Unit\Commands;

use App\Models\Server;
use App\Provider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class CleanupVagrantServersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_cleanup_vagrant_servers_command()
    {
        // Arrange
        $server1 = Server::factory()->create(['provider' => Provider::Vagrant, 'provider_id' => '1']);
        $server2 = Server::factory()->create(['provider' => Provider::Vagrant, 'provider_id' => '2']);
        $server3 = Server::factory()->create(['provider' => Provider::DigitalOcean, 'provider_id' => '3']);
        $filesystemMock = $this->createMock(Filesystem::class);

        Process::fake();

        $filesystemMock->expects($this->exactly(2))->method('deleteDirectory');
        $filesystemMock->expects($this->exactly(1))->method('directories')->willReturn([
            config('services.vagrant.path').'/1',
            config('services.vagrant.path').'/2',
        ]);

        $this->app->bind(Filesystem::class, fn () => $filesystemMock);

        $this->artisan('app:cleanup-vagrant-servers')
            ->expectsOutput('Vagrant destroy 1')
            ->expectsOutput('Deleting Server Model')
            ->expectsOutput('Deleting Server Directory')
            ->expectsOutput('Vagrant destroy 2')
            ->expectsOutput('Deleting Server Model')
            ->expectsOutput('Deleting Server Directory')
            ->doesntExpectOutput('Vagrant destroy 3')
            ->doesntExpectOutput('Deleting Server Model')
            ->doesntExpectOutput('Deleting Server Directory');

        Process::assertRanTimes('vagrant destroy -f', times: 2);

        $this->assertNull($server1->fresh());
        $this->assertNull($server2->fresh());
        $this->assertNotNull($server3->fresh());
    }
}
