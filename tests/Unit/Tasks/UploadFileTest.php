<?php

namespace Tests\Unit\Tasks;

use App\Tasks\UploadFile;
use Tests\TestCase;

class UploadFileTest extends TestCase
{
    /** @test */
    public function it_can_store_a_file_at_the_given_path()
    {
        $this->assertMatchesBashSnapshot(
            (new UploadFile('/home/protone/test.txt', 'new content'))->getScript()
        );
    }
}
