<?php

namespace Tests\Unit\Rules;

use App\Rules\CronExpression;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CronExpressionTest extends TestCase
{
    /** @test */
    public function it_can_validate_a_cron_expression()
    {
        $rule = new CronExpression();

        $validator = Validator::make(
            ['expression' => 'foo'],
            ['expression' => $rule]
        );

        $this->assertTrue($validator->fails());

        $validator = Validator::make(
            ['expression' => '* * * * *'],
            ['expression' => $rule]
        );
        $this->assertFalse($validator->fails());
    }
}
