<?php

namespace Tests\Browser;

use App\Models\Site;
use App\Tasks\GetFile;
use App\Tasks\UploadFile;
use App\Tasks\ValidateCaddyfile;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteFileTest extends DuskTestCase
{
    use DatabaseMigrations;
    use ServerTest;

    /** @test */
    public function it_can_edit_a_file_from_the_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            TaskRunner::fake([
                GetFile::class => 'Dummy Caddyfile',
                ValidateCaddyfile::class => 'Valid configuration',
                UploadFile::class => 'OK',
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.files.index', [$this->server, $site]))
                ->waitForText('Caddyfile')
                ->click('tbody td')
                ->waitForModal()
                ->waitForText('/Caddyfile')
                ->waitForText('Dummy Caddyfile')
                ->type('contents', 'Dummy Caddyfile updated')
                ->press('Save')
                ->waitForText('The file will be updated');

            TaskRunner::assertDispatched(UploadFile::class, function (UploadFile $task) use ($site) {
                return $task->path === $site->files()->caddyfile()->path && $task->contents === 'Dummy Caddyfile updated';
            });
        });
    }
}
