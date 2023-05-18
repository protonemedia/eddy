<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateDeployment;
use App\Jobs\DeploySite;
use App\Models\Site;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateDeploymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_starts_a_deployment()
    {
        Bus::fake();

        /** @var Site */
        $site = SiteFactory::new()->create(['zero_downtime_deployment' => false]);
        $this->assertEmpty($site->deployments);

        $job = new CreateDeployment($site, $site->server->createdByUser);
        $job->handle();

        $deployment = $site->deployments()->first();
        $this->assertNotNull($deployment);

        Bus::assertDispatched(function (DeploySite $job) use ($deployment) {
            return $job->deployment->is($deployment);
        });
    }
}
