<?php

namespace App\Services;

use App\Models\BusinessApproval;
use App\Models\MarginPolicy;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BusinessApprovalService
{
    public function projectedMargin(Order $order): float
    {
        $revenue = (float) $order->total_amount;

        return $revenue > 0 ? round(((float) $order->profit_amount / $revenue) * 100, 2) : 0;
    }

    public function requiresMarginApproval(Order $order): bool
    {
        $minimum = (float) (MarginPolicy::query()->where('filial_id', $order->filial_id)->where('is_active', true)->orderByDesc('service_id')->value('minimum_margin_percent') ?? 20);

        return $this->projectedMargin($order) < $minimum;
    }

    public function requestMarginException(Order $order, User $actor, string $reason): BusinessApproval
    {
        return BusinessApproval::query()->firstOrCreate([
            'subject_type' => Order::class, 'subject_id' => $order->id, 'type' => 'margin_exception', 'status' => 'pending',
        ], ['filial_id' => $order->filial_id, 'requested_by_id' => $actor->id, 'projected_margin_percent' => $this->projectedMargin($order), 'required_role' => 'admin_manager', 'reason' => $reason, 'payload' => ['order_code' => $order->order_code, 'revenue' => $order->total_amount, 'cost' => $order->cost_amount]]);
    }

    public function requestRefund(PaymentsModel $payment, User $actor, float $amount, string $reason): ?BusinessApproval
    {
        $limit = (float) (MarginPolicy::query()->where('filial_id', $payment->filial_id)->where('is_active', true)->value('refund_auto_approve_limit') ?? 0);
        if ($actor->hasAnyRole(['super_admin', 'admin_manager']) || ($limit > 0 && $amount <= $limit)) {
            return null;
        }

        return BusinessApproval::create(['filial_id' => $payment->filial_id, 'subject_type' => PaymentsModel::class, 'subject_id' => $payment->id, 'requested_by_id' => $actor->id, 'type' => 'refund', 'amount' => $amount, 'required_role' => 'admin_manager', 'reason' => $reason, 'payload' => ['payment_id' => $payment->id]]);
    }

    public function resolve(BusinessApproval $approval, User $actor, bool $approved, ?string $note = null): BusinessApproval
    {
        if (! $actor->hasAnyRole(['super_admin', 'admin_manager'])) {
            throw ValidationException::withMessages(['approval' => 'Bu tasdiq uchun manager huquqi kerak.']);
        }
        if ($approval->status !== 'pending') {
            throw ValidationException::withMessages(['approval' => 'So‘rov allaqachon ko‘rib chiqilgan.']);
        }
        $approval->forceFill(['status' => $approved ? 'approved' : 'rejected', 'resolved_by_id' => $actor->id, 'resolution_note' => $note, 'resolved_at' => now()])->save();
        if ($approved && $approval->type === 'refund' && $approval->subject instanceof PaymentsModel) {
            $payload = $approval->payload ?: [];
            app(PaymentLedgerService::class)->refund($approval->subject, (float) $approval->amount, $actor, $approval->reason);
        }

        return $approval->fresh();
    }
}
