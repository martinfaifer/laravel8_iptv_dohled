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

class StreamInfoCa implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // $caDescription ?? null, $caAccess, $caScrambled
    public $streamId;
    public $caDescription;
    public $caAccess;
    public $caScrambled;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId, $caDescription, $caAccess, $caScrambled)
    {
        $this->streamId = $streamId;
        $this->caDescription = $caDescription;
        $this->caAccess = $caAccess;
        $this->caScrambled = $caScrambled;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTsCa' . $this->streamId);
    }

    public function broadcastWith()
    {
        return [
            'description' => $this->caDescription,
            'access' => $this->caAccess,
            'scrambled' => $this->caScrambled,
        ];
    }
}
