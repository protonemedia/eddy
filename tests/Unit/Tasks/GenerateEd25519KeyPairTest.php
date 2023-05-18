<?php

namespace Tests\Unit\Tasks;

use App\Tasks\GenerateEd25519KeyPair;
use Tests\TestCase;

class GenerateEd25519KeyPairTest extends TestCase
{
    /** @test */
    public function it_generates_a_ed25519_key_pair()
    {
        $this->assertMatchesBashSnapshot(
            (new GenerateEd25519KeyPair('/tmp'))->getScript()
        );
    }
}
