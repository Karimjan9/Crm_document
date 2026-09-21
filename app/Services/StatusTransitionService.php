<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StatusTransitionService
{
    public function transition(Order $order, string $status, ?User $actor = null, ?string $reason = null): Order
    {
        if (! in_array($status, Order::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Order statusi noto\'g\'ri.']);
        }
        if ($order->status === $status) {
            return $order->fresh();
        }

        return DB::transaction(function () use ($order, $status, $actor, $reason): Order {
            $from = $order->status;
            $order->status = $status;
            if ($status === 'completed') {
                $order->completed_at = now();
            }
            if ($status === 'cancelled') {
                $order->cancelled_at = now();
            }
            if ($status === 'delivered') {
                $order->delivered_at = now();
            }
            $order->save();
            $order->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $status,
                'changed_by_id' => $actor?->id,
                'reason' => $reason,
            ]);

            DB::afterCommit(fn () => app(TelegramBotIntegrationService::class)->queueOrderStatus($order->fresh()));

            return $order->fresh();
        });
    }
}
