<?php

namespace Tests\Unit\Rules;

use App\Rules\FirewallPort;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FirewallPortTest extends TestCase
{
    /** @test */
    public function it_can_validate_a_port()
    {
        $rule = new FirewallPort();
        $validator = Validator::make(
            ['port' => '22'],
            ['port' => $rule]
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_can_invalide_port()
    {
        $rule = new FirewallPort();
        $validator = Validator::make(
            ['port' => 'abc'],
            ['port' => $rule]
        );
        $this->assertTrue($validator->fails());
        $this->assertEquals('The port field is invalid.', $validator->errors()->first('port'));
    }

    /** @test */
    public function it_can_invalide_range()
    {
        $rule = new FirewallPort();
        $validator = Validator::make(
            ['port' => '500:300'],
            ['port' => $rule]
        );
        $this->assertTrue($validator->fails());
        $this->assertEquals('The range is invalid.', $validator->errors()->first('port'));
    }

    /** @test */
    public function it_can_be_out_of_range()
    {
        $rule = new FirewallPort();
        $validator = Validator::make(
            ['port' => '0'],
            ['port' => $rule]
        );
        $this->assertTrue($validator->fails());
        $this->assertEquals('The port field must be between 1 and 65535.', $validator->errors()->first('port'));
    }
}
