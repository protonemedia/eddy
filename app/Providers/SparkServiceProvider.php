<?php

namespace App\Providers;

use App\Models\Server;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Paddle\Cashier;
use Spark\Plan;
use Spark\Spark;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Cashier::ignoreMigrations();

        if (! class_exists(Spark::class)) {
            return;
        }

        Spark::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! class_exists(Spark::class)) {
            return;
        }

        Spark::billable(Team::class)->resolve(function (Request $request) {
            return $request->user()->currentTeam;
        });

        Spark::billable(Team::class)->authorize(function (Team $billable, Request $request) {
            return $request->user() && $request->user()->ownsTeam($billable);
        });

        Spark::billable(Team::class)->checkPlanEligibility(function (Team $billable, Plan $plan) {
            $maxServers = $plan->options['max_servers'];
            $maxSitesPerServer = $plan->options['max_sites_per_server'];
            $maxTeamMembers = $plan->options['max_team_members'];

            if ($maxServers !== false && $billable->subscriptionOptions()->countServers() > $maxServers) {
                throw ValidationException::withMessages([
                    'plan' => __('You have too many servers for this plan.'),
                ]);
            }

            if ($maxTeamMembers !== false && $billable->subscriptionOptions()->countTeamMembers() > $maxTeamMembers) {
                throw ValidationException::withMessages([
                    'plan' => __('You have too many team members for this plan.'),
                ]);
            }

            if ($maxSitesPerServer === false) {
                return;
            }

            $billable->servers->each(function (Server $server) use ($billable, $maxSitesPerServer) {
                if ($billable->subscriptionOptions()->countSitesOnServer($server) > $maxSitesPerServer) {
                    throw ValidationException::withMessages([
                        'plan' => __('You have too many sites on one of your servers for this plan.'),
                    ]);
                }
            });
        });
    }
}
