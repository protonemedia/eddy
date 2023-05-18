<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallSiteCaddyfile;
use App\Models\Site;
use App\Notifications\JobOnServerFailed;
use App\Tasks\UpdateCaddyfile;
use App\Tasks\UpdateCaddySiteImports;
use Database\Factories\SiteFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallSiteCaddyfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_caddyfile_and_refreshes_the_sites_list()
    {
        TaskRunner::fake();

        /** @var Site */
        $site = SiteFactory::new()->notInstalled()->create([
            'address' => 'example.com',
        ]);

        $job = new InstallSiteCaddyfile($site);
        $job->handle();

        $this->assertNotNull($site->fresh()->installed_at);

        TaskRunner::assertDispatched(function (UpdateCaddyfile $task) use ($site) {
            return $task->site->is($site)
                && Str::contains($task->caddyfile, 'example.com:443 {');
        });

        TaskRunner::assertDispatched(function (UpdateCaddySiteImports $task) use ($site) {
            return $task->server->is($site->server);
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $site = SiteFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallSiteCaddyfile($site, $user);
        $job->failed(new \Exception('Installation failed.'));

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'installation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($site) {
            return $notification->server->is($site->server);
        });
    }
}
