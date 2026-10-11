<?php

namespace App\Notifications;

use App\Notifications\Channels\BrevoApiChannel;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    public function __construct(private readonly string $code, private readonly int $validForMinutes)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [BrevoApiChannel::class];
    }

    public function toBrevo(): array
    {
        $escapedCode = htmlspecialchars($this->code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $expiry = "This code expires in {$this->validForMinutes} minutes.";
        $notice = 'If you did not attempt to sign in, you can ignore this email.';

        return [
            'subject' => 'Your sign-in verification code',
            'textContent' => implode("\n\n", [
                "Verify it's you",
                "Your verification code is: {$this->code}",
                $expiry,
                $notice,
            ]),
            'htmlContent' => implode('', [
                '<p>Verify it\'s you</p>',
                '<p>Your verification code is: <strong>'.$escapedCode.'</strong></p>',
                '<p>'.$expiry.'</p>',
                '<p>'.$notice.'</p>',
            ]),
        ];
    }
}
