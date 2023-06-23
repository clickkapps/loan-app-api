<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class CampaignCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Campaign $campaign)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return [SmsChannel::class];
    }


    #[ArrayShape(['phone' => "mixed", 'message' => "string"])] public function toSMS($notifiable): array
    {
        $encoded = json_encode($notifiable);
        Log::info("notifiable: $encoded");

        return [
            'phone' => $notifiable->{'email'},
            'message' => $this->campaign->{'message'},
        ];
    }

}
