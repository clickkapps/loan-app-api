<?php

namespace App\Notifications;

use App\Channels\PaymentChannel;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DefermentInitiated extends Notification implements ShouldQueue
{
    use Queueable;
    private Payment $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [PaymentChannel::class];
    }

    public function toPaymentGateway($notifiable): Payment
    {
        $encoded = json_encode($notifiable);
        Log::info("notifiable: $encoded");

        return $this->payment;
    }
}
