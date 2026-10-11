<?php

namespace App\Notifications\Channels;

use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class BrevoApiChannel
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof TwoFactorCodeNotification) {
            return;
        }

        $apiKey = config('services.brevo.api_key');
        $senderAddress = config('mail.from.address');
        $recipientAddress = $notifiable->routeNotificationFor('mail', $notification);

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new BrevoApiException('BREVO_API_KEY is not configured.');
        }

        if (! is_string($senderAddress) || trim($senderAddress) === '') {
            throw new BrevoApiException('MAIL_FROM_ADDRESS is not configured.');
        }

        if (! is_string($recipientAddress) || trim($recipientAddress) === '') {
            throw new BrevoApiException('The notification recipient address is unavailable.');
        }

        $payload = $notification->toBrevo();
        $recipientName = data_get($notifiable, 'name');
        $payload['sender'] = [
            'email' => $senderAddress,
            'name' => (string) config('mail.from.name', ''),
        ];
        $payload['to'] = [[
            'email' => $recipientAddress,
            'name' => is_string($recipientName) ? $recipientName : $recipientAddress,
        ]];

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withHeaders(['api-key' => $apiKey])
                ->timeout(8)
                ->post(self::ENDPOINT, $payload);
        } catch (ConnectionException) {
            throw new BrevoApiException('Could not connect to the Brevo API.');
        }

        if (! $response->successful()) {
            throw new BrevoApiException('The Brevo API returned HTTP '.$response->status().'.');
        }
    }
}
