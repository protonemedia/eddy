<?php

namespace Tests\Browser;

use App\Jobs\DeploySite;
use App\Models\Deployment;
use App\Models\Site;
use Database\Factories\DeploymentFactory;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteDeploymentTest extends DuskTestCase
{
    use DatabaseMigrations;
    use ServerTest;
    use PersistentBus;

    /** @test */
    public function it_can_trigger_a_deployment()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $this->assertCount(0, $site->deployments);

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.show', [$this->server, $site]))
                ->press('@deploy-site')
                ->waitForText('Are you sure you want to start a new deployment?')
                ->press('@splade-confirm-confirm')
                ->waitForText('Deployment queued');

            $this->assertCount(1, $site->fresh()->deployments);

            Bus::assertDispatched(DeploySite::class, function (DeploySite $job) use ($site) {
                return $job->deployment->site()->is($site);
            });
        });
    }

    /** @test */
    public function it_can_view_the_deployment_log()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            /** @var Deployment */
            $deployment = DeploymentFactory::new()->forSite($site)->create();
            $deployment->task->forceFill(['output' => 'Dummy output'])->save();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.deployments.index', [$this->server, $site]))
                ->click('tbody td')
                ->waitForText('Dummy output');
        });
    }
}
