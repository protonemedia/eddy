<?php

namespace Tests\Unit\Jobs;

use App\CaddyfilePatcher;
use App\Jobs\UpdateSiteTlsSetting;
use App\Models\Certificate;
use App\Models\Site;
use App\Models\TlsSetting;
use App\Tasks\PrettifyCaddyfile;
use App\Tasks\UpdateCaddyfile;
use Database\Factories\CertificateFactory;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UpdateSiteTlsSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_to_a_custom_certificate()
    {
        TaskRunner::fake();
        Bus::fake();

        /** @var Site */
        $site = SiteFactory::new()->installed()->create([
            'address' => 'example.com',
            'tls_setting' => TlsSetting::Auto,
            'pending_tls_update_since' => now(),
        ]);

        /** @var Certificate */
        $certificate = CertificateFactory::new()->uploaded()->notActive()->create(['site_id' => $site->id]);
        $certificate->id = 1;

        //

        app()->singleton(
            CaddyfilePatcher::class,
            fn () => $this->mock(CaddyfilePatcher::class)
                ->shouldReceive('replaceTlsSnippet')
                ->with(TlsSetting::Custom, $certificate)
                ->andReturnSelf()
                ->getMock()
                ->shouldReceive('get')
                ->andReturn('new caddyfile')
                ->getMock()
        );

        $job = new UpdateSiteTlsSetting($site, TlsSetting::Custom, $certificate);
        $job->handle();

        TaskRunner::assertDispatched(function (PrettifyCaddyfile $task) use ($site) {
            return $task->path === $site->files()->caddyfile()->path;
        });

        TaskRunner::assertDispatched(function (UpdateCaddyfile $task) use ($site) {
            return $task->site->is($site)
                && $task->caddyfile === 'new caddyfile';
        });

        $site->refresh();

        $this->assertEquals(TlsSetting::Custom, $site->tls_setting);
        $this->assertTrue($certificate->is_active);
        $this->assertNull($site->pending_tls_update_since);
    }
}
