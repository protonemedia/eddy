<?php

namespace Tests\Unit\Tasks;

use App\Tasks\UpdateCaddySiteImports;
use Database\Factories\ServerFactory;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCaddySiteImportsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_reloads_the_caddy_server()
    {
        $server = ServerFactory::new()->provisioned()->create();

        SiteFactory::new()->installed()->create([
            'address' => 'google.com',
            'server_id' => $server->id,
        ]);

        SiteFactory::new()->installed()->create([
            'address' => 'apple.com',
            'server_id' => $server->id,
        ]);

        $this->assertMatchesBashSnapshot(
            (new UpdateCaddySiteImports($server))->getScript()
        );
    }
}
