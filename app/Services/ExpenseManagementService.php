<?php

namespace App\Services;

use App\Models\ExpenseAdminModel;
use App\Models\ExpenseAllocation;
use App\Models\ExpenseCategory;
use App\Models\FilialModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseManagementService
{
    public function save(
        array $data,
        ?ExpenseAdminModel $expense = null,
        ?UploadedFile $receipt = null,
        ?User $actor = null
    ): ExpenseAdminModel {
        $oldReceipt = $expense?->receipt_path;
        $newReceipt = null;

        try {
            $saved = DB::transaction(function () use ($data, $expense, $receipt, $actor, &$newReceipt): ExpenseAdminModel {
                $attributes = Arr::except($data, ['allocations', 'receipt']);
                $attributes['user_id'] ??= $actor?->id;
                $attributes['expense_date'] ??= today()->toDateString();
                $attributes['currency'] ??= 'UZS';
                $attributes['payment_method'] ??= 'cash';
                $attributes['expense_type'] ??= 'branch';
                $attributes['approval_status'] ??= 'pending';
                $attributes['is_recurring'] = filter_var(
                    $attributes['is_recurring'] ?? ! empty($attributes['recurrence_rule']),
                    FILTER_VALIDATE_BOOLEAN
                );

                if (($attributes['approval_status'] ?? null) === 'approved') {
                    $attributes['approver_id'] ??= $actor?->id;
                    $attributes['approved_at'] ??= now();
                }

                if (! ($attributes['is_recurring'] ?? false)) {
                    $attributes['recurrence_rule'] = null;
                    $attributes['recurrence_start'] = null;
                    $attributes['recurrence_end'] = null;
                    $attributes['next_occurrence'] = null;
                } elseif (empty($attributes['next_occurrence'])) {
                    $attributes['next_occurrence'] = $attributes['recurrence_start'] ?: today()->toDateString();
                }

                if (empty($attributes['category_id'])) {
                    $attributes['category_id'] = ExpenseCategory::query()
                        ->where('code', 'general')
                        ->value('id');
                }

                $record = $expense ?: new ExpenseAdminModel;
                $record->fill($attributes);
                $record->save();

                if ($receipt) {
                    $path = app(FileSecurityService::class)->store($receipt, 'expense-receipts');
                    $newReceipt = $path;
                    $record->forceFill([
                        'receipt_path' => $path,
                        'receipt_original_name' => $receipt->getClientOriginalName(),
                    ])->save();
                }

                if (array_key_exists('allocations', $data)) {
                    $this->replaceAllocations($record, $data['allocations'] ?: [], (int) $record->filial_id, (float) $record->amount);
                } elseif (! $record->allocations()->exists()) {
                    $this->replaceAllocations($record, [], (int) $record->filial_id, (float) $record->amount);
                }

                return $record->fresh(['allocations']);
            });
        } catch (\Throwable $exception) {
            if ($newReceipt) {
                Storage::disk('private')->delete($newReceipt);
            }

            throw $exception;
        }

        if ($receipt && $oldReceipt && $oldReceipt !== $saved->receipt_path) {
            Storage::disk('private')->delete($oldReceipt);
        }

        return $saved;
    }

    public function delete(ExpenseAdminModel $expense): void
    {
        $receipt = $expense->receipt_path;

        DB::transaction(fn () => $expense->delete());

        if ($receipt) {
            Storage::disk('private')->delete($receipt);
        }
    }

    public function generateRecurring(?Carbon $date = null): int
    {
        $date ??= today();
        $created = 0;

        ExpenseAdminModel::query()
            ->where('is_recurring', true)
            ->whereNotNull('next_occurrence')
            ->whereDate('next_occurrence', '<=', $date)
            ->where('approval_status', '<>', 'rejected')
            ->orderBy('id')
            ->get()
            ->each(function (ExpenseAdminModel $source) use ($date, &$created): void {
                $next = Carbon::parse($source->next_occurrence);
                $end = $source->recurrence_end ? Carbon::parse($source->recurrence_end) : null;

                while ($next->lte($date) && (! $end || $next->lte($end))) {
                    $generated = $source->replicate();
                    $generated->forceFill([
                        'expense_date' => $next->toDateString(),
                        'is_recurring' => false,
                        'next_occurrence' => null,
                        'receipt_path' => null,
                        'receipt_original_name' => null,
                        'metadata' => array_merge($source->metadata ?: [], [
                            'recurring_source_id' => $source->id,
                        ]),
                    ])->save();

                    $source->allocations->each(function (ExpenseAllocation $allocation) use ($generated): void {
                        $generated->allocations()->create([
                            'filial_id' => $allocation->filial_id,
                            'amount' => $allocation->amount,
                            'percentage' => $allocation->percentage,
                            'note' => $allocation->note,
                        ]);
                    });

                    $created++;
                    $next = $this->advance($next, $source->recurrence_rule);
                }

                $source->forceFill([
                    'next_occurrence' => $end && $next->gt($end) ? null : $next->toDateString(),
                ])->save();
            });

        return $created;
    }

    private function replaceAllocations(
        ExpenseAdminModel $expense,
        array $allocations,
        int $fallbackFilialId,
        float $total
    ): void {
        if ($allocations === []) {
            $allocations = [[
                'filial_id' => $fallbackFilialId,
                'amount' => $total,
            ]];
        }

        $normalized = collect($allocations)->map(function (array $allocation): array {
            return [
                'filial_id' => (int) ($allocation['filial_id'] ?? 0),
                'amount' => round((float) ($allocation['amount'] ?? 0), 2),
                'percentage' => isset($allocation['percentage']) ? round((float) $allocation['percentage'], 3) : null,
                'note' => $allocation['note'] ?? null,
            ];
        })->filter(fn (array $allocation): bool => $allocation['filial_id'] > 0 && $allocation['amount'] > 0)->values();

        $allocatedTotal = round((float) $normalized->sum('amount'), 2);
        if (abs($allocatedTotal - round($total, 2)) > 0.01) {
            throw new \InvalidArgumentException('Filial taqsimoti umumiy xarajat summasiga teng bo‘lishi kerak.');
        }

        $validFilials = FilialModel::query()->whereIn('id', $normalized->pluck('filial_id'))->pluck('id');
        if ($validFilials->count() !== $normalized->pluck('filial_id')->unique()->count()) {
            throw new \InvalidArgumentException('Filial taqsimotida mavjud bo‘lmagan filial bor.');
        }

        $expense->allocations()->delete();
        $normalized->each(function (array $allocation) use ($expense, $total): void {
            $expense->allocations()->create([
                ...$allocation,
                'percentage' => $allocation['percentage'] ?? ($total > 0 ? round(($allocation['amount'] / $total) * 100, 3) : 0),
            ]);
        });
    }

    private function advance(Carbon $date, ?string $rule): Carbon
    {
        return match ($rule) {
            'weekly' => $date->copy()->addWeek(),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }
}
