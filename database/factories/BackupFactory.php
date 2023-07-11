<?php

namespace Database\Factories;

use App\Models\Backup;
use App\Models\Disk;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Backup>
 */
class BackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'My Backup',
            'retention' => 14,
            'cron_expression' => '0 0 * * *',
            'server_id' => ServerFactory::new()->provisioned()->withDatabases(),
            'disk_id' => DiskFactory::new(),
        ];
    }

    public function forDatabases(Collection $databases): self
    {
        return $this->afterCreating(function (Backup $backup) use ($databases) {
            $backup->databases()->sync($databases);
        });
    }

    public function forDisk(Disk $disk): self
    {
        return $this->state(function (array $attributes) use ($disk) {
            return [
                'disk_id' => $disk->id,
            ];
        });
    }

    public function forServer(Server $server): self
    {
        return $this->state(function (array $attributes) use ($server) {
            return [
                'server_id' => $server->id,
            ];
        });
    }

    public function createdByUser(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'created_by_user_id' => $user->id,
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Backup $backup) {
            if ($backup->databases()->doesntExist()) {
                $backup->databases()->sync($backup->server->databases);
            }
        });
    }
}
