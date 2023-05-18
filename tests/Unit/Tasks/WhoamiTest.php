<?php

namespace Tests\Unit\Tasks;

use App\Tasks\Whoami;
use Tests\TestCase;

class WhoamiTest extends TestCase
{
    /** @test */
    public function it_reloads_the_caddy_server()
    {
        $this->assertEquals(
            'whoami',
            (new Whoami)->render()
        );
    }
}
