<?php

namespace App\Services;

use App\Models\ExpenseAdminModel;
use App\Models\Order;
use App\Models\OrderCost;
use App\Models\PaymentsModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FinanceDashboardService
{
    public function __construct(private readonly PaymentLedgerService $ledger)
    {
    }

    public function build(?User $user, array $filters = []): array
    {
        $from = Carbon::parse($filters['date_from'] ?? today()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($filters['date_to'] ?? today()->toDateString())->endOfDay();

        $ordersQuery = Order::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->whereNot('status', 'cancelled')
            ->when($filters['filial_id'] ?? null, fn (Builder $query, $filialId) => $query->where('filial_id', $filialId));

        $orders = $ordersQuery
            ->with([
                'filial:id,name',
                'documents:id,order_id,service_id,service_price,addons_total_price,final_price',
                'documents.service:id,name',
                'priceLines:id,order_id,document_id,line_type,cost_amount',
            ])
            ->get();

        $orderIds = $orders->modelKeys();
        $directCosts = OrderCost::query()
            ->whereIn('order_id', $orderIds)
            ->with('order:id,filial_id')
            ->get();
        $directCostByOrder = $directCosts->groupBy('order_id')->map(fn ($costs) => (float) $costs->sum('amount'));

        $expenses = ExpenseAdminModel::query()
            ->whereNot('approval_status', 'rejected')
            ->where(function (Builder $query) use ($from, $to): void {
                $query->whereDate('expense_date', '>=', $from->toDateString())
                    ->whereDate('expense_date', '<=', $to->toDateString())
                    ->orWhere(function (Builder $fallback) use ($from, $to): void {
                        $fallback->whereNull('expense_date')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->with('allocations:expense_id,filial_id,amount')
            ->when($filters['filial_id'] ?? null, function (Builder $query, $filialId): void {
                $query->where(function (Builder $branch) use ($filialId): void {
                    $branch->where('filial_id', $filialId)
                        ->orWhereHas('allocations', fn (Builder $allocation) => $allocation->where('filial_id', $filialId));
                });
            })
            ->get();

        $revenue = round((float) $orders->sum('total_amount'), 2);
        $paymentQuery = PaymentsModel::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($filters['filial_id'] ?? null, function (Builder $query, $filialId): void {
                $query->where(function (Builder $scope) use ($filialId): void {
                    $scope->where('filial_id', $filialId)
                        ->orWhereHas('order', fn (Builder $order) => $order->where('filial_id', $filialId))
                        ->orWhereHas('document', fn (Builder $document) => $document->where('filial_id', $filialId));
                });
            });
        $ledgerPaid = $this->ledger->effectiveSum($paymentQuery);
        $ledgerRefunds = round((float) (clone $paymentQuery)->sum('refund_amount'), 2);
        $outstanding = round((float) $orders->sum(fn (Order $order): float => $order->balance_amount), 2);
        $directExpense = (float) $expenses
            ->where('expense_type', 'direct')
            ->sum(fn (ExpenseAdminModel $expense) => $this->allocatedAmount($expense, $filters['filial_id'] ?? null));
        $directCost = round((float) $directCosts->sum('amount') + $directExpense, 2);
        $branchExpense = round((float) $expenses
            ->reject(fn (ExpenseAdminModel $expense) => $expense->expense_type === 'direct')
            ->sum(fn (ExpenseAdminModel $expense) => $this->allocatedAmount($expense, $filters['filial_id'] ?? null)), 2);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'kpis' => [
                'revenue' => $revenue,
                'direct_cost' => $directCost,
                'branch_expense' => $branchExpense,
                'gross_profit' => round($revenue - $directCost - $branchExpense, 2),
                'gross_margin' => $revenue > 0 ? round((($revenue - $directCost - $branchExpense) / $revenue) * 100, 2) : 0,
                'order_count' => $orders->count(),
                'paid' => $ledgerPaid,
                'refunds' => $ledgerRefunds,
                'outstanding' => $outstanding,
            ],
            'branches' => $this->branchStats($orders, $directCostByOrder, $expenses),
            'services' => $this->serviceStats($orders, $directCostByOrder),
        ];
    }

    private function branchStats($orders, $directCostByOrder, $expenses): array
    {
        $rows = $orders->groupBy('filial_id')->map(function ($branchOrders, $filialId) use ($directCostByOrder, $expenses): array {
            $revenue = (float) $branchOrders->sum('total_amount');
            $directCost = (float) $branchOrders->sum(fn (Order $order) => $directCostByOrder->get($order->id, 0));
            $branchExpense = (float) $expenses
                ->reject(fn (ExpenseAdminModel $expense) => $expense->expense_type === 'direct')
                ->sum(fn (ExpenseAdminModel $expense) => $this->allocatedAmount($expense, (int) $filialId));

            return [
                'id' => (int) $filialId,
                'name' => $branchOrders->first()?->filial?->name ?: 'Noma’lum filial',
                'orders' => $branchOrders->count(),
                'revenue' => round($revenue, 2),
                'direct_cost' => round($directCost, 2),
                'branch_expense' => round($branchExpense, 2),
                'gross_profit' => round($revenue - $directCost - $branchExpense, 2),
            ];
        });

        return $rows->sortByDesc('gross_profit')->values()->all();
    }

    private function serviceStats($orders, $directCostByOrder): array
    {
        $rows = collect();

        foreach ($orders as $order) {
            $documents = $order->documents;
            $documentRevenue = (float) $documents->sum(fn ($document) => (float) ($document->final_price ?: ((float) $document->service_price + (float) $document->addons_total_price)));
            $orderDirectCost = (float) $directCostByOrder->get($order->id, 0);

            foreach ($documents as $document) {
                $serviceId = (int) ($document->service_id ?: 0);
                $revenue = (float) ($document->final_price ?: ((float) $document->service_price + (float) $document->addons_total_price));
                $lineCost = (float) $order->priceLines
                    ->where('document_id', $document->id)
                    ->sum('cost_amount');
                $allocatedOrderCost = $documentRevenue > 0 ? $orderDirectCost * ($revenue / $documentRevenue) : 0;
                $key = $serviceId . ':' . ($document->service?->name ?: 'Noma’lum xizmat');
                $row = $rows->get($key, [
                    'id' => $serviceId ?: null,
                    'name' => $document->service?->name ?: 'Noma’lum xizmat',
                    'orders' => 0,
                    'revenue' => 0,
                    'direct_cost' => 0,
                    'gross_profit_before_branch_expense' => 0,
                ]);
                $row['orders']++;
                $row['revenue'] += $revenue;
                $row['direct_cost'] += $lineCost + $allocatedOrderCost;
                $row['gross_profit_before_branch_expense'] = $row['revenue'] - $row['direct_cost'];
                $rows->put($key, $row);
            }
        }

        return $rows->sortByDesc('gross_profit_before_branch_expense')->values()->map(function (array $row): array {
            foreach (['revenue', 'direct_cost', 'gross_profit_before_branch_expense'] as $money) {
                $row[$money] = round((float) $row[$money], 2);
            }
            $row['margin'] = $row['revenue'] > 0
                ? round(($row['gross_profit_before_branch_expense'] / $row['revenue']) * 100, 2)
                : 0;

            return $row;
        })->all();
    }

    private function allocatedAmount(ExpenseAdminModel $expense, ?int $filialId): float
    {
        if ($filialId === null) {
            return (float) $expense->amount;
        }

        $allocations = $expense->allocations;
        if ($allocations->isEmpty()) {
            return (int) $expense->filial_id === $filialId ? (float) $expense->amount : 0;
        }

        return (float) $allocations
            ->where('filial_id', $filialId)
            ->sum('amount');
    }
}
