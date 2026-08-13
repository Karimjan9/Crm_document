<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Jobs\DeliverOrderNotification;

class CustomerNotificationService
{
    public const EVENTS = [
        'order_received',
        'payment_received',
        'missing_files',
        'deadline_approaching',
        'document_ready',
        'courier_sent',
        'order_delivered',
        'status_changed',
        'order_created',
    ];

    public function queueEvent(Order $order, string $event, ?string $message = null, ?array $channels = null): array
    {
        if (! in_array($event, self::EVENTS, true)) {
            throw new \InvalidArgumentException('Notification eventi noto‘g‘ri.');
        }

        $order->loadMissing('client');
        $message ??= $this->defaultMessage($order, $event);
        $targets = $this->targets($order, $channels);
        $notifications = [];

        foreach ($targets as $channel => $recipient) {
            $notification = $order->notifications()->create([
                'event' => $event,
                'channel' => $channel,
                'recipient' => $recipient,
                'message' => $message,
                'status' => $channel === 'internal' ? 'sent' : 'queued',
                'sent_at' => $channel === 'internal' ? now() : null,
                'metadata' => ['automation' => true],
            ]);
            $this->dispatchIfEnabled($notification);
            $notifications[] = $notification;
        }

        return $notifications;
    }

    public function queueMessage(Order $order, string $channel, string $message, ?string $recipient = null, string $event = 'customer_message'): OrderNotification
    {
        if (! in_array($channel, ['sms', 'telegram', 'email', 'whatsapp', 'internal'], true)) {
            throw new \InvalidArgumentException('Notification kanali noto‘g‘ri.');
        }

        $notification = $order->notifications()->create([
            'event' => $event,
            'channel' => $channel,
            'recipient' => $recipient ?: $this->defaultRecipient($order, $channel),
            'message' => $message,
            'status' => $channel === 'internal' ? 'sent' : 'queued',
            'sent_at' => $channel === 'internal' ? now() : null,
        ]);

        $this->dispatchIfEnabled($notification);

        return $notification;
    }

    private function targets(Order $order, ?array $channels): array
    {
        $client = $order->client;
        $available = [
            'sms' => $client?->phone_number,
            'telegram' => $client?->telegram_chat_id,
            'email' => $client?->email,
            'whatsapp' => $client?->whatsapp_phone ?: $client?->phone_number,
        ];

        if ($channels === null) {
            $enabled = (array) config('services.notifications.enabled_channels', ['internal']);
            $channels = array_values(array_filter($enabled, fn ($channel) => isset($available[$channel]) && $available[$channel]));
            if ($channels === []) {
                $channels = ['internal'];
            }
        }

        $targets = [];
        foreach ($channels as $channel) {
            if ($channel === 'internal') {
                $targets['internal'] = null;
            } elseif (isset($available[$channel]) && $available[$channel]) {
                $targets[$channel] = $available[$channel];
            }
        }

        return $targets ?: ['internal' => null];
    }

    private function defaultRecipient(Order $order, string $channel): ?string
    {
        $order->loadMissing('client');

        return match ($channel) {
            'email' => $order->client?->email,
            'telegram' => $order->client?->telegram_chat_id,
            'whatsapp' => $order->client?->whatsapp_phone ?: $order->client?->phone_number,
            default => $order->client?->phone_number,
        };
    }

    private function dispatchIfEnabled(OrderNotification $notification): void
    {
        if ($notification->status === 'queued' && config('services.notifications.dispatch', false)) {
            $notification->forceFill(['last_attempt_at' => now()])->save();
            DeliverOrderNotification::dispatch($notification->id);
        }
    }

    private function defaultMessage(Order $order, string $event): string
    {
        return match ($event) {
            'order_received', 'order_created' => "Buyurtma {$order->order_code} qabul qilindi.",
            'payment_received' => "Buyurtma {$order->order_code} uchun to‘lov qabul qilindi.",
            'missing_files' => "Buyurtma {$order->order_code} uchun yetishmayotgan fayllar bor.",
            'deadline_approaching' => "Buyurtma {$order->order_code} deadline'i yaqinlashmoqda.",
            'document_ready' => "Buyurtma {$order->order_code} hujjati tayyor.",
            'courier_sent' => "Buyurtma {$order->order_code} kuryerga yuborildi.",
            'order_delivered' => "Buyurtma {$order->order_code} topshirildi.",
            default => "Buyurtma {$order->order_code} holati yangilandi.",
        };
    }
}
