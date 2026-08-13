<?php

namespace App\Jobs;

use App\Models\OrderNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $notificationId)
    {
        $this->afterCommit();
    }

    public function handle(NotificationDeliveryService $delivery): void
    {
        $notification = OrderNotification::query()->find($this->notificationId);
        if (! $notification || $notification->status !== 'queued') {
            return;
        }

        $notification->forceFill([
            'attempts' => (int) $notification->attempts + 1,
            'last_attempt_at' => now(),
        ])->save();

        try {
            $result = $delivery->deliver($notification->fresh());
            $notification->forceFill([
                'status' => $result['status'],
                'sent_at' => $result['status'] === 'sent' ? now() : null,
                'provider_message_id' => $result['provider_message_id'] ?? null,
                'error_message' => $result['error_message'] ?? null,
            ])->save();
        } catch (\Throwable $exception) {
            $attempts = (int) $notification->attempts;
            $notification->forceFill([
                'status' => $attempts >= $this->tries ? 'failed' : 'queued',
                'error_message' => $exception->getMessage(),
            ])->save();

            if ($attempts < $this->tries) {
                throw $exception;
            }
        }
    }
}
