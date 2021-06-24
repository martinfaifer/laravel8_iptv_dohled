<?php

namespace App\Events;

use App\Http\Controllers\StreamHistoryController;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamInfoHistory implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $streamHistory;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($streamId)
    {
        $this->streamId = $streamId;
        $this->streamHistory = StreamHistoryController::stream_info_history_ten_for_events($this->streamId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('streamInfoTsHistory' . $this->streamId);
    }

    public function broadcastWith()
    {
        return [$this->streamHistory];
    }
}
