<?php

namespace Tests\Unit\View\Components;

use App\View\Components\SupervisorProgram;
use Database\Factories\DaemonFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorProgramTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_rendered()
    {
        $daemon = DaemonFactory::new()->create();
        $daemon->id = '1';

        $this->assertMatchesTextSnapshot(
            (new SupervisorProgram($daemon))->renderComponent()
        );
    }
}
