<?php

namespace App\Services;

use App\Models\ExpenseAdminModel;
use App\Models\CashReconciliation;
use App\Models\MarginLeak;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ServiceAddonModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MarginLeakDetectorService
{
    public function scan(Carbon $from, Carbon $to, ?int $filialId = null): array
    {
        $leaks = [];
        $orders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNot('status', 'cancelled')
            ->when($filialId, fn (Builder $query) => $query->where('filial_id', $filialId))
            ->with(['priceLines.document', 'costs'])
            ->get();

        foreach ($orders as $order) {
            $subtotal = (float) $order->subtotal_amount;
            $discount = (float) $order->discount_amount;
            if ($subtotal > 0 && ($discount / $subtotal) >= 0.2) {
                $leaks[] = $this->upsert([
                    'leak_type' => 'large_discount',
                    'severity' => ($discount / $subtotal) >= 0.4 ? 'high' : 'medium',
                    'order_id' => $order->id,
                    'filial_id' => $order->filial_id,
                    'amount' => $discount,
                    'message' => "{$order->order_code}: chegirma " . round(($discount / $subtotal) * 100, 1) . '% ga yetdi.',
                    'metadata' => ['subtotal' => $subtotal, 'discount' => $discount],
                ]);
            }

            $freeAddons = $order->priceLines
                ->where('line_type', 'addon')
                ->filter(fn ($line) => (float) $line->total_price <= 0)
                ->count();
            if ($freeAddons > 0) {
                $leaks[] = $this->upsert([
                    'leak_type' => 'free_addon',
                    'severity' => 'high',
                    'order_id' => $order->id,
                    'filial_id' => $order->filial_id,
                    'amount' => 0,
                    'message' => "{$order->order_code}: {$freeAddons} ta addon narxsiz tushgan.",
                    'metadata' => ['free_addons' => $freeAddons],
                ]);
            }

            foreach ($order->priceLines->where('line_type', 'addon') as $line) {
                $document = $line->document;
                $expected = $document?->service_id
                    ? ServiceAddonModel::query()
                        ->whereKey($line->source_id)
                        ->where('service_id', $document->service_id)
                        ->value('price')
                    : null;
                if ($expected !== null && abs((float) $line->unit_price - (float) $expected) > 0.01) {
                    $leaks[] = $this->upsert([
                        'leak_type' => 'addon_price_mismatch',
                        'severity' => 'high',
                        'order_id' => $order->id,
                        'filial_id' => $order->filial_id,
                        'amount' => abs(round((float) $expected - (float) $line->unit_price, 2)),
                        'message' => "{$order->order_code}: addon narxi katalogdan farq qiladi.",
                        'metadata' => [
                            'line_id' => $line->id,
                            'addon_id' => $line->source_id,
                            'expected' => (float) $expected,
                            'applied' => (float) $line->unit_price,
                        ],
                    ]);
                }
            }

            if (in_array($order->status, ['completed', 'delivered'], true) && $order->balance_amount > 0) {
                $leaks[] = $this->upsert([
                    'leak_type' => 'unpaid_completion',
                    'severity' => 'high',
                    'order_id' => $order->id,
                    'filial_id' => $order->filial_id,
                    'amount' => $order->balance_amount,
                    'message' => "{$order->order_code}: to‘lovsiz yakunlangan qoldiq mavjud.",
                    'metadata' => ['balance' => $order->balance_amount],
                ]);
            }

            if ((float) $order->profit_amount < 0) {
                $leaks[] = $this->upsert([
                    'leak_type' => 'negative_margin',
                    'severity' => 'high',
                    'order_id' => $order->id,
                    'filial_id' => $order->filial_id,
                    'amount' => abs((float) $order->profit_amount),
                    'message' => "{$order->order_code}: zarar bilan sotilgan order.",
                    'metadata' => ['profit' => (float) $order->profit_amount],
                ]);
            }
        }

        foreach ($this->branchExpenseSpikes($from, $to, $filialId) as $spike) {
            $leaks[] = $this->upsert($spike);
        }

        foreach ($this->workerRework($from, $to, $filialId) as $rework) {
            $leaks[] = $this->upsert($rework);
        }

        foreach ($this->cashVariances($from, $to, $filialId) as $variance) {
            $leaks[] = $this->upsert($variance);
        }

        return collect($leaks)->filter()->values()->all();
    }

    public function open(?int $filialId = null, int $limit = 100)
    {
        return MarginLeak::query()
            ->whereNull('resolved_at')
            ->when($filialId, fn (Builder $query) => $query->where('filial_id', $filialId))
            ->with(['order:id,order_code', 'filial:id,name'])
            ->latest('detected_at')
            ->limit($limit)
            ->get();
    }

    public function resolve(MarginLeak $leak): MarginLeak
    {
        $leak->forceFill(['resolved_at' => now()])->save();

        return $leak->fresh();
    }

    private function upsert(array $data): MarginLeak
    {
        $fingerprint = sha1(implode('|', [
            $data['leak_type'],
            $data['order_id'] ?? 0,
            $data['filial_id'] ?? 0,
            now()->format('Y-m-d'),
        ]));

        return MarginLeak::query()->updateOrCreate(
            ['fingerprint' => $fingerprint],
            [...$data, 'fingerprint' => $fingerprint, 'detected_at' => now()]
        );
    }

    private function branchExpenseSpikes(Carbon $from, Carbon $to, ?int $filialId): array
    {
        $days = max(1, $from->diffInDays($to) + 1);
        $previousFrom = $from->copy()->subDays($days);
        $previousTo = $from->copy()->subSecond();

        $current = $this->expensesByBranch($from, $to, $filialId);
        $previous = $this->expensesByBranch($previousFrom, $previousTo, $filialId);
        $rows = [];

        foreach ($current as $branchId => $amount) {
            $old = (float) ($previous[$branchId] ?? 0);
            if ($amount >= 100000 && ($old <= 0 || $amount > $old * 1.3)) {
                $rows[] = [
                    'leak_type' => 'branch_expense_spike',
                    'severity' => $old <= 0 || $amount > $old * 2 ? 'high' : 'medium',
                    'order_id' => null,
                    'filial_id' => $branchId,
                    'amount' => $amount,
                    'message' => 'Filial xarajati oldingi davrga nisbatan keskin oshgan.',
                    'metadata' => ['current' => $amount, 'previous' => $old],
                ];
            }
        }

        return $rows;
    }

    private function expensesByBranch(Carbon $from, Carbon $to, ?int $filialId): array
    {
        $expenses = ExpenseAdminModel::query()
            ->whereNot('approval_status', 'rejected')
            ->where(function (Builder $query) use ($from, $to): void {
                $query->whereDate('expense_date', '>=', $from->toDateString())
                    ->whereDate('expense_date', '<=', $to->toDateString())
                    ->orWhere(function (Builder $fallback) use ($from, $to): void {
                        $fallback->whereNull('expense_date')->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->when($filialId, function (Builder $query) use ($filialId): void {
                $query->where(function (Builder $branch) use ($filialId): void {
                    $branch->where('filial_id', $filialId)
                        ->orWhereHas('allocations', fn (Builder $allocation) => $allocation->where('filial_id', $filialId));
                });
            })
            ->with('allocations:expense_id,filial_id,amount')
            ->get();

        $totals = [];
        foreach ($expenses as $expense) {
            if ($expense->allocations->isEmpty()) {
                $totals[$expense->filial_id] = ($totals[$expense->filial_id] ?? 0) + (float) $expense->amount;
                continue;
            }
            foreach ($expense->allocations as $allocation) {
                $totals[$allocation->filial_id] = ($totals[$allocation->filial_id] ?? 0) + (float) $allocation->amount;
            }
        }

        return collect($totals)->map(fn ($amount) => (float) $amount)->all();
    }

    private function workerRework(Carbon $from, Carbon $to, ?int $filialId): array
    {
        $rows = [];
        $histories = OrderStatusHistory::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('changed_by_id')
            ->whereIn('to_status', ['in_processing', 'waiting_review'])
            ->with('order:id,filial_id,order_code')
            ->when($filialId, fn (Builder $query) => $query->whereHas('order', fn (Builder $orders) => $orders->where('filial_id', $filialId)))
            ->get();

        $histories->groupBy(fn (OrderStatusHistory $history) => $history->changed_by_id . '|' . $history->to_status)
            ->each(function ($items) use (&$rows): void {
                if ($items->count() < 3) {
                    return;
                }

                $first = $items->first();
                $rows[] = [
                    'leak_type' => 'worker_rework',
                    'severity' => $items->count() >= 6 ? 'high' : 'medium',
                    'order_id' => $first->order_id,
                    'filial_id' => $first->order?->filial_id,
                    'amount' => $items->count(),
                    'message' => ($first->order?->order_code ?: 'Order') . ': xodim tomonidan qayta ishlash signali aniqlandi.',
                    'metadata' => [
                        'changed_by_id' => $first->changed_by_id,
                        'transition' => $first->to_status,
                        'occurrences' => $items->count(),
                    ],
                ];
            });

        return $rows;
    }

    private function cashVariances(Carbon $from, Carbon $to, ?int $filialId): array
    {
        return CashReconciliation::query()
            ->whereDate('reconciliation_date', '>=', $from->toDateString())
            ->whereDate('reconciliation_date', '<=', $to->toDateString())
            ->where(function (Builder $query): void {
                $query->where('difference', '>', 0.01)->orWhere('difference', '<', -0.01);
            })
            ->when($filialId, fn (Builder $query) => $query->where('filial_id', $filialId))
            ->get()
            ->map(fn (CashReconciliation $row): array => [
                'leak_type' => 'cash_variance',
                'severity' => abs((float) $row->difference) >= 100000 ? 'high' : 'medium',
                'order_id' => null,
                'filial_id' => $row->filial_id,
                'amount' => abs((float) $row->difference),
                'message' => 'Naqd pul reconciliation farqi aniqlandi.',
                'metadata' => [
                    'reconciliation_id' => $row->id,
                    'expected' => (float) $row->expected_amount,
                    'actual' => (float) $row->actual_amount,
                    'difference' => (float) $row->difference,
                ],
            ])
            ->all();
    }
}
