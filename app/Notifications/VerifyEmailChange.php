<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Config, URL};

class VerifyEmailChange extends Notification
{
    protected $newEmail;

    public function __construct(string $newEmail)
    {
        $this->newEmail = $newEmail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    protected function verificationUrl($notifiable)
    {
        $hash = sha1($notifiable->getEmailForVerification());

        return URL::temporarySignedRoute(
            'email.change.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'        => $notifiable->getKey(),
                'hash'      => $hash,
                'new_email' => $this->newEmail,
            ]
        );
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Email Change Check')
            ->line('Please click the button below to verify your new email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('If you did not request this change, no action is required.');
    }
}
