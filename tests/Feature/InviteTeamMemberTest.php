<?php

namespace Tests\Feature;

use App\TeamSubscriptionOptions;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_forbids_the_user_from_inviting_members_when_it_reached_the_subscription_plan_limit()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('canAddTeamMember')->andReturn(false);
            });
        });

        $this->actingAs($user)
            ->postJson(route('team-members.store', $user->currentTeam), [
                'email' => 'dummy@example.com',
            ])
            ->assertJsonValidationErrorFor('email')
            ->assertSee('You have reached the maximum number of team members for your subscription plan');
    }
}
