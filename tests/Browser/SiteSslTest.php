<?php

namespace Tests\Browser;

use App\Jobs\InstallCertificate;
use App\Jobs\UpdateSiteTlsSetting;
use App\Models\Site;
use App\Models\TlsSetting;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteSslTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_change_the_ssl_setting_to_auto()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create([
                'tls_setting' => TlsSetting::Off,
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.ssl.edit', [$this->server, $site]))
                ->waitForText('SSL Settings')
                ->radio('tls_setting', 'auto')
                ->press('Save')
                ->waitForText('Are you sure you want to update the SSL settings?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site SSL settings will be updated.');

            $this->assertNotNull($site->fresh()->pending_tls_update_since);

            Bus::assertNotDispatched(InstallCertificate::class);
            Bus::assertDispatched(UpdateSiteTlsSetting::class, function (UpdateSiteTlsSetting $job) use ($site) {
                return $job->site->is($site) && $job->tlsSetting === TlsSetting::Auto;
            });
        });
    }

    /** @test */
    public function it_knows_whether_no_ssl_changes_have_been_made()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.ssl.edit', [$this->server, $site]))
                ->waitForText('SSL Settings')
                ->press('Save')
                ->waitForText('Are you sure you want to update the SSL settings?')
                ->press('@splade-confirm-confirm')
                ->waitForText('No changes were made');

            $this->assertNull($site->fresh()->pending_tls_update_since);

            Bus::assertNotDispatched(InstallCertificate::class);
            Bus::assertNotDispatched(UpdateSiteTlsSetting::class);
        });
    }

    /** @test */
    public function it_can_change_the_ssl_setting_to_internal()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.ssl.edit', [$this->server, $site]))
                ->waitForText('SSL Settings')
                ->radio('tls_setting', 'internal')
                ->press('Save')
                ->waitForText('Are you sure you want to update the SSL settings?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site SSL settings will be updated.');

            $this->assertNotNull($site->fresh()->pending_tls_update_since);

            Bus::assertNotDispatched(InstallCertificate::class);
            Bus::assertDispatched(UpdateSiteTlsSetting::class, function (UpdateSiteTlsSetting $job) use ($site) {
                return $job->site->is($site) && $job->tlsSetting === TlsSetting::Internal;
            });
        });
    }

    /** @test */
    public function it_can_change_the_ssl_setting_to_off()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.ssl.edit', [$this->server, $site]))
                ->waitForText('SSL Settings')
                ->radio('tls_setting', 'off')
                ->press('Save')
                ->waitForText('Are you sure you want to update the SSL settings?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site SSL settings will be updated.');

            $this->assertNotNull($site->fresh()->pending_tls_update_since);

            Bus::assertNotDispatched(InstallCertificate::class);
            Bus::assertDispatched(UpdateSiteTlsSetting::class, function (UpdateSiteTlsSetting $job) use ($site) {
                return $job->site->is($site) && $job->tlsSetting === TlsSetting::Off;
            });
        });
    }

    /** @test */
    public function it_can_add_a_custom_certificate()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.ssl.edit', [$this->server, $site]))
                ->waitForText('SSL Settings')
                ->radio('tls_setting', 'custom')
                ->type('private_key', 'private_key')
                ->type('certificate', 'certificate')
                ->press('Save')
                ->waitForText('Are you sure you want to update the SSL settings?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The certificate will be uploaded to the server and installed');

            $this->assertNotNull($site->fresh()->pending_tls_update_since);

            $this->assertCount(1, $site->fresh()->certificates);
            $this->assertEquals('private_key', $site->fresh()->certificates->first()->private_key);
            $this->assertEquals('certificate', $site->fresh()->certificates->first()->certificate);

            Bus::assertNotDispatched(UpdateSiteTlsSetting::class);
            Bus::assertDispatched(InstallCertificate::class, function (InstallCertificate $job) use ($site) {
                return $job->certificate->is($site->fresh()->certificates->first());
            });
        });
    }
}
