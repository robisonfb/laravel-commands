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

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Email Change Check')
            ->line('Please click the button below to verify your new email address.')
            ->action('Verify Email', $url)
            ->line('If you did not request this change, no action is required.');
    }

    protected function verificationUrl($notifiable)
    {
        $url = URL::temporarySignedRoute(
            'email.change.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'        => $notifiable->getKey(),
                'new_email' => $this->newEmail,
                'hash'      => sha1($this->newEmail),
            ]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $params);

        return config('app.frontend_url') . '/verify-email-change?' . http_build_query([
            'id'        => $notifiable->getKey(),
            'new_email' => $this->newEmail,
            'hash'      => $params['hash'] ?? '',
            'expires'   => $params['expires'] ?? '',
            'signature' => $params['signature'] ?? '',
        ]);
    }
}
