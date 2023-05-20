<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Permission\Commands\Show;

class AgentRevoked extends Notification implements ShouldQueue
{
    use Queueable;
    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message = "Your role as an agent has been revoked. Kindly contact support if you have any queries";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->{'name'};
        return (new MailMessage)
                    ->subject('Agent role revoked')
                    ->greeting("Hello $name,")
                    ->line($this->message);
    }


}
