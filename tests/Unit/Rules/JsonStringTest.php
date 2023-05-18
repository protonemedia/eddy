<?php

namespace Tests\Unit\Rules;

use App\Rules\JsonString;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class JsonStringTest extends TestCase
{
    /** @test */
    public function it_can_validate_a_json_string()
    {
        $rule = new JsonString();

        $validator = Validator::make(
            ['json' => '{ // no }'],
            ['json' => $rule]
        );

        $this->assertTrue($validator->fails());

        $validator = Validator::make(
            ['json' => '{ "foo": "bar" }'],
            ['json' => $rule]
        );
        $this->assertFalse($validator->fails());
    }
}
