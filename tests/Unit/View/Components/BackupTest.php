<?php

namespace Tests\Unit\View\Components;

use App\View\Components\Backup;
use Database\Factories\BackupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_rendered()
    {
        $backup = BackupFactory::new()->create();
        $backup->id = '1';
        $backup->dispatch_token = 'token';

        $this->assertMatchesTextSnapshot(
            (new Backup($backup))->renderComponent()
        );
    }
}
