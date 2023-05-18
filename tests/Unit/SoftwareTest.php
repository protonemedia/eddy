<?php

namespace Tests\Unit;

use App\Server\PhpVersion;
use App\Server\Software;
use PHPUnit\Framework\TestCase;

class SoftwareTest extends TestCase
{
    /** @test */
    public function it_can_return_default_stack()
    {
        $expected = [
            Software::Caddy2,
            Software::MySql80,
            Software::Redis6,
            Software::Php81,
            Software::Php82,
            Software::Composer2,
            Software::Node18,
        ];
        $this->assertEquals($expected, Software::defaultStack());
    }

    /** @test */
    public function it_can_return_display_name()
    {
        $this->assertEquals('Caddy 2', Software::Caddy2->getDisplayName());
        $this->assertEquals('Composer 2', Software::Composer2->getDisplayName());
        $this->assertEquals('MySQL 8.0', Software::MySql80->getDisplayName());
        $this->assertEquals('Node 18', Software::Node18->getDisplayName());
        $this->assertEquals('PHP 8.1', Software::Php81->getDisplayName());
        $this->assertEquals('PHP 8.2', Software::Php82->getDisplayName());
        $this->assertEquals('Redis 6', Software::Redis6->getDisplayName());
    }

    /** @test */
    public function it_can_return_restart_task_class()
    {
        $this->assertEquals(\App\Tasks\ReloadCaddy::class, Software::Caddy2->restartTaskClass());
        $this->assertEquals(\App\Tasks\RestartMySql::class, Software::MySql80->restartTaskClass());
        $this->assertEquals(\App\Tasks\RestartPhp81::class, Software::Php81->restartTaskClass());
        $this->assertEquals(\App\Tasks\RestartPhp82::class, Software::Php82->restartTaskClass());
        $this->assertEquals(\App\Tasks\RestartRedis::class, Software::Redis6->restartTaskClass());
        $this->assertNull(Software::Composer2->restartTaskClass());
    }

    /** @test */
    public function it_can_return_find_php_version()
    {
        $this->assertEquals(PhpVersion::Php81, Software::Php81->findPhpVersion());
        $this->assertEquals(PhpVersion::Php82, Software::Php82->findPhpVersion());
        $this->assertNull(Software::Caddy2->findPhpVersion());
    }
}
