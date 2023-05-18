<?php

namespace Tests\Unit\Jobs;

use App\CaddyfilePatcher;
use App\Jobs\UpdateSiteCaddyfile;
use App\Models\Site;
use App\Server\PhpVersion;
use App\Tasks\PrettifyCaddyfile;
use App\Tasks\UpdateCaddyfile;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UpdateSiteCaddyfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_the_php_version_and_web_folder()
    {
        TaskRunner::fake();
        Bus::fake();

        /** @var Site */
        $site = SiteFactory::new()->installed()->create([
            'address' => 'example.com',
            'php_version' => PhpVersion::Php81,
            'web_folder' => '/public',
            'pending_caddyfile_update_since' => now(),
        ]);

        //

        app()->singleton(
            CaddyfilePatcher::class,
            fn () => $this->mock(CaddyfilePatcher::class)
                ->shouldReceive('replacePhpVersion')
                ->with(PhpVersion::Php82)
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('replacePublicFolder')
                ->with('/home/eddy/example.com/current/web')
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('get')
                ->andReturn('new caddyfile')
                ->getMock()
        );

        $job = new UpdateSiteCaddyfile($site, PhpVersion::Php82, '/web');
        $job->handle();

        TaskRunner::assertDispatched(function (PrettifyCaddyfile $task) use ($site) {
            return $task->path === $site->files()->caddyfile()->path;
        });

        TaskRunner::assertDispatched(function (UpdateCaddyfile $task) use ($site) {
            return $task->site->is($site)
                && $task->caddyfile === 'new caddyfile';
        });

        $site->refresh();

        $this->assertEquals(PhpVersion::Php82, $site->php_version);
        $this->assertEquals('/web', $site->web_folder);
        $this->assertNull($site->pending_caddyfile_update_since);
    }
}
