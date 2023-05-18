<?php

namespace Tests\Unit\Tasks;

use App\Tasks\ReloadSupervisor;
use Tests\TestCase;

class ReloadSupervisorTest extends TestCase
{
    /** @test */
    public function it_reloads_the_supervisor_config()
    {
        $this->assertMatchesBashSnapshot(
            (new ReloadSupervisor)->getScript()
        );
    }
}
