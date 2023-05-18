<?php

namespace Database\Factories;

use App\Models\Server;
use App\Signal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Daemon>
 */
class DaemonFactory extends Factory
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
            'directory' => '/home/eddy',
            'processes' => 1,
            'stop_wait_seconds' => 10,
            'stop_signal' => Signal::TERM,
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
