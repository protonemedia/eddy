<?php

namespace Tests\Unit\Tasks;

use App\Models\Server;
use App\Tasks\DeauthorizePublicKey;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeauthorizePublicKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deauthorizes_the_public_key()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new DeauthorizePublicKey($server, 'public-key'))->getScript()
        );
    }
}
