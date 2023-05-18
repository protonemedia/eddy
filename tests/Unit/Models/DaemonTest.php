<?php

namespace Tests\Unit\Models;

use Database\Factories\DaemonFactory;
use Tests\TestCase;

class DaemonTest extends TestCase
{
    /** @test */
    public function it_generates_a_error_path_based_on_the_user()
    {
        $daemon = DaemonFactory::new()->make(['user' => 'root']);
        $daemon->id = 1;
        $this->assertEquals('/root/.eddy/daemon-1.err', $daemon->errorLogPath());

        $daemon = DaemonFactory::new()->make(['user' => 'protone']);
        $daemon->id = 2;
        $this->assertEquals('/home/protone/.eddy/daemon-2.err', $daemon->errorLogPath());
    }

    /** @test */
    public function it_generates_a_log_path_based_on_the_user()
    {
        $daemon = DaemonFactory::new()->make(['user' => 'root']);
        $daemon->id = 1;
        $this->assertEquals('/root/.eddy/daemon-1.log', $daemon->outputLogPath());

        $daemon = DaemonFactory::new()->make(['user' => 'protone']);
        $daemon->id = 2;
        $this->assertEquals('/home/protone/.eddy/daemon-2.log', $daemon->outputLogPath());
    }
}
