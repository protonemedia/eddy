<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\Splade\Facades\Splade;

class SiteUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Site $site)
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
            new PrivateChannel('teams.'.$this->site->server->team_id),
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
