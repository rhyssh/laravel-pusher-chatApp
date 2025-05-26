<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class StatusTyping implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $user;
    public $isTyping;

    public function __construct(User $user, bool $isTyping)
    {
        $this->user = $user;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return new Channel('typing');
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user,
            'isTyping' => $this->isTyping,
        ];
    }
}
