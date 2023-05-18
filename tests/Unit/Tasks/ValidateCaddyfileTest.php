<?php

namespace Tests\Unit\Tasks;

use App\Tasks\ValidateCaddyfile;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValidateCaddyfileTest extends TestCase
{
    /** @test */
    public function it_stores_the_caddy_file_to_a_temporary_path_and_validates_it()
    {
        Str::createRandomStringsUsing(fn () => '0IJ7PAPA18XR05Jt');

        $this->assertMatchesBashSnapshot(
            (new ValidateCaddyfile('server { }'))->getScript()
        );
    }
}
