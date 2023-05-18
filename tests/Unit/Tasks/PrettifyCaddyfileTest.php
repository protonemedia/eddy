<?php

namespace Tests\Unit\Tasks;

use App\Tasks\PrettifyCaddyfile;
use Tests\TestCase;

class PrettifyCaddyfileTest extends TestCase
{
    /** @test */
    public function it_overwrites_the_existing_path()
    {
        $this->assertEquals(
            'caddy fmt /home/protone/caddyfile --overwrite',
            (new PrettifyCaddyfile('/home/protone/caddyfile'))->render()
        );
    }
}
