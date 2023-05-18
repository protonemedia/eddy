<?php

namespace Tests\Browser;

use App\Tasks\GetFile;
use App\Tasks\UploadFile;
use App\Tasks\ValidateCaddyfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\DuskTestCase;
use Tests\ServerTest;

class FileTest extends DuskTestCase
{
    use DatabaseMigrations;
    use ServerTest;

    /** @test */
    public function it_can_edit_a_file_from_the_server()
    {
        $this->browse(function (Browser $browser) {
            TaskRunner::fake([
                GetFile::class => 'Dummy Caddyfile',
                ValidateCaddyfile::class => 'Valid configuration',
                UploadFile::class => 'OK',
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.files.index', $this->server))
                ->waitForText('Caddyfile')
                ->click('tbody td')
                ->waitForModal()
                ->waitForText('/etc/caddy/Caddyfile')
                ->waitForText('Dummy Caddyfile')
                ->type('contents', 'Dummy Caddyfile updated')
                ->press('Save')
                ->waitForText('The file will be updated');

            TaskRunner::assertDispatched(UploadFile::class, function (UploadFile $task) {
                return $task->path === $this->server->files()->caddyfile()->path && $task->contents === 'Dummy Caddyfile updated';
            });
        });
    }
}
