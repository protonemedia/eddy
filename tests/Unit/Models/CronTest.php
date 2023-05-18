<?php

namespace Tests\Unit\Models;

use Database\Factories\CronFactory;
use Tests\TestCase;

class CronTest extends TestCase
{
    /** @test */
    public function it_generates_a_log_path_based_on_the_user()
    {
        $cron = CronFactory::new()->make(['user' => 'root']);
        $cron->id = 1;

        $this->assertEquals('/root/.eddy/cron-1.log', $cron->logPath());

        $cron = CronFactory::new()->make(['user' => 'protone']);
        $cron->id = 2;

        $this->assertEquals('/home/protone/.eddy/cron-2.log', $cron->logPath());
    }
}
