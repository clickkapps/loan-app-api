<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCreated extends Notification implements ShouldQueue
{
    use Queueable;
    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(public  string $tempPassword)
    {
        $appName = config('app.name');
        $this->message = sprintf("You have been added as an admin on %s with limited privileges. Use the code below as your password. You can later change your password in the settings of the dashboard", $appName);
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
        $appUrl = config('app.url');
        return (new MailMessage)
                    ->subject("Administrator's account created")
                    ->greeting("Hello $name,")
                    ->line($this->message)
                    ->line($this->tempPassword)
                    ->action('Go to dashboard', $appUrl);
    }
}
