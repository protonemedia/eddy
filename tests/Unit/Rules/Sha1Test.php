<?php

namespace Tests\Unit\Rules;

use App\Rules\Sha1;
use Tests\TestCase;

class Sha1Test extends TestCase
{
    /** @test */
    public function it_validates_a_sha1_hash()
    {
        $this->assertTrue(Sha1::passes('a94a8fe5ccb19ba61c4c0873d391e987982fbbd3'));
        $this->assertFalse(Sha1::passes('a94a8fe5ccb19ba61c4c0873d391e987982fbbd'));
        $this->assertFalse(Sha1::passes('a94a8fe5ccb19ba61c4c0873d391e987982fbbdg'));
    }
}
