<?php

namespace Tests\Unit\Rules;

use App\Rules\PublicKey;
use Database\Factories\Dummies;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublicKeyTest extends TestCase
{
    /** @test */
    public function it_can_validate_public_key()
    {
        $publicKey = Dummies::rsaKeyPair()->publicKey;
        $rule = new PublicKey();
        $validator = Validator::make(
            ['public_key' => $publicKey],
            ['public_key' => $rule]
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_can_invalidate_public_key()
    {
        $publicKey = 'not a valid public key';
        $rule = new PublicKey();
        $validator = Validator::make(
            ['public_key' => $publicKey],
            ['public_key' => $rule]
        );
        $this->assertTrue($validator->fails());
        $this->assertEquals('The public key is not valid.', $validator->errors()->first('public_key'));
    }
}
