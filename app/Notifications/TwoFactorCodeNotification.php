<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your login verification code')
            ->greeting('Hello '.($notifiable->name ?? '').'!')
            ->line('Your verification code is:')
            ->line('**'.$this->code.'**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not attempt to log in, please change your password immediately.');
    }
}
