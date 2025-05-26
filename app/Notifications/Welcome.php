<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Welcome extends Notification
{
    protected $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Welcome!')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Thank you for registering on our platform.')
            ->line('Your account has been successfully verified and you now have full access to our services.')
            ->action('Access your account', config('app.frontend_url') . '/login')
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }
}
