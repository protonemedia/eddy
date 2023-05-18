<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\Splade\Facades\Splade;

class ServerDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $serverId, public $teamId)
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
            new PrivateChannel('teams.'.$this->teamId),
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
            Splade::redirectOnEvent()->route('servers.index'),
            Splade::toastOnEvent()->warning(__('The server has been deleted.')),
        ];
    }
}
