<?php

namespace App\Events;

use App\Http\Controllers\User\UserController;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEdit implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->user = UserController::userDetail();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('UserEdit' . $this->userId);
    }

    public function broadcastWith()
    {
        return [$this->user];
    }
}
