<?php

namespace Tests\Browser;

use App\Tasks\GetFile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PersistentFakeTasks;
use Tests\DuskTestCase;
use Tests\ServerTest;

class LogTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentFakeTasks;
    use ServerTest;

    /** @test */
    public function it_can_view_a_log_file_from_the_server()
    {
        $this->browse(function (Browser $browser) {
            TaskRunner::fake([
                GetFile::class => 'Dummy Server output',
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.logs.index', $this->server))
                ->waitForText('Caddy Access Log')
                ->click('tbody td')
                ->waitForModal()
                ->waitForText('Dummy Server output');
        });
    }
}
