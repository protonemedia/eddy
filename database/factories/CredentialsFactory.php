<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use App\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credentials>
 */
class CredentialsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'My Credentials',
            'user_id' => UserFactory::new(),
        ];
    }

    public function provider(Provider $provider)
    {
        if ($provider === Provider::DigitalOcean) {
            return $this->digitalOcean();
        }

        if ($provider === Provider::HetznerCloud) {
            return $this->hetznerCloud();
        }

        if ($provider === Provider::Github) {
            return $this->github();
        }

        return $this->state(function (array $attributes) use ($provider) {
            return [
                'provider' => $provider,
            ];
        });
    }

    public function digitalOcean()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'My Digital Ocean Credentials',
                'provider' => Provider::DigitalOcean,
                'credentials' => [
                    'digital_ocean_token' => 'valid-token',
                ],
            ];
        });
    }

    public function hetznerCloud()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'My Hetzner Cloud Credentials',
                'provider' => Provider::HetznerCloud,
                'credentials' => [
                    'hetzner_cloud_token' => 'valid-token',
                ],
            ];
        });
    }

    public function vagrant()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'My Vagrant Credentials',
                'provider' => Provider::Vagrant,
            ];
        });
    }

    public function github()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'My Github Credentials',
                'provider' => Provider::Github,
                'credentials' => [
                    'token' => 'valid-token',
                ],
            ];
        });
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function forTeam(Team $team)
    {
        return $this->state(function (array $attributes) use ($team) {
            return [
                'team_id' => $team->id,
            ];
        });
    }
}
