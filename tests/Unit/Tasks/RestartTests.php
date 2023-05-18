<?php

namespace Tests\Unit\Tasks;

use App\Tasks\RestartMySql;
use App\Tasks\RestartPhp81;
use App\Tasks\RestartPhp82;
use App\Tasks\RestartRedis;
use App\Tasks\RestartSupervisor;
use Tests\TestCase;

class RestartTests extends TestCase
{
    /** @test */
    public function it_can_restart_mysql()
    {
        $this->assertMatchesBashSnapshot(
            (new RestartMySql)->getScript()
        );
    }

    /** @test */
    public function it_can_restart_php81()
    {
        $this->assertMatchesBashSnapshot(
            (new RestartPhp81)->getScript()
        );
    }

    /** @test */
    public function it_can_restart_php82()
    {
        $this->assertMatchesBashSnapshot(
            (new RestartPhp82)->getScript()
        );
    }

    /** @test */
    public function it_can_restart_redis()
    {
        $this->assertMatchesBashSnapshot(
            (new RestartRedis)->getScript()
        );
    }

    /** @test */
    public function it_can_restart_supervisor()
    {
        $this->assertMatchesBashSnapshot(
            (new RestartSupervisor)->getScript()
        );
    }
}
