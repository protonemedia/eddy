<?php

namespace Tests\Unit\Support;

use App\Http\Csp;
use Tests\TestCase;

class CspTest extends TestCase
{
    /** @test */
    public function it_can_configure_the_csp_policy_without_errors()
    {
        $csp = new Csp();
        $this->assertNull($csp->configure());
        $this->assertIsString($csp->__toString());
    }
}
