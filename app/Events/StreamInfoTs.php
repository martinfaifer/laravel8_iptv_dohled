<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamInfoTs implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $country;
    public $pids;
    public $clearpids;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId, $country, $pids, $clearpids)
    {
        $this->streamId = $streamId;
        $this->country = $country;
        $this->pids = $pids;
        $this->clearpids = $clearpids;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTS' . $this->streamId);
    }


    public function broadcastWith()
    {
        return [
            'country' => $this->country,
            'pids' => $this->pids,
            'clearpids' => $this->clearpids
        ];
    }
}
