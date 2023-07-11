<?php

namespace Tests\Unit\Tasks;

use App\Models\Server;
use App\Tasks\InstallEddyBackupCli;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallEddyBackupCliTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_installs_the_eddy_filesystem_cli_on_the_server()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new InstallEddyBackupCli($server))->getScript()
        );
    }
}
