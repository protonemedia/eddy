<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'user_id' => User::factory(),
            'personal_team' => true,
            'requires_subscription' => true,
        ];
    }

    /**
     * Indicate that the team should have a provisioned server.
     */
    public function withProvisionedServer(): static
    {
        return $this->has(
            ServerFactory::new()->provisioned(),
            'servers'
        );
    }

    /**
     * Indicate that the user should have a subscription plan.
     *
     * @return $this
     */
    public function withSubscription(string|int $planId = null): static
    {
        return $this->afterCreating(function (Team $team) use ($planId) {
            optional($team->customer)->update(['trial_ends_at' => null]);

            $team->subscriptions()->create([
                'name' => 'default',
                'paddle_id' => fake()->unique()->numberBetween(1, 1000),
                'paddle_status' => 'active',
                'paddle_plan' => $planId,
                'quantity' => 1,
                'trial_ends_at' => null,
                'paused_from' => null,
                'ends_at' => null,
            ]);
        });
    }

    /**
     * Indicate that the user should have a trial plan.
     *
     * @return $this
     */
    public function withTrial(int $trialDays = null): static
    {
        $trialDays = $trialDays ?: config('spark.billables.team.trial_days');

        return $this->afterCreating(function (Team $team) use ($trialDays) {
            if ($team->customer) {
                return $team->customer->update(['trial_ends_at' => now()->addDays($trialDays)]);
            }

            $team->customer()->create([
                'trial_ends_at' => $trialDays ? now()->addDays($trialDays) : null,
            ]);
        });
    }
}
