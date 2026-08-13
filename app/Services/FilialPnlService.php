<?php

namespace App\Services;

use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\PriceTariff;
use App\Models\User;
use App\Support\WorkdayCalendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilialPnlService
{
    private const EXCLUDED_ORDER_STATUSES = ['cancelled', 'refunded'];

    private const EMPLOYEE_ROLES = ['employee', 'admin_filial', 'admin_manager'];

    private const WEEKDAY_LABELS = [
        1 => 'Du',
        2 => 'Se',
        3 => 'Cho',
        4 => 'Pa',
        5 => 'Ju',
        6 => 'Sha',
        7 => 'Ya',
    ];

    public function build(FilialModel $filial, Carbon|string $from, Carbon|string $to): array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        if ($from->greaterThan($to)) {
            throw new \InvalidArgumentException('P&L davri noto\'g\'ri.');
        }

        $orders = $this->periodOrders($filial, $from, $to);
        $allOrders = $this->ordersAsOf($filial, $to);
        $payments = $this->paymentsAsOf($filial, $to);
        $expenses = $this->expenses($filial, $from, $to);

        $periodOrderIds = $orders->modelKeys();
        $orderCosts = $orders->sum(fn (Order $order): float => (float) $order->cost_amount);
        $directExpense = $expenses
            ->where('expense_type', 'direct')
            ->sum(fn (ExpenseAdminModel $expense): float => $this->allocatedAmount($expense, $filial->id));
        $operatingExpense = $expenses
            ->reject(fn (ExpenseAdminModel $expense): bool => $expense->expense_type === 'direct')
            ->sum(fn (ExpenseAdminModel $expense): float => $this->allocatedAmount($expense, $filial->id));

        $revenue = round((float) $orders->sum('total_amount'), 2);
        $directCost = round((float) $orderCosts + (float) $directExpense, 2);
        $operatingExpense = round((float) $operatingExpense, 2);
        $expense = round($directCost + $operatingExpense, 2);
        $grossProfit = round($revenue - $expense, 2);

        $paidByOrder = $this->paidByOrder($payments);
        $periodPayments = $payments->filter(
            fn (PaymentsModel $payment): bool => $payment->created_at?->betweenIncluded($from, $to) ?? false
        );
        $paid = round((float) $periodPayments->sum(fn (PaymentsModel $payment): float => $payment->effective_amount), 2);
        $refunds = round((float) $periodPayments->sum('refund_amount'), 2);
        $debt = round((float) $allOrders->sum(function (Order $order) use ($paidByOrder): float {
            $paid = array_key_exists($order->id, $paidByOrder)
                ? $paidByOrder[$order->id]
                : (float) $order->paid_amount;

            return max((float) $order->total_amount - $paid, 0);
        }), 2);

        $deadline = $this->deadlineMetrics($orders, $to);
        $workingDays = $this->workingDaysBetween($filial, $from, $to);
        $dailyCapacity = (int) ($filial->daily_capacity ?? 0);
        $capacity = $dailyCapacity > 0 ? $dailyCapacity * $workingDays : 0;

        $specialPrices = $this->specialPrices($filial, $from, $to);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'profile' => $this->profile($filial, $specialPrices, $workingDays, $capacity),
            'kpis' => [
                'revenue' => $revenue,
                'paid' => $paid,
                'refunds' => $refunds,
                'debt' => $debt,
                'expense' => $expense,
                'direct_cost' => $directCost,
                'operating_expense' => $operatingExpense,
                'planned_monthly_expense' => round((float) $filial->monthly_expense, 2),
                'gross_profit' => $grossProfit,
                'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
                'order_count' => $orders->count(),
                'average_order_value' => $orders->count() > 0 ? round($revenue / $orders->count(), 2) : 0,
                'target_progress' => (float) $filial->target_amount > 0
                    ? round(($revenue / (float) $filial->target_amount) * 100, 2)
                    : 0,
                'capacity' => $capacity,
                'capacity_utilization' => $capacity > 0 ? round(($orders->count() / $capacity) * 100, 2) : 0,
                'deadline_breach_count' => $deadline['breach_count'],
                'deadline_breach_orders' => $deadline['breach_order_count'],
                'deadline_breach_documents' => $deadline['breach_document_count'],
                'deadline_breach_rate' => $deadline['breach_rate'],
            ],
            'employees' => $this->employeeProductivity($filial, $orders, $deadline['breached_order_ids']),
            'special_prices' => $specialPrices,
            'meta' => [
                'order_ids' => $periodOrderIds,
                'working_days' => $workingDays,
            ],
        ];
    }

    public function buildMany(iterable $filials, Carbon|string $from, Carbon|string $to): array
    {
        return collect($filials)
            ->map(fn (FilialModel $filial): array => $this->build($filial, $from, $to))
            ->values()
            ->all();
    }

    private function periodOrders(FilialModel $filial, Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->where('filial_id', $filial->id)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->with([
                'documents:id,order_id,user_id,assigned_to_id,qa_user_id,status_doc,deadline_time,rework_count,created_at,updated_at',
                'documents.statusHistories:id,document_id,to_status,created_at',
                'statusHistories:id,order_id,to_status,created_at',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    private function ordersAsOf(FilialModel $filial, Carbon $to): Collection
    {
        return Order::query()
            ->where('filial_id', $filial->id)
            ->where('created_at', '<=', $to)
            ->whereNotIn('status', self::EXCLUDED_ORDER_STATUSES)
            ->get([
                'id', 'total_amount', 'paid_amount', 'status', 'responsible_user_id',
                'created_by_id', 'promised_at', 'completed_at', 'delivered_at', 'created_at', 'updated_at',
            ]);
    }

    private function paymentsAsOf(FilialModel $filial, Carbon $to): Collection
    {
        return PaymentsModel::query()
            ->whereIn('status', ['confirmed', 'partially_refunded', 'refunded'])
            ->where('created_at', '<=', $to)
            ->where(function (Builder $query) use ($filial): void {
                $query->where('filial_id', $filial->id)
                    ->orWhereHas('order', fn (Builder $order) => $order->where('filial_id', $filial->id))
                    ->orWhereHas('document', fn (Builder $document) => $document->where('filial_id', $filial->id));
            })
            ->with([
                'order:id,filial_id',
                'document:id,order_id,filial_id',
            ])
            ->get(['id', 'order_id', 'document_id', 'filial_id', 'amount', 'refund_amount', 'created_at']);
    }

    private function expenses(FilialModel $filial, Carbon $from, Carbon $to): Collection
    {
        return ExpenseAdminModel::query()
            ->whereNot('approval_status', 'rejected')
            ->where(function (Builder $query) use ($from, $to): void {
                $query->whereDate('expense_date', '>=', $from->toDateString())
                    ->whereDate('expense_date', '<=', $to->toDateString())
                    ->orWhere(function (Builder $fallback) use ($from, $to): void {
                        $fallback->whereNull('expense_date')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->where(function (Builder $query) use ($filial): void {
                $query->where('filial_id', $filial->id)
                    ->orWhereHas('allocations', fn (Builder $allocation) => $allocation->where('filial_id', $filial->id));
            })
            ->with('allocations:expense_id,filial_id,amount')
            ->get();
    }

    private function paidByOrder(Collection $payments): array
    {
        $paid = [];

        foreach ($payments as $payment) {
            $orderId = $payment->order_id ?: $payment->document?->order_id;
            if (! $orderId) {
                continue;
            }

            $paid[$orderId] = round(($paid[$orderId] ?? 0) + $payment->effective_amount, 2);
        }

        return $paid;
    }

    private function allocatedAmount(ExpenseAdminModel $expense, int $filialId): float
    {
        if ($expense->allocations->isEmpty()) {
            return (int) $expense->filial_id === $filialId ? (float) $expense->amount : 0;
        }

        return (float) $expense->allocations
            ->where('filial_id', $filialId)
            ->sum('amount');
    }

    private function profile(FilialModel $filial, array $specialPrices, int $workingDays, int $capacity): array
    {
        $workingDayNumbers = collect($filial->working_days ?: [1, 2, 3, 4, 5, 6])
            ->map(fn ($day): int => (int) $day)
            ->filter(fn (int $day): bool => $day >= 1 && $day <= 7)
            ->unique()
            ->sort()
            ->values();

        return [
            'id' => (int) $filial->id,
            'name' => $filial->name,
            'code' => $filial->code,
            'phone' => $filial->phone,
            'address' => $filial->address,
            'manager_id' => $filial->manager_id,
            'manager_name' => $filial->manager?->name,
            'work_start_time' => $this->shortTime($filial->work_start_time),
            'work_end_time' => $this->shortTime($filial->work_end_time),
            'working_days' => $workingDayNumbers->all(),
            'working_days_label' => $workingDayNumbers
                ->map(fn (int $day): string => self::WEEKDAY_LABELS[$day] ?? (string) $day)
                ->implode(', '),
            'holiday_dates' => array_values($filial->holiday_dates ?: []),
            'monthly_expense' => round((float) $filial->monthly_expense, 2),
            'target_amount' => round((float) $filial->target_amount, 2),
            'commission_percent' => round((float) $filial->commission_percent, 2),
            'daily_capacity' => (int) ($filial->daily_capacity ?? 0),
            'period_capacity' => $capacity,
            'working_days_in_period' => $workingDays,
            'special_price_count' => count($specialPrices),
        ];
    }

    private function specialPrices(FilialModel $filial, Carbon $from, Carbon $to): array
    {
        return $filial->priceTariffs()
            ->active()
            ->where('effective_from', '<=', $to)
            ->where(function (Builder $query) use ($from): void {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $from);
            })
            ->with(['service:id,name', 'serviceAddon:id,name'])
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get([
                'id', 'filial_id', 'line_type', 'service_id', 'service_addon_id', 'variant',
                'name', 'price', 'cost_amount', 'deadline', 'currency', 'effective_from', 'effective_to',
            ])
            ->map(fn (PriceTariff $tariff): array => [
                'id' => (int) $tariff->id,
                'name' => $tariff->name,
                'line_type' => $tariff->line_type,
                'variant' => $tariff->variant,
                'service' => $tariff->service?->name,
                'addon' => $tariff->serviceAddon?->name,
                'price' => (float) $tariff->price,
                'cost_amount' => (float) $tariff->cost_amount,
                'deadline' => $tariff->deadline,
                'currency' => $tariff->currency,
                'effective_from' => $tariff->effective_from?->toDateString(),
                'effective_to' => $tariff->effective_to?->toDateString(),
            ])
            ->values()
            ->all();
    }

    private function employeeProductivity(FilialModel $filial, Collection $orders, array $breachedOrderIds): array
    {
        $workers = User::query()
            ->where('filial_id', $filial->id)
            ->whereHas('roles', fn (Builder $roles) => $roles
                ->whereIn('name', self::EMPLOYEE_ROLES)
                ->where('guard_name', 'web'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $orderGroups = $orders->groupBy(fn (Order $order): int => (int) ($order->responsible_user_id ?: $order->created_by_id ?: 0));
        $documents = $orders->flatMap(fn (Order $order): Collection => $order->documents);
        $workloadByUser = $documents
            ->filter(fn (DocumentsModel $document): bool => (int) ($document->assigned_to_id ?: $document->user_id) > 0)
            ->groupBy(fn (DocumentsModel $document): int => (int) ($document->assigned_to_id ?: $document->user_id))
            ->map(fn (Collection $items): int => (int) $items->sum('estimated_workload_minutes'));
        $breached = array_fill_keys(array_map('intval', $breachedOrderIds), true);

        $rows = $workers->map(function (User $worker) use ($orderGroups, $workloadByUser, $breached): array {
            return $this->employeeRow($worker->id, $worker->name, $orderGroups->get($worker->id, collect()), $workloadByUser->get($worker->id, 0), $breached);
        });

        $unassigned = $orderGroups->get(0, collect());
        if ($unassigned->isNotEmpty()) {
            $rows->push($this->employeeRow(null, 'Biriktirilmagan', $unassigned, 0, $breached));
        }

        return $rows->values()->all();
    }

    private function employeeRow(?int $id, string $name, Collection $orders, int $workload, array $breached): array
    {
        $revenue = (float) $orders->sum('total_amount');
        $completed = $orders->whereIn('status', ['completed', 'delivered'])->count();
        $breachCount = $orders->filter(fn (Order $order): bool => isset($breached[$order->id]))->count();

        return [
            'id' => $id,
            'name' => $name,
            'order_count' => $orders->count(),
            'completed_orders' => $completed,
            'revenue' => round($revenue, 2),
            'average_order_value' => $orders->count() > 0 ? round($revenue / $orders->count(), 2) : 0,
            'completion_rate' => $orders->count() > 0 ? round(($completed / $orders->count()) * 100, 2) : 0,
            'deadline_breaches' => $breachCount,
            'workload_minutes' => $workload,
        ];
    }

    private function deadlineMetrics(Collection $orders, Carbon $to): array
    {
        $asOf = now()->lessThan($to) ? now() : $to->copy();
        $breachedOrderIds = [];
        $breachDocumentCount = 0;

        foreach ($orders as $order) {
            if ($this->orderDeadlineBreached($order, $asOf)) {
                $breachedOrderIds[] = (int) $order->id;
            }

            foreach ($order->documents as $document) {
                if ($this->documentDeadlineBreached($document, $asOf)) {
                    $breachDocumentCount++;
                    $breachedOrderIds[] = (int) $order->id;
                }
            }
        }

        $breachedOrderIds = array_values(array_unique($breachedOrderIds));
        $orderCount = $orders->count();

        return [
            'breach_count' => count($breachedOrderIds),
            'breach_order_count' => count($breachedOrderIds),
            'breach_document_count' => $breachDocumentCount,
            'breach_rate' => $orderCount > 0 ? round((count($breachedOrderIds) / $orderCount) * 100, 2) : 0,
            'breached_order_ids' => $breachedOrderIds,
        ];
    }

    private function orderDeadlineBreached(Order $order, Carbon $asOf): bool
    {
        if (! $order->promised_at || in_array($order->status, self::EXCLUDED_ORDER_STATUSES, true)) {
            return false;
        }

        $completionAt = $this->orderCompletionAt($order);
        return ($completionAt ?: $asOf)->greaterThan($order->promised_at) && ($completionAt || $asOf->greaterThan($order->promised_at));
    }

    private function documentDeadlineBreached(DocumentsModel $document, Carbon $asOf): bool
    {
        $status = match ((string) $document->status_doc) {
            'process' => 'in_processing',
            'finish' => 'completed',
            default => (string) $document->status_doc,
        };

        if (in_array($status, ['cancelled', 'refunded'], true)) {
            return false;
        }

        $dueAt = WorkdayCalendar::resolveDueAt($document->created_at, $document->deadline_time);
        $completionAt = $this->documentCompletionAt($document);

        return ($completionAt ?: $asOf)->greaterThan($dueAt) && ($completionAt || $asOf->greaterThan($dueAt));
    }

    private function orderCompletionAt(Order $order): ?Carbon
    {
        $history = $order->statusHistories
            ->filter(fn ($event): bool => in_array($event->to_status, ['ready_for_delivery', 'courier_sent', 'delivered', 'completed'], true))
            ->sortBy('created_at')
            ->first();

        return $history?->created_at ?: ($order->completed_at ?: ($order->delivered_at ?: (in_array($order->status, ['completed', 'delivered'], true) ? $order->updated_at : null)));
    }

    private function documentCompletionAt(DocumentsModel $document): ?Carbon
    {
        $history = $document->statusHistories
            ->filter(fn ($event): bool => in_array($event->to_status, ['ready_for_delivery', 'courier_sent', 'delivered', 'completed'], true))
            ->sortBy('created_at')
            ->first();

        return $history?->created_at ?: (in_array((string) $document->status_doc, ['ready_for_delivery', 'courier_sent', 'delivered', 'completed', 'finish'], true) ? $document->updated_at : null);
    }

    private function workingDaysBetween(FilialModel $filial, Carbon $from, Carbon $to): int
    {
        $workingDays = collect($filial->working_days ?: [1, 2, 3, 4, 5, 6])
            ->map(fn ($day): int => (int) $day)
            ->unique();
        $holidays = collect($filial->holiday_dates ?: [])->map(fn ($date): string => (string) $date)->flip();
        $count = 0;
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($to)) {
            if ($workingDays->contains($cursor->dayOfWeekIso)
                && ! $holidays->has($cursor->toDateString())
                && ! WorkdayCalendar::isNonWorkingDay($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    private function shortTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
