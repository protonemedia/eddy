<?php

namespace Tests\Browser;

use App\Jobs\CreateDeployment;
use App\Jobs\DeploySite;
use App\Jobs\UninstallSite;
use App\Jobs\UpdateSiteCaddyfile;
use App\Models\Site;
use App\Models\SiteType;
use App\Server\PhpVersion;
use App\Tasks\GenerateEd25519KeyPair;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_create_a_new_site()
    {
        TaskRunner::dontFake(GenerateEd25519KeyPair::class);

        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->sites()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.index', $this->server))
                ->clickLink('New Site')
                ->waitForModal()
                ->type('address', 'my-site.test')
                ->select('php_version', 'php81')
                ->select('type', 'generic')
                ->type('web_folder', '/web')
                ->type('repository_url', 'git@github.com:protonemedia/eddy.git')
                ->type('repository_branch', 'main')
                ->press('Deploy Now')
                ->waitForText('Output Log');

            $this->assertEquals(1, $this->server->sites()->count());

            /** @var Site */
            $site = $this->server->sites()->first();
            $this->assertNotNull($site->latestDeployment);

            $browser->assertRouteIs('servers.sites.deployments.show', [$this->server, $site, $site->latestDeployment]);

            $this->assertEquals('my-site.test', $site->address);
            $this->assertEquals(PhpVersion::Php81, $site->php_version);
            $this->assertEquals(SiteType::Generic, $site->type);
            $this->assertEquals('/web', $site->web_folder);
            $this->assertEquals('git@github.com:protonemedia/eddy.git', $site->repository_url);
            $this->assertEquals('main', $site->repository_branch);
            $this->assertNull($site->deploy_key_public);
            $this->assertNull($site->deploy_key_private);

            Bus::assertDispatched(DeploySite::class);
        });
    }

    /** @test */
    public function it_can_create_a_new_site_with_a_deploy_key()
    {
        TaskRunner::dontFake(GenerateEd25519KeyPair::class);

        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->sites()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.index', $this->server))
                ->clickLink('New Site')
                ->waitForModal()
                ->type('address', 'my-site.test')
                ->select('php_version', 'php81')
                ->select('type', 'generic')
                ->type('web_folder', '/web')
                ->type('repository_url', 'git@github.com:protonemedia/eddy.git')
                ->type('repository_branch', 'main')
                ->press('Use a Deploy Key')
                ->press('Deploy Now')
                ->waitForText('Output Log');

            $this->assertEquals(1, $this->server->sites()->count());

            /** @var Site */
            $site = $this->server->sites()->first();
            $this->assertNotNull($site->latestDeployment);

            $browser->assertRouteIs('servers.sites.deployments.show', [$this->server, $site, $site->latestDeployment]);

            $this->assertEquals('my-site.test', $site->address);
            $this->assertEquals(PhpVersion::Php81, $site->php_version);
            $this->assertEquals(SiteType::Generic, $site->type);
            $this->assertEquals('/web', $site->web_folder);
            $this->assertEquals('git@github.com:protonemedia/eddy.git', $site->repository_url);
            $this->assertEquals('main', $site->repository_branch);
            $this->assertNotNull($site->deploy_key_public);
            $this->assertNotNull($site->deploy_key_private);

            Bus::assertDispatched(DeploySite::class);
        });
    }

    /** @test */
    public function it_can_refresh_the_deployment_token()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.show', [$this->server, $site]))
                ->waitForText('Site Overview')
                ->click('@refresh-deploy-token')
                ->waitForText('Are you sure you want to regenerate the deploy token?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The deploy token has been regenerated');

            $this->assertNotEquals($site->fresh()->deploy_token, $site->deploy_token);
        });
    }

    /** @test */
    public function it_can_change_the_repository_of_the_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.edit', [$this->server, $site]))
                ->type('repository_url', 'git@github.com:protonemedia/updated-repo.git')
                ->type('repository_branch', 'dev')
                ->press('Save')
                ->waitForText('Are you sure you want to update the site?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site settings have been saved and the site is being deployed.')
                ->assertRouteIs('servers.sites.deployments.show', [$this->server, $site, $site->latestDeployment]);

            $this->assertEquals('git@github.com:protonemedia/updated-repo.git', $site->fresh()->repository_url);
            $this->assertEquals('dev', $site->fresh()->repository_branch);

            Bus::assertDispatched(DeploySite::class);
            Bus::assertNotDispatched(UpdateSiteCaddyfile::class);
        });
    }

    /** @test */
    public function it_can_change_the_php_version_and_web_folder_of_the_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.edit', [$this->server, $site]))
                ->waitForText('PHP Version')
                ->select('php_version', 'php82')
                ->type('web_folder', '/public_html')
                ->press('Save')
                ->waitForText('Are you sure you want to update the site?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site settings are being saved. The Caddyfile will be updated and the site will be deployed.')
                ->assertRouteIs('servers.sites.edit', [$this->server, $site])
                ->assertSee('The Caddyfile for this site is currently being updated. This may take a few minutes.');

            // These will be updated after the Caddyfile has been updated
            $this->assertEquals(PhpVersion::Php81, $site->fresh()->php_version);
            $this->assertEquals('/public', $site->fresh()->web_folder);

            $site = $site->fresh();
            $user = $this->user->fresh();

            Bus::assertChained([
                new UpdateSiteCaddyfile($site, PhpVersion::Php82, '/public_html', $user),
                new CreateDeployment($site, $user),
            ]);
        });
    }

    /** @test */
    public function it_knows_wether_no_changes_have_been_made()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.edit', [$this->server, $site]))
                ->press('Save')
                ->waitForText('Are you sure you want to update the site?')
                ->press('@splade-confirm-confirm')
                ->waitForText('No changes were made');

            Bus::assertNotDispatched(DeploySite::class);
            Bus::assertNotDispatched(UpdateSiteCaddyfile::class);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.index', $this->server))
                ->click('tbody td')
                ->waitForRoute('servers.sites.show', [$this->server, $site])
                ->press('Delete Site')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The site is deleted and will be uninstalled from the server shortly')
                ->assertRouteIs('servers.sites.index', $this->server);

            Bus::assertDispatched(UninstallSite::class, function (UninstallSite $job) use ($site) {
                return $job->server->is($this->server)
                    && $job->sitePath === $site->path;
            });
        });
    }
}
