<?php

namespace Tests\Feature;

use App\Models\User;
use App\TeamSubscriptionOptions;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VerifySubscriptionStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_forbids_the_user_from_viewing_servers_without_a_trial_or_subscription_plan()
    {
        /** @var User */
        $user = UserFactory::new()->withPersonalTeam()->create();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('mustVerifySubscription')->andReturn(true);
                $mock->shouldReceive('onTrialOrIsSubscribed')->andReturn(false);
            });
        });

        $this->actingAs($user)
            ->post(route('servers.index'))
            ->assertRedirectToRoute('no-subscription');
    }

    /** @test */
    public function it_allows_the_user_to_view_servers_with_a_trial_or_subscription_plan()
    {
        /** @var User */
        $user = UserFactory::new()->withPersonalTeam()->create();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('mustVerifySubscription')->andReturn(true);
                $mock->shouldReceive('onTrialOrIsSubscribed')->andReturn(true);
            });
        });

        $this->actingAs($user)
            ->get(route('servers.index'))
            ->assertOk();
    }

    /** @test */
    public function it_allows_the_user_to_view_servers_when_no_verification_is_required()
    {
        /** @var User */
        $user = UserFactory::new()->withPersonalTeam()->create();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('mustVerifySubscription')->andReturn(false);
            });
        });

        $this->actingAs($user)
            ->get(route('servers.index'))
            ->assertOk();
    }
}
