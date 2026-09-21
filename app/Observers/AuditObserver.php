<?php

namespace App\Observers;

use App\Models\DocumentsModel;
use App\Models\PaymentRefund;
use App\Services\AuditLogger;
use App\Services\SecurityAlertService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /** @var array<int, array{old: array, new: array}> */
    private static array $pendingChanges = [];

    public function creating(Model $model): void
    {
        // Creation is recorded from attributes in created(); no temporary model relation is needed.
    }

    public function created(Model $model): void
    {
        app(AuditLogger::class)->model('created', $model, [], $model->getAttributes());
        $this->detectAnomaly($model);
    }

    public function updating(Model $model): void
    {
        $dirty = $model->getDirty();
        unset($dirty['updated_at']);
        $old = [];
        foreach (array_keys($dirty) as $key) {
            $old[$key] = $model->getRawOriginal($key);
        }
        self::$pendingChanges[spl_object_id($model)] = ['old' => $old, 'new' => $dirty];
    }

    public function updated(Model $model): void
    {
        $change = self::$pendingChanges[spl_object_id($model)] ?? null;
        unset(self::$pendingChanges[spl_object_id($model)]);
        if ($change && $change['old'] !== []) {
            app(AuditLogger::class)->model('updated', $model, $change['old'], $change['new']);
            $this->detectAnomaly($model);
        }
    }

    public function deleted(Model $model): void
    {
        app(AuditLogger::class)->model('deleted', $model, $model->getOriginal(), []);
    }

    private function detectAnomaly(Model $model): void
    {
        if ($model instanceof DocumentsModel && (float) $model->discount_percent >= (float) config('security.alerts.large_discount_percent', 30)) {
            app(SecurityAlertService::class)->raise('large_discount', 'warning', [
                'document_id' => $model->id,
                'filial_id' => $model->filial_id,
                'discount_percent' => (float) $model->discount_percent,
                'discount_amount' => (float) $model->discount_amount,
            ]);
        }

        if ($model instanceof PaymentRefund && (float) $model->amount >= (float) config('security.alerts.large_refund_amount', 1000000)) {
            app(SecurityAlertService::class)->raise('large_refund', 'warning', [
                'refund_id' => $model->id,
                'payment_id' => $model->payment_id,
                'amount' => (float) $model->amount,
            ]);
        }
    }
}
