<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAdminPasswordGenerated extends Notification implements  ShouldQueue
{
    use Queueable;
    private string $message;
    private string $tempPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $tempPassword)
    {
        $this->message = "You requested change of password. Use the temporary password below to login and you'd be asked to set a new password";
        $this->tempPassword = $tempPassword;
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
            ->subject("Password reset")
            ->greeting("Hello $name,")
            ->line($this->message)
            ->line($this->tempPassword);
    }

}
