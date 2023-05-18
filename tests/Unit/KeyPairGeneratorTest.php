<?php

namespace Tests\Unit;

use App\KeyPairGenerator;
use App\KeyPairType;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class KeyPairGeneratorTest extends TestCase
{
    /** @test */
    public function it_can_generate_an_ed25519_key_pair()
    {
        $generator = new KeyPairGenerator;

        $pair = $generator->ed25519();

        $this->assertEquals(KeyPairType::Ed25519, $pair->type);
        $this->assertStringContainsString('BEGIN OPENSSH PRIVATE KEY', $pair->privateKey);
        $this->assertStringContainsString('ssh-ed25519', $pair->publicKey);
        $this->assertTrue((new Filesystem)->isEmptyDirectory(storage_path('app/keygen')));
    }
}
