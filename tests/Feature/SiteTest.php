<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifySubscriptionStatus;
use App\TeamSubscriptionOptions;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_forbids_the_user_from_adding_sites_when_it_reached_the_subscription_plan_limit()
    {
        $user = UserFactory::new()->withPersonalTeam(provisionedServer: true)->create();

        $server = $user->currentTeam->servers->first();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('canCreateSiteOnServer')->andReturn(false);
            });
        });

        $this->actingAs($user)
            ->withoutMiddleware(VerifySubscriptionStatus::class)
            ->post(route('servers.sites.store', $server))
            ->assertForbidden();
    }
}
