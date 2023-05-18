<?php

namespace Tests\Unit\Tasks;

use App\Tasks\ValidateMySqlConfig;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValidateMySqlConfigTest extends TestCase
{
    /** @test */
    public function it_stores_the_mysql_config_to_a_temporary_path_and_validates_it()
    {
        Str::createRandomStringsUsing(fn () => '0IJ7PAPA18XR05Jt');

        $this->assertMatchesBashSnapshot(
            (new ValidateMySqlConfig('[mysqld]'))->getScript()
        );
    }
}
