<?php

namespace Database\Factories;

use App\Models\Server;
use App\Server\Firewall\RuleAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FirewallRule>
 */
class FirewallRuleFactory extends Factory
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
            'name' => 'SSH',
            'action' => RuleAction::Allow,
            'port' => 22,
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
