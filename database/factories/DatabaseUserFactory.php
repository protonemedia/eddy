<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DatabaseUser>
 */
class DatabaseUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'my_database',
            'server_id' => ServerFactory::new(),
            'installed_at' => now(),
        ];
    }

    public function forServer(Server $server): self
    {
        return $this->state(function (array $attributes) use ($server) {
            return [
                'server_id' => $server->id,
            ];
        });
    }
}
