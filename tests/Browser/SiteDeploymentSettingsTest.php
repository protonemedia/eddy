<?php

namespace Tests\Browser;

use App\Models\Site;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use Tests\ServerTest;

class SiteDeploymentSettingsTest extends DuskTestCase
{
    use DatabaseMigrations;
    use ServerTest;

    /** @test */
    public function it_can_change_the_deployment_settings_of_a_zero_downtime_site()
    {
        $this->browse(function (Browser $browser) {
            /** @var Site */
            $site = SiteFactory::new()->forServer($this->server)->forRepository()->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.sites.deployment-settings.edit', [$this->server, $site]))
                ->waitForText('Deployment Settings')
                ->type('deployment_releases_retention', '30')
                ->type('deploy_notification_email', 'deployments@example.com')
                ->type('shared_directories', 'my-storage')
                ->type('shared_files', 'my-env-file')
                ->type('writeable_directories', 'my-writable-directory')
                ->type('hook_before_updating_repository', '#1')
                ->type('hook_after_updating_repository', '#2')
                ->type('hook_before_making_current', '#3')
                ->type('hook_after_making_current', '#4')
                ->press('Save')
                ->waitForText('The deployment settings have been saved');

            $site->refresh();

            $this->assertEquals(30, $site->deployment_releases_retention);
            $this->assertEquals('deployments@example.com', $site->deploy_notification_email);
            $this->assertEquals(['my-storage'], $site->shared_directories->toArray());
            $this->assertEquals(['my-env-file'], $site->shared_files->toArray());
            $this->assertEquals(['my-writable-directory'], $site->writeable_directories->toArray());
            $this->assertEquals('#1', $site->hook_before_updating_repository);
            $this->assertEquals('#2', $site->hook_after_updating_repository);
            $this->assertEquals('#3', $site->hook_before_making_current);
            $this->assertEquals('#4', $site->hook_after_making_current);
        });
    }
}
