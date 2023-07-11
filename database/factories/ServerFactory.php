<?php

namespace Database\Factories;

use App\Infrastructure\Entities\ServerStatus;
use App\Models\Server;
use App\Models\Team;
use App\Provider;
use App\Server\Software;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $keyPair = Dummies::ed25519KeyPair();

        return [
            'name' => 'Test Server',
            'provider' => Provider::Vagrant,
            'status' => ServerStatus::Running,
            'team_id' => TeamFactory::new(),
            'ssh_port' => 22,
            'public_ipv4' => '1.1.1.1',
            'region' => 18,
            'type' => 20,
            'image' => 9,

            'created_by_user_id' => UserFactory::new(),

            'username' => config('eddy.server_defaults.username'),
            'password' => 'password',
            'database_password' => 'password',
            'working_directory' => config('eddy.server_defaults.working_directory'),

            'public_key' => $keyPair->publicKey,
            'private_key' => $keyPair->privateKey,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Server $server) {
            if (! $server->getAttributeValue('credentials_id') && $server->provider !== Provider::Vagrant) {
                $server->credentials_id = CredentialsFactory::new()->provider($server->provider)->create()->id;
            }
        });
    }

    public function provider(Provider $provider)
    {
        return $this->state(function (array $attributes) use ($provider) {
            return [
                'provider' => $provider,
            ];
        });
    }

    public function notProvisioned()
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_id' => null,
                'public_ipv4' => null,
                'status' => ServerStatus::New,
            ];
        });
    }

    public function waitingToConnect()
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_id' => 17,
                'public_ipv4' => null,
                'status' => ServerStatus::Starting,
            ];
        });
    }

    public function provisioned()
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_id' => 1,
                'public_ipv4' => '192.168.60.61',
                'status' => ServerStatus::Running,
                'installed_software' => Software::defaultStack(),
                'provisioned_at' => now(),
            ];
        });
    }

    public function forTeam(Team $team): self
    {
        return $this->state(function (array $attributes) use ($team) {
            return [
                'team_id' => $team->id,
            ];
        });
    }

    public function withDatabases(int $count = 3): self
    {
        return $this->has(
            DatabaseFactory::new()->count($count)->state(new Sequence(
                fn (Sequence $sequence) => ['name' => 'my_database'.$sequence->index],
            ))
        );
    }
}
