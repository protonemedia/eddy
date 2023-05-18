<?php

namespace Tests\Feature;

use App\Jobs\DeploySite;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SiteDeploymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_start_a_deployment_with_the_correct_token()
    {
        Bus::fake();

        $site = SiteFactory::new()->create();

        $this->postJson(route('site.deployWithToken', [
            'site' => $site,
            'token' => $site->deploy_token,
        ]))->assertOk();

        Bus::assertDispatched(DeploySite::class, function (DeploySite $job) use ($site) {
            return $job->deployment->site->is($site);
        });
    }

    /** @test */
    public function it_cant_start_a_deployment_with_a_wrong_token()
    {
        Bus::fake();

        $site = SiteFactory::new()->create();

        $this->postJson(route('site.deployWithToken', [
            'site' => $site,
            'token' => 'nope',
        ]))->assertForbidden();

        Bus::assertNotDispatched(DeploySite::class);
    }
}
