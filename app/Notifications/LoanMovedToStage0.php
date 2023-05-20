<?php

namespace App\Notifications;

use App\Channels\PaymentChannel;
use App\Channels\SmsChannel;
use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class LoanMovedToStage0 extends Notification implements ShouldQueue
{
    use Queueable;

    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($loan, string $status = 'success', string $desc = 'loan request')
    {
        $amount = toCurrencyFormat($loan->{'amount_to_pay'});
        if($status == 'failed') {

            $this->message = "Sorry, your $desc failed. Loan amount: $amount. Kindly check your app for further details";

        } else {
            $this->message = "Congrats, your $desc has been approved. Loan amount: $amount. Kindly check your app for further details";
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
