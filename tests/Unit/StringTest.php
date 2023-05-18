<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Tests\TestCase;

class StringTest extends TestCase
{
    /** @test */
    public function it_can_create_a_key_for_wordpress_installations()
    {
        $key1 = Str::generateWordpressKey();
        $key2 = Str::generateWordpressKey();

        $this->assertNotEquals($key1, $key2);
        $this->assertEquals(64, strlen($key1));
        $this->assertEquals(64, strlen($key2));
    }
}
