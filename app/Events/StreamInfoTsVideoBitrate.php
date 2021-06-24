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

class StreamInfoTsVideoBitrate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $videoBitrate;
    public $videoPid;
    public $discontinuities;
    public $scrambled;
    public $videoAccess;
    public $videoDescription;
    public $videoPackets;
    public $videoClear;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId, $videoBitrate, $videoPid, $discontinuities, $scrambled, $videoAccess, $videoDescription, $videoPackets, $videoClear)
    {
        $this->videoBitrate = $videoBitrate;
        $this->videoPid = $videoPid;
        $this->discontinuities = $discontinuities;
        $this->scrambled = $scrambled;
        $this->videoAccess = $videoAccess;
        $this->videoDescription = $videoDescription;
        $this->videoPackets = $videoPackets;
        $this->videoClear = $videoClear;

        $this->streamId = $streamId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTsVideoBitrate' . $this->streamId);
    }

    public function broadcastWith()
    {
        return [
            'bitrate' => $this->videoBitrate,
            'videoPid' => $this->videoPid,
            'discontinuities' => $this->discontinuities,
            'scrambled' => $this->scrambled,
            'access' => $this->videoAccess,
            'videoDescription' => $this->videoDescription,
            'videoPackets' => $this->videoPackets,
            'videoClear' => $this->videoClear
        ];
    }
}
