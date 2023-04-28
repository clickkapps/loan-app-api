<?php

namespace App\Events;

use App\Models\ConfigCusBoardingField;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CusBoardingFieldAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConfigCusBoardingField $configCusBoardingField;
    /**
     * Create a new event instance.
     */
    public function __construct(ConfigCusBoardingField $configCusBoardingField)
    {
        $this->configCusBoardingField = $configCusBoardingField;
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
