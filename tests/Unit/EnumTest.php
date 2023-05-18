<?php

namespace Tests\Unit;

use App\Enum;
use Illuminate\Validation\Rules\Enum as EnumRule;
use PHPUnit\Framework\TestCase;

enum TestEnum: string
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
}

class EnumTest extends TestCase
{
    /** @test */
    public function it_can_return_options()
    {
        $options = Enum::options(TestEnum::class, true);

        $this->assertEquals(['a' => 'A', 'b' => 'B', 'c' => 'C'], $options);
    }

    /** @test */
    public function it_can_return_values()
    {
        $values = Enum::values(TestEnum::class);

        $this->assertEquals(['a', 'b', 'c'], $values);
    }

    /** @test */
    public function it_can_return_rule()
    {
        $rule = Enum::rule(TestEnum::class);

        $this->assertInstanceOf(EnumRule::class, $rule);
    }

    /** @test */
    public function it_can_return_required_if()
    {
        $item = TestEnum::A;

        $requiredIf = Enum::requiredIf($item);

        $this->assertEquals('required_if:test_enum,a', $requiredIf);
    }

    /** @test */
    public function it_can_return_required_if_with_field()
    {
        $item = TestEnum::A;

        $requiredIf = Enum::requiredIf($item, 'test_enum_field');

        $this->assertEquals('required_if:test_enum_field,a', $requiredIf);
    }

    /** @test */
    public function it_can_return_required_unless()
    {
        $item = TestEnum::A;

        $requiredUnless = Enum::requiredUnless($item);

        $this->assertEquals('required_unless:test_enum,a', $requiredUnless);
    }

    /** @test */
    public function it_can_return_required_unless_with_field()
    {
        $item = TestEnum::A;

        $requiredUnless = Enum::requiredUnless($item, 'test_enum_field');

        $this->assertEquals('required_unless:test_enum_field,a', $requiredUnless);
    }
}
