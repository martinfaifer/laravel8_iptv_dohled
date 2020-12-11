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

class StreamInfoAudioBitrate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $streamId;
    public $audioBitrate;
    public $audioPid;
    public $audioDiscontinuities;
    public $audioScrambled;
    public $audioLanguage;
    public $audioAccess;
    public $audioDescription;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId, $audioBitrate, $audioPid, $audioDiscontinuities, $audioScrambled, $audioLanguage, $audioAccess, $audioDescription)
    {
        $this->streamId = $streamId;
        $this->audioBitrate = $audioBitrate;
        $this->audioPid = $audioPid;
        $this->audioDiscontinuities = $audioDiscontinuities;
        $this->audioScrambled = $audioScrambled;
        $this->audioLanguage = $audioLanguage;
        $this->audioAccess = $audioAccess;
        $this->audioDescription = $audioDescription;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTsAudioBitrate' . $this->streamId);
    }


    public function broadcastWith()
    {
        return [
            'bitrate' => $this->audioBitrate,
            'audioPid' => $this->audioPid,
            'audioDiscontinuities' => $this->audioDiscontinuities,
            'audioScrambled' => $this->audioScrambled,
            'audioLanguage' => $this->audioLanguage,
            'audioAccess' => $this->audioAccess,
            'audioDescription' => $this->audioDescription
        ];
    }
}
