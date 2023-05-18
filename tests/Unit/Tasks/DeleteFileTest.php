<?php

namespace Tests\Unit\Tasks;

use App\Tasks\DeleteFile;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    /** @test */
    public function it_can_delete_the_given_file_path()
    {
        $this->assertMatchesBashSnapshot(
            (new DeleteFile('/home/protone/test.txt'))->getScript()
        );
    }
}
