<?php

namespace Tests\Unit\Tasks;

use App\Models\Server;
use App\Tasks\AuthorizePublicKey;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizePublicKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_authorizes_the_public_key()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new AuthorizePublicKey($server, 'public-key'))->getScript()
        );
    }

    /** @test */
    public function it_authorizes_the_public_key_as_root()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new AuthorizePublicKey($server, 'public-key', true))->getScript()
        );
    }
}
