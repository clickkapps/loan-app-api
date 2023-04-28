<?php

namespace App\Events;

use App\Models\ConfigCusBoardingPage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CusBoardingPageAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConfigCusBoardingPage $configCusBoardingPage;
    /**
     * Create a new event instance.
     */
    public function __construct(ConfigCusBoardingPage $configCusBoardingPage)
    {
        $this->configCusBoardingPage = $configCusBoardingPage;
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
