<?php

namespace Tests\Unit;

use App\Models\Team;
use Database\Factories\ServerFactory;
use Database\Factories\SiteFactory;
use Database\Factories\TeamFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamSubscriptionOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config([
            'eddy.subscriptions_enabled' => true,
            'spark.billables.team.plans' => [
                [
                    'name' => 'Compact',
                    'short_description' => 'compact',
                    'monthly_id' => 10,
                    'yearly_id' => 20,
                    'features' => [],
                    'archived' => false,
                    'options' => [
                        'max_servers' => 3,
                        'max_sites_per_server' => 3,
                        'max_team_members' => 3,
                        'has_backups' => true,
                    ],
                ],
                [
                    'name' => 'unlimited',
                    'short_description' => 'Unlimited',
                    'monthly_id' => 30,
                    'yearly_id' => 40,
                    'features' => [],
                    'archived' => false,
                    'options' => [
                        'max_servers' => false,
                        'max_sites_per_server' => false,
                        'max_team_members' => false,
                        'has_backups' => true,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_picks_the_first_plan_when_on_trial()
    {
        /** @var Team */
        $team = TeamFactory::new()->withTrial()->create();

        $options = $team->subscriptionOptions();

        $planOptions = $options->planOptions();

        $this->assertEquals(3, $planOptions['max_servers']);
        $this->assertEquals(3, $planOptions['max_sites_per_server']);
        $this->assertEquals(3, $planOptions['max_team_members']);
    }

    /** @test */
    public function it_can_determine_if_a_team_can_create_a_server()
    {
        /** @var Team */
        $team = TeamFactory::new()->withSubscription(10)->create();

        $this->assertFalse($team->onTrial());
        $this->assertTrue($team->subscribedToPlan(10));

        $options = $team->subscriptionOptions();

        $maxServers = $options->planOptions()['max_servers'];

        for ($i = 0; $i < $maxServers; $i++) {
            $this->assertTrue($options->canCreateServer());
            ServerFactory::new()->forTeam($team)->create();
        }

        $this->assertFalse($options->canCreateServer());

        // Test unlimited plan:
        $team->forceFill(['requires_subscription' => false])->save();
        $this->assertTrue($options->canCreateServer());
    }

    /** @test */
    public function it_can_determine_if_a_team_can_create_a_site_on_a_server()
    {
        /** @var Team */
        $team = TeamFactory::new()->withSubscription(10)->create();

        $this->assertFalse($team->onTrial());
        $this->assertTrue($team->subscribedToPlan(10));

        $server = ServerFactory::new()->forTeam($team)->create();
        $options = $team->subscriptionOptions();

        $maxSites = $options->planOptions()['max_sites_per_server'];

        for ($i = 0; $i < $maxSites; $i++) {
            $this->assertTrue($options->canCreateSiteOnServer($server));
            SiteFactory::new()->forServer($server)->create();
        }

        $this->assertFalse($options->canCreateSiteOnServer($server));

        // Test unlimited plan:
        $team->forceFill(['requires_subscription' => false])->save();
        $this->assertTrue($options->canCreateSiteOnServer($server));
    }

    /** @test */
    public function it_can_determine_if_a_team_can_add_a_team_member()
    {
        /** @var Team */
        $team = TeamFactory::new()->withSubscription(10)->create();

        $this->assertFalse($team->onTrial());
        $this->assertTrue($team->subscribedToPlan(10));

        $options = $team->subscriptionOptions();

        $maxMembers = $options->planOptions()['max_team_members'];

        // We start at 1 because the team owner is already a member
        for ($i = 1; $i < $maxMembers; $i++) {
            $this->assertTrue($options->canAddTeamMember());
            $user = UserFactory::new()->create();
            $team->users()->attach($user);
        }

        $this->assertFalse($options->canAddTeamMember());

        // Test unlimited plan:
        $team->forceFill(['requires_subscription' => false])->save();
        $this->assertTrue($options->canAddTeamMember());
    }

    /** @test */
    public function it_can_determine_if_an_option_is_unlimited()
    {
        /** @var Team */
        $team = TeamFactory::new()->withSubscription(30)->create();

        $this->assertFalse($team->onTrial());
        $this->assertTrue($team->subscribedToPlan(30));

        $options = $team->subscriptionOptions();

        $maxMembers = $options->planOptions()['max_team_members'];
        $this->assertFalse($maxMembers);

        $users = UserFactory::new()->count(10)->create();
        $team->users()->attach($users);

        $this->assertTrue($options->canAddTeamMember());
    }
}
