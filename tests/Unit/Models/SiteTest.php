<?php

namespace Tests\Unit\Models;

use App\Jobs\CreateDeployment;
use App\Jobs\DeploySite;
use App\Jobs\UpdateSiteCaddyfile;
use App\Models\Deployment;
use App\Models\DeploymentStatus;
use App\Models\Site;
use App\Models\SiteType;
use App\Models\TlsSetting;
use App\Models\User;
use App\Server\PhpVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_it_returns_80_if_tls_setting_is_off()
    {
        $site = Site::factory()->create(['tls_setting' => TlsSetting::Off]);

        $this->assertSame(80, $site->port);
    }

    /** @test */
    public function it_can_it_returns_443_if_tls_setting_is_on()
    {
        $site = Site::factory()->create(['tls_setting' => TlsSetting::Auto]);

        $this->assertSame(443, $site->port);
    }

    /** @test */
    public function it_has_a_url_attribute()
    {
        // Create a new Site instance with a mocked TlsSetting
        $site = Site::factory()->make([
            'address' => 'example.com',
            'tls_setting' => TlsSetting::Off,
        ]);

        // Check that the URL is correct when TLS is off
        $this->assertEquals('http://example.com', $site->url);

        // Update the Site instance to use TLS
        $site->tls_setting = TlsSetting::Auto;

        // Check that the URL is correct when TLS is on
        $this->assertEquals('https://example.com', $site->url);
    }

    /** @test */
    public function it_can_laravel_site_generates_expected_environment_variables()
    {
        $site = Site::factory()->create(['type' => SiteType::Laravel]);
        $env = $site->generateEnvironmentVariables();

        $this->assertArrayHasKey('APP_KEY', $env);
        $this->assertArrayHasKey('APP_URL', $env);
        $this->assertEquals($env['APP_URL'], 'https://'.$site->address);
    }

    /** @test */
    public function it_can_generic_site_generates_expected_environment_variables()
    {
        $site = Site::factory()->create(['type' => SiteType::Generic]);
        $env = $site->generateEnvironmentVariables();

        $this->assertEquals([], $env);
    }

    /** @test */
    public function it_can_wordpress_site_generates_expected_environment_variables()
    {
        $site = Site::factory()->create(['type' => SiteType::Wordpress]);
        $env = $site->generateEnvironmentVariables();

        $this->assertArrayHasKey('AUTH_KEY', $env);
        $this->assertArrayHasKey('AUTH_SALT', $env);
        $this->assertArrayHasKey('LOGGED_IN_KEY', $env);
        $this->assertArrayHasKey('LOGGED_IN_SALT', $env);
        $this->assertArrayHasKey('NONCE_KEY', $env);
        $this->assertArrayHasKey('NONCE_SALT', $env);
        $this->assertArrayHasKey('SECURE_AUTH_KEY', $env);
        $this->assertArrayHasKey('SECURE_AUTH_SALT', $env);
    }

    /** @test */
    public function it_can_create_a_deployment_and_dispatch_a_job()
    {
        TaskRunner::fake();
        Queue::fake();

        /** @var Site */
        $site = Site::factory()->create(['zero_downtime_deployment' => false]);

        $deployment = $site->deploy();

        Queue::assertPushed(DeploySite::class, function (DeploySite $job) use ($deployment) {
            return $job->deployment->is($deployment);
        });
    }

    /** @test */
    public function it_updates_the_caddyfile_and_deploys_the_site()
    {
        Bus::fake();

        /** @var Site */
        $site = Site::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        $phpVersion = PhpVersion::Php82;
        $webFolder = "/var/www/{$site->id}/public";

        $site->updateCaddyfile($phpVersion, $webFolder, $user);

        Bus::assertChained([
            new UpdateSiteCaddyfile($site, $phpVersion, $webFolder, $user),
            new CreateDeployment($site, $user),
        ]);
    }

    /** @test */
    public function it_returns_true_when_latest_deployment_is_pending()
    {
        $deployment = Deployment::factory()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $this->assertTrue($deployment->site->isDeploying());
    }

    /** @test */
    public function it_returns_false_when_latest_deployment_is_not_pending()
    {
        $deployment = Deployment::factory()->create([
            'status' => DeploymentStatus::Finished,
        ]);

        $this->assertFalse($deployment->site->isDeploying());
    }
}
