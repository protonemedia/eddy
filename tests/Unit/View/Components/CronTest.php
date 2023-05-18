<?php

namespace Tests\Unit\View\Components;

use App\View\Components\Cron;
use Database\Factories\CronFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_rendered()
    {
        $cron = CronFactory::new()->create();
        $cron->id = '1';

        $this->assertMatchesTextSnapshot(
            (new Cron($cron))->renderComponent()
        );
    }
}
