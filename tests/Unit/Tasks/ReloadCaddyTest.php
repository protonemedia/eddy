<?php

namespace Tests\Unit\Tasks;

use App\Tasks\ReloadCaddy;
use Tests\TestCase;

class ReloadCaddyTest extends TestCase
{
    /** @test */
    public function it_reloads_the_caddy_config()
    {
        $this->assertMatchesBashSnapshot(
            (new ReloadCaddy)->getScript()
        );
    }
}
