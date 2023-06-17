<?php

namespace App\Events;
use App\Models\LoanApplication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanApplicationAssignedToAgent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public ?LoanApplication $loanApplication;

    /**
     * Create a new event instance.
     */
    public function __construct($loanApplication)
    {
        $this->loanApplication = $loanApplication;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('loan-assigned');
    }
}
