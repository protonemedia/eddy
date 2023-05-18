<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cron>
 */
class CronFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'server_id' => ServerFactory::new(),
            'user' => config('eddy.server_defaults.username'),
            'command' => 'php -v',
            'expression' => '* * * * *',
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
