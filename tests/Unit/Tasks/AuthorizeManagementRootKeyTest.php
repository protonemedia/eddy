<?php

namespace Tests\Unit\Tasks;

use App\Models\Server;
use App\Tasks\AuthorizeManagementRootKey;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizeManagementRootKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_authorizes_the_public_key()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new AuthorizeManagementRootKey($server))->getScript()
        );
    }
}
