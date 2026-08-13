<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPaymentLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderPaymentLinkService
{
    public function activeFor(Order $order): ?OrderPaymentLink
    {
        return $order->paymentLinks()
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();
    }

    public function ensure(Order $order, ?float $amount = null): ?OrderPaymentLink
    {
        return DB::transaction(function () use ($order, $amount): ?OrderPaymentLink {
            // Lock the parent order so two operators cannot issue multiple
            // active links for the same balance at the same time.
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $amount = round($amount ?? $lockedOrder->balance_amount, 2);
            if ($amount <= 0) {
                return null;
            }

            $existing = $lockedOrder->paymentLinks()
                ->where('status', 'pending')
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->first();

            if ($existing && abs((float) $existing->amount - $amount) < 0.01) {
                return $existing;
            }

            $lockedOrder->paymentLinks()->where('status', 'pending')->update(['status' => 'superseded']);

            return $lockedOrder->paymentLinks()->create([
                'token' => Str::random(80),
                'amount' => $amount,
                'currency' => $lockedOrder->currency ?: 'UZS',
                'provider' => config('services.payment.provider', 'manual'),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
                'metadata' => ['source' => 'customer_portal'],
            ]);
        });
    }
}
