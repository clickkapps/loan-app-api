<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $permission;
    public User $user;
    public bool $assigned;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $permission, bool $assigned)
    {
        $this->user = $user;
        $this->permission = $permission;
        $this->assigned = $assigned;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
