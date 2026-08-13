<?php

namespace App\Services;

use App\Models\OrderNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationDeliveryService
{
    public function deliver(OrderNotification $notification): array
    {
        return match ($notification->channel) {
            'internal' => ['status' => 'sent', 'provider_message_id' => null],
            'email' => $this->sendEmail($notification),
            'sms' => $this->sendSms($notification),
            'telegram' => $this->sendTelegram($notification),
            'whatsapp' => $this->sendWhatsApp($notification),
            default => throw new \InvalidArgumentException('Notification kanali noto\'g\'ri.'),
        };
    }

    private function sendEmail(OrderNotification $notification): array
    {
        if (! $notification->recipient) {
            return $this->skipped('Email recipient mavjud emas.');
        }

        Mail::raw($notification->message, function ($mail) use ($notification): void {
            $mail->to($notification->recipient)
                ->subject('CRM Document: ' . $notification->event);
        });

        return ['status' => 'sent', 'provider_message_id' => null];
    }

    private function sendSms(OrderNotification $notification): array
    {
        $config = (array) config('services.notifications.sms', []);
        if (empty($config['endpoint'])) {
            return $this->skipped('SMS endpoint sozlanmagan.');
        }
        if (! $notification->recipient) {
            return $this->skipped('SMS recipient mavjud emas.');
        }

        $response = Http::asForm()->timeout((int) ($config['timeout'] ?? 15))->post($config['endpoint'], [
            'login' => $config['login'] ?? null,
            'password' => $config['password'] ?? null,
            'from' => $config['sender'] ?? null,
            'phone' => $notification->recipient,
            'message' => $notification->message,
        ]);
        $response->throw();

        return [
            'status' => 'sent',
            'provider_message_id' => data_get($response->json(), 'id') ?: data_get($response->json(), 'message_id'),
        ];
    }

    private function sendTelegram(OrderNotification $notification): array
    {
        $config = (array) config('services.notifications.telegram', []);
        if (! ($config['bot_token'] ?? null)) {
            return $this->skipped('Telegram bot token sozlanmagan.');
        }
        if (! $notification->recipient) {
            return $this->skipped('Telegram chat ID mavjud emas.');
        }

        $endpoint = rtrim((string) ($config['endpoint'] ?? 'https://api.telegram.org'), '/');
        $response = Http::timeout((int) ($config['timeout'] ?? 15))
            ->post($endpoint . '/bot' . $config['bot_token'] . '/sendMessage', [
                'chat_id' => $notification->recipient,
                'text' => $notification->message,
            ]);
        $response->throw();

        return [
            'status' => 'sent',
            'provider_message_id' => data_get($response->json(), 'result.message_id'),
        ];
    }

    private function sendWhatsApp(OrderNotification $notification): array
    {
        $config = (array) config('services.notifications.whatsapp', []);
        if (! ($config['token'] ?? null) || ! ($config['phone_number_id'] ?? null)) {
            return $this->skipped('WhatsApp Cloud API sozlanmagan.');
        }
        if (! $notification->recipient) {
            return $this->skipped('WhatsApp recipient mavjud emas.');
        }

        $endpoint = rtrim((string) ($config['endpoint'] ?? 'https://graph.facebook.com/v20.0'), '/');
        $response = Http::withToken($config['token'])
            ->timeout((int) ($config['timeout'] ?? 15))
            ->post($endpoint . '/' . $config['phone_number_id'] . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $notification->recipient,
                'type' => 'text',
                'text' => ['body' => $notification->message],
            ]);
        $response->throw();

        return [
            'status' => 'sent',
            'provider_message_id' => data_get($response->json(), 'messages.0.id'),
        ];
    }

    private function skipped(string $reason): array
    {
        return ['status' => 'skipped', 'provider_message_id' => null, 'error_message' => $reason];
    }
}
