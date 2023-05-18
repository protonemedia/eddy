<?php

namespace App\Events;

use App\Models\Deployment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\Splade\Facades\Splade;

class DeploymentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Deployment $deployment)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('teams.'.$this->deployment->site->server->team_id),
        ];
    }

    /**
     * Get the data that should be sent with the broadcasted event.
     *
     * @return array|null
     */
    public function broadcastWith()
    {
        return [
            Splade::refreshOnEvent(),
        ];
    }
}
