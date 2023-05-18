<?php

namespace Tests\Unit\Tasks;

use App\Tasks\GetFile;
use Tests\TestCase;

class GetFileTest extends TestCase
{
    /** @test */
    public function it_returns_the_content_of_a_file()
    {
        $this->assertMatchesBashSnapshot(
            (new GetFile('/home/protone/test.txt'))->getScript()
        );
    }
}
