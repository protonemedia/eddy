<?php

namespace Database\Factories;

use App\Models\DeploymentStatus;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deployment>
 */
class DeploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => SiteFactory::new(),
            'user_id' => UserFactory::new(),
            'task_id' => TaskFactory::new(),
            'status' => DeploymentStatus::Pending,
        ];
    }

    public function forSite(Site $site): self
    {
        return $this->state(function (array $attributes) use ($site) {
            return [
                'site_id' => $site->id,
            ];
        });
    }
}
