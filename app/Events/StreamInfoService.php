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

class StreamInfoService implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $tsid;
    public $pmtpid;
    public $pcrpid;
    public $name;
    public $provider;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId, $tsid, $pmtpid, $pcrpid, $name, $provider)
    {
        $this->streamId = $streamId;
        $this->tsid = $tsid;
        $this->pmtpid = $pmtpid;
        $this->pcrpid = $pcrpid;
        $this->name = $name;
        $this->provider = $provider;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTsService' . $this->streamId);
    }

    public function broadcastWith()
    {
        return [
            'pcrpid' => $this->pcrpid,
            'pmtpid' => $this->pmtpid,
            'tsid' => $this->tsid,
            'name' => $this->name,
            'provider' => $this->provider
        ];
    }
}
