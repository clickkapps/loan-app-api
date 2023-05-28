<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class RepaymentReceived extends Notification
{
    use Queueable;
    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $paymentType , string $amount)
    {
        if($paymentType == 'full-repayment'){
            $this->message = "An amount of $amount has been received. Thank you for the full loan repayment. You can now request another loan with more flexible terms";
        }else{
            $this->message = "An amount of $amount has been received. Thank you for the partial loan repayment. You're entreated make full payment on time";
        }

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
