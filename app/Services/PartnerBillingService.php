<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerInvoice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PartnerBillingService
{
    public function generateForPeriod(Partner $partner, CarbonImmutable $start, CarbonImmutable $end): ?PartnerInvoice
    {
        return DB::transaction(function () use ($partner, $start, $end): ?PartnerInvoice {
            Partner::query()->whereKey($partner->id)->lockForUpdate()->firstOrFail();

            $orders = Order::query()
                ->where('partner_id', $partner->id)
                ->where('billing_status', 'unbilled')
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return null;
            }

            $sequence = PartnerInvoice::query()
                ->where('partner_id', $partner->id)
                ->whereDate('period_start', $start->toDateString())
                ->count() + 1;
            $invoiceNumber = sprintf(
                'B2B-%s-%s-%02d',
                strtoupper($partner->code),
                $start->format('Ym'),
                $sequence
            );
            $terms = max(0, (int) $partner->payment_terms_days);

            $invoice = $partner->invoices()->create([
                'invoice_number' => $invoiceNumber,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'issued_at' => now(),
                'due_at' => $end->addDays($terms)->toDateString(),
                'status' => 'issued',
                'currency' => $partner->currency ?: 'UZS',
                'subtotal_amount' => round((float) $orders->sum('subtotal_amount'), 2),
                'discount_amount' => round((float) $orders->sum('discount_amount'), 2),
                'total_amount' => round((float) $orders->sum('total_amount'), 2),
                'paid_amount' => 0,
                'payment_terms_days' => $terms,
                'metadata' => ['generated_by' => 'b2b:generate-invoices'],
            ]);

            foreach ($orders as $order) {
                $invoice->lines()->create([
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'description' => $order->title ?: 'B2B order ' . $order->order_code,
                    'subtotal_amount' => $order->subtotal_amount,
                    'discount_amount' => $order->discount_amount,
                    'total_amount' => $order->total_amount,
                    'metadata' => [
                        'partner_reference' => $order->partner_reference,
                        'tracking_token' => $order->tracking_token,
                    ],
                ]);

                $order->forceFill([
                    'billing_status' => 'invoiced',
                    'partner_invoice_id' => $invoice->id,
                ])->save();
            }

            return $invoice->fresh(['partner', 'lines.order']);
        });
    }
}
