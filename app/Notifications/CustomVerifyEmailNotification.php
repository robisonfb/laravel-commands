<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Config, URL};

class CustomVerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $hash = sha1($notifiable->getEmailForVerification());

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => $hash,
            ]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $params);

        return config('app.frontend_url') . '/verify-email?' . http_build_query([
            'id'        => $notifiable->getKey(),
            'hash'      => $hash,
            'expires'   => $params['expires'] ?? '',
            'signature' => $params['signature'] ?? '',
        ]);
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('	Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}
