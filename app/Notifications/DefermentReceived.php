<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class DefermentReceived extends Notification
{
    use Queueable;

    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $amount)
    {
        $this->message = "An amount of $amount has been received. Thank you for deferring. We are glad to extend your repayment deadline";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    #[ArrayShape(['phone' => "mixed", 'message' => "string"])] public function toSMS($notifiable): array
    {
        $encoded = json_encode($notifiable);
        Log::info("notifiable: $encoded");

        return [
            'phone' => $notifiable->{'email'},
            'message' => $this->message,
        ];
    }

}
