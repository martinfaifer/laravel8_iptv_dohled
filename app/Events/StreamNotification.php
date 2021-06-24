<?php

namespace App\Events;

use App\Http\Controllers\StreamController;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamsStatuses;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->streamsStatuses = StreamController::show_problematic_streams_as_alerts();
        // $this->streamsStatuses = $streamsStatuses;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('stream-statuses');
    }
}
