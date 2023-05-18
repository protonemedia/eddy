<?php

namespace Tests\Browser;

use App\Models\Site;
use App\Tasks\GetFile;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PersistentFakeTasks;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteLogTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentFakeTasks;
    use ServerTest;

    /** @test */
    public function it_can_view_a_log_file_from_the_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            TaskRunner::fake([
                GetFile::class => 'Dummy Caddy Log output',
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.logs.index', [$this->server, $site]))
                ->waitForText('Caddy Log')
                ->click('tbody td')
                ->waitForModal()
                ->waitForText('Dummy Caddy Log output');
        });
    }
}
