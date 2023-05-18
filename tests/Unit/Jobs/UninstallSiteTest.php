<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallSite;
use App\Models\Site;
use App\Tasks\DeleteFile;
use App\Tasks\UpdateCaddySiteImports;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PendingTask;
use Tests\TestCase;

class UninstallSiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_executes_a_command_to_delete_the_firewall_rule()
    {
        TaskRunner::fake();

        /** @var Site */
        $site = SiteFactory::new()->make();

        $job = new UninstallSite($site->server, $site->path);
        $job->handle();

        TaskRunner::assertDispatched(UpdateCaddySiteImports::class);
        TaskRunner::assertDispatched(DeleteFile::class, function (PendingTask $task) use ($site) {
            return $task->task->path === $site->path;
        });
    }
}
