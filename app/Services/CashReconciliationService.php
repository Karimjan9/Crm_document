<?php

namespace App\Services;

use App\Models\CashReconciliation;
use App\Models\PaymentsModel;
use App\Models\User;
use Carbon\Carbon;

class CashReconciliationService
{
    public function expectedFor(int $filialId, Carbon $date): float
    {
        return round((float) PaymentsModel::query()
            ->where('payment_type', 'cash')
            ->effective()
            ->whereDate('created_at', $date->toDateString())
            ->where(function ($query) use ($filialId): void {
                $query->where('filial_id', $filialId)
                    ->orWhereHas('order', fn ($order) => $order->where('filial_id', $filialId))
                    ->orWhere(function ($legacy) use ($filialId): void {
                        $legacy->whereNull('order_id')
                            ->whereHas('document', fn ($document) => $document->where('filial_id', $filialId));
                    });
            })
            ->selectRaw('COALESCE(SUM(amount - COALESCE(refund_amount, 0)), 0) AS effective_total')
            ->value('effective_total'), 2);
    }

    public function record(
        int $filialId,
        Carbon $date,
        float $actualAmount,
        ?float $expectedAmount = null,
        ?string $notes = null,
        ?User $actor = null
    ): CashReconciliation {
        $expectedAmount ??= $this->expectedFor($filialId, $date);
        $difference = round($actualAmount - $expectedAmount, 2);

        return CashReconciliation::query()->updateOrCreate(
            ['filial_id' => $filialId, 'reconciliation_date' => $date->toDateString()],
            [
                'expected_amount' => round($expectedAmount, 2),
                'actual_amount' => round($actualAmount, 2),
                'difference' => $difference,
                'status' => abs($difference) <= 0.01 ? 'balanced' : 'variance',
                'recorded_by_id' => $actor?->id,
                'notes' => $notes,
            ]
        );
    }
}
