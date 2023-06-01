<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentAssigned extends Notification  implements ShouldQueue
{
    use Queueable;
    private string $message;


    /**
     * Create a new notification instance.
     */
    public function __construct()
    {

        $this->message = "You have been assigned as an agent. Kindly login to the agent mobile app with the link below";
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
        $agentAppUrl = config('app.agent-url');
        return (new MailMessage)
                    ->subject("Agent role assigned to you")
                    ->greeting("Hello $name,")
                    ->line($this->message)
                    ->action('Download agent app', $agentAppUrl);
    }

}
