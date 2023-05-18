<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use ProtoneMedia\LaravelTaskRunner\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
            'name' => 'My Task',
            'user' => 'root',
            'type' => Task::class,
            'script' => 'echo "Hello World!";',
            'timeout' => 5,
            'status' => TaskStatus::Pending,
        ];
    }

    public function forServer(Server $server)
    {
        return $this->state(function (array $attributes) use ($server) {
            return [
                'server_id' => $server->id,
            ];
        });
    }
}
