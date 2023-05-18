<?php

namespace App;

use App\Models\Server;
use App\Models\Team;
use Illuminate\Support\Arr;

class TeamSubscriptionOptions
{
    public function __construct(private Team $team)
    {
    }

    public function mustVerifySubscription(): bool
    {
        return $this->team->requires_subscription && config('eddy.subscriptions_enabled');
    }

    private function onTrial(): bool
    {
        return $this->team->onTrial();
    }

    private function isSubscribed(): bool
    {
        return $this->team->subscribed();
    }

    public function onTrialOrIsSubscribed(): bool
    {
        return $this->onTrial() || $this->isSubscribed();
    }

    public function planOptions(): array
    {
        if ($this->onTrial()) {
            return Arr::first(config('spark.billables.team.plans'))['options'];
        }

        if (! $this->isSubscribed()) {
            return [
                'max_servers' => 0,
                'max_sites_per_server' => 0,
                'max_deployments_per_site' => 5,
                'max_team_members' => 1,
            ];
        }

        return data_get($this->team->sparkPlan(), 'options');
    }

    private function limitNotReached(string $optionKey, int $currentValue): bool
    {
        if (! $this->mustVerifySubscription()) {
            return true;
        }

        $max = $this->planOptions()[$optionKey];

        if ($max === false) {
            return true;
        }

        return $currentValue < $max;
    }

    public function maxDeploymentsPerSite(): bool|int
    {
        if (! $this->mustVerifySubscription()) {
            return false;
        }

        return $this->planOptions()['max_deployments_per_site'];
    }

    public function countServers(): int
    {
        return $this->team->servers()->count();
    }

    public function canCreateServer(): bool
    {
        return $this->limitNotReached('max_servers', $this->countServers());
    }

    public function countSitesOnServer(Server $server): int
    {
        return $server->sites()->count();
    }

    public function canCreateSiteOnServer(Server $server): bool
    {
        return $this->limitNotReached('max_sites_per_server', $this->countSitesOnServer($server));
    }

    public function countTeamMembers(): int
    {
        // Also count the owner as a team member.
        return $this->team->users()->count() + 1;
    }

    public function canAddTeamMember(): bool
    {
        return $this->limitNotReached('max_team_members', $this->countTeamMembers());
    }
}
