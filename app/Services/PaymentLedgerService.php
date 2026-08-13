<?php

namespace App\Services;

use App\Models\CashReconciliation;
use App\Models\CashSession;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentRefund;
use App\Models\PaymentStatusHistory;
use App\Models\PaymentsModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentLedgerService
{
    public function effectiveSum(Builder|Relation $query): float
    {
        $total = (clone $query)
            ->effective()
            ->selectRaw('COALESCE(SUM(amount - COALESCE(refund_amount, 0)), 0) AS effective_total')
            ->value('effective_total');

        return round((float) $total, 2);
    }

    public function create(
        ?Order $order,
        ?DocumentsModel $document,
        float $amount,
        string $paymentType,
        ?User $actor = null,
        array $options = []
    ): PaymentsModel {
        $amount = round($amount, 2);
        $status = (string) ($options['status'] ?? 'confirmed');
        $confirmationStatus = (string) ($options['confirmation_status'] ?? ($status === 'confirmed' ? 'confirmed' : 'pending'));

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'To‘lov summasi 0 dan katta bo‘lishi kerak.']);
        }

        if (! in_array($paymentType, PaymentsModel::TYPES, true)) {
            throw ValidationException::withMessages(['payment_type' => 'To‘lov turi noto‘g‘ri.']);
        }

        if (! in_array($status, PaymentsModel::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'To‘lov statusi noto‘g‘ri.']);
        }

        $filialId = $order?->filial_id ?: $document?->filial_id;
        $invoiceId = $options['invoice_id'] ?? ($order
            ? Invoice::query()->where('order_id', $order->id)->value('id')
            : null);
        $cashSession = $this->resolveCashSession(
            $filialId ? (int) $filialId : null,
            $paymentType,
            $options['cash_session_id'] ?? null,
            $options['cashier_id'] ?? $actor?->id,
        );
        $proof = $options['payment_proof'] ?? null;

        if ($proof !== null && ! $proof instanceof UploadedFile) {
            throw ValidationException::withMessages(['payment_proof' => 'To‘lov isboti fayl bo‘lishi kerak.']);
        }

        $attributes = [
            'document_id' => $document?->id,
            'order_id' => $order?->id,
            'filial_id' => $filialId,
            'amount' => $amount,
            'payment_type' => $paymentType,
            'status' => $status,
            'confirmation_status' => $confirmationStatus,
            'paid_by_admin_id' => $actor?->id,
            'confirmed_by_id' => $status === 'confirmed' ? ($actor?->id) : null,
            'confirmed_at' => $status === 'confirmed' ? now() : null,
            'cashier_id' => $options['cashier_id'] ?? $actor?->id,
            'cash_session_id' => $cashSession?->id,
            'online_transaction_id' => $options['online_transaction_id'] ?? null,
            'refund_amount' => 0,
            'invoice_id' => $invoiceId,
            'metadata' => $options['metadata'] ?? null,
        ];

        if ($proof instanceof UploadedFile) {
            $path = $proof->store('payment-proofs', 'private');
            $attributes += [
                'payment_proof_path' => $path,
                'payment_proof_original_name' => $proof->getClientOriginalName(),
                'payment_proof_mime' => $proof->getMimeType(),
                'payment_proof_size' => $proof->getSize(),
            ];
        }

        $payment = PaymentsModel::create($attributes);
        $payment->forceFill([
            'receipt_number' => $this->receiptNumber($payment, $filialId ? (int) $filialId : null),
        ])->save();

        $this->recordHistory($payment, null, $status, $actor, 'To‘lov ledgerga kiritildi');

        return $payment->fresh();
    }

    public function confirm(PaymentsModel $payment, ?User $actor = null, ?string $reason = null): PaymentsModel
    {
        return DB::transaction(function () use ($payment, $actor, $reason): PaymentsModel {
            $payment = PaymentsModel::query()->lockForUpdate()->findOrFail($payment->id);

            if (in_array($payment->status, ['cancelled', 'refunded'], true)) {
                throw ValidationException::withMessages(['payment' => 'Bekor qilingan yoki qaytarilgan to‘lovni tasdiqlab bo‘lmaydi.']);
            }

            $from = $payment->status;
            $payment->forceFill([
                'status' => $payment->refund_amount > 0 ? 'partially_refunded' : 'confirmed',
                'confirmation_status' => 'confirmed',
                'confirmed_by_id' => $actor?->id,
                'confirmed_at' => now(),
            ])->save();

            $this->recordHistory($payment, $from, $payment->status, $actor, $reason ?: 'To‘lov tasdiqlandi');
            $this->syncAggregates($payment);

            return $payment->fresh();
        });
    }

    public function cancel(PaymentsModel $payment, ?User $actor = null, string $reason = ''): PaymentsModel
    {
        return DB::transaction(function () use ($payment, $actor, $reason): PaymentsModel {
            $payment = PaymentsModel::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'cancelled') {
                return $payment->fresh();
            }

            if ($payment->status === 'refunded') {
                throw ValidationException::withMessages(['payment' => 'To‘liq qaytarilgan to‘lovni bekor qilib bo‘lmaydi.']);
            }

            $from = $payment->status;
            $payment->forceFill([
                'status' => 'cancelled',
                'confirmation_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by_id' => $actor?->id,
                'cancellation_reason' => trim($reason) ?: 'Sabab ko‘rsatilmagan',
            ])->save();

            $this->recordHistory($payment, $from, 'cancelled', $actor, $payment->cancellation_reason);
            $this->syncAggregates($payment);

            return $payment->fresh();
        });
    }

    public function refund(PaymentsModel $payment, float $amount, ?User $actor = null, string $reason = ''): PaymentRefund
    {
        return DB::transaction(function () use ($payment, $amount, $actor, $reason): PaymentRefund {
            $payment = PaymentsModel::query()->lockForUpdate()->findOrFail($payment->id);
            $amount = round($amount, 2);
            $available = round((float) $payment->amount - (float) $payment->refund_amount, 2);

            if (! in_array($payment->status, ['confirmed', 'partially_refunded'], true)) {
                throw ValidationException::withMessages(['payment' => 'Faqat tasdiqlangan to‘lovdan refund qilish mumkin.']);
            }

            if ($amount <= 0 || $amount > $available) {
                throw ValidationException::withMessages(['amount' => 'Refund summasi to‘lovning qolgan summasidan oshmasligi kerak.']);
            }

            $refund = PaymentRefund::create([
                'payment_id' => $payment->id,
                'refunded_by_id' => $actor?->id,
                'confirmed_by_id' => $actor?->id,
                'amount' => $amount,
                'status' => 'confirmed',
                'reason' => trim($reason) ?: 'Sabab ko‘rsatilmagan',
                'confirmed_at' => now(),
            ]);
            $refund->forceFill([
                'refund_number' => 'RFND-' . ($payment->receipt_number ?: $payment->id) . '-' . $refund->id,
            ])->save();

            $newRefundAmount = round((float) $payment->refund_amount + $amount, 2);
            $newStatus = $newRefundAmount >= (float) $payment->amount ? 'refunded' : 'partially_refunded';
            $from = $payment->status;
            $payment->forceFill([
                'refund_amount' => $newRefundAmount,
                'status' => $newStatus,
                'refunded_at' => now(),
                'refunded_by_id' => $actor?->id,
            ])->save();

            $this->recordHistory($payment, $from, $newStatus, $actor, 'Refund: ' . $refund->refund_number);
            $this->syncAggregates($payment);

            return $refund->fresh(['payment']);
        });
    }

    public function syncAggregates(PaymentsModel $payment): void
    {
        $document = $payment->document_id
            ? DocumentsModel::query()->find($payment->document_id)
            : null;
        $order = $payment->order_id
            ? Order::query()->find($payment->order_id)
            : $document?->order;

        if ($document) {
            $paid = $this->effectiveSum(PaymentsModel::query()->where('document_id', $document->id));
            $document->forceFill(['paid_amount' => $paid])->save();
        }

        if ($order) {
            $paid = $this->effectiveSum(PaymentsModel::query()->where('order_id', $order->id));
            $order->forceFill(['paid_amount' => $paid])->save();
        }

        if ($payment->invoice_id) {
            $invoice = Invoice::query()->find($payment->invoice_id);
            if ($invoice) {
                $paid = $this->effectiveSum(PaymentsModel::query()->where('invoice_id', $invoice->id));
                $invoice->forceFill([
                    'paid_amount' => $paid,
                    'balance_amount' => round(max((float) $invoice->total_amount - $paid, 0), 2),
                    'status' => $paid >= (float) $invoice->total_amount
                        ? 'paid'
                        : ($paid > 0 ? 'partially_paid' : 'issued'),
                ])->save();
            }
        }
    }

    public function issueInvoice(Order $order, ?User $actor = null, ?Carbon $dueAt = null): Invoice
    {
        return DB::transaction(function () use ($order, $actor, $dueAt): Invoice {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $order->loadMissing(['priceLines.document']);
            $paid = $this->effectiveSum(PaymentsModel::query()->where('order_id', $order->id));
            $total = round((float) $order->total_amount, 2);

            $invoice = Invoice::query()->where('order_id', $order->id)->lockForUpdate()->first();
            if (! $invoice) {
                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'client_id' => $order->client_id,
                    'filial_id' => $order->filial_id,
                    'issued_by_id' => $actor?->id,
                    'invoice_number' => 'INV-' . $order->order_code,
                    'status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partially_paid' : 'issued'),
                    'currency' => $order->currency ?: 'UZS',
                    'subtotal_amount' => $order->subtotal_amount,
                    'discount_amount' => $order->discount_amount,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'balance_amount' => round(max($total - $paid, 0), 2),
                    'issued_at' => now(),
                    'due_at' => $dueAt?->toDateString(),
                ]);

                foreach ($order->priceLines as $line) {
                    $invoice->lines()->create([
                        'order_price_line_id' => $line->id,
                        'document_id' => $line->document_id,
                        'line_type' => $line->pricing_line_type ?: $line->line_type,
                        'description' => $line->name,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'total_amount' => $line->total_price,
                        'metadata' => $line->metadata,
                    ]);
                }
            } else {
                $invoice->forceFill([
                    'paid_amount' => $paid,
                    'balance_amount' => round(max((float) $invoice->total_amount - $paid, 0), 2),
                    'status' => $paid >= (float) $invoice->total_amount
                        ? 'paid'
                        : ($paid > 0 ? 'partially_paid' : 'issued'),
                ])->save();
            }

            PaymentsModel::query()
                ->where('order_id', $order->id)
                ->whereNull('invoice_id')
                ->update(['invoice_id' => $invoice->id]);

            return $invoice->fresh(['lines', 'order', 'payments']);
        });
    }

    public function openCashSession(
        int $filialId,
        User $actor,
        float $openingBalance = 0,
        ?Carbon $date = null,
        ?int $cashierId = null
    ): CashSession {
        $this->assertBranchAccess($filialId, $actor);
        $date ??= now();
        $cashierId ??= $actor->id;
        $openingBalance = round($openingBalance, 2);

        if ($openingBalance < 0) {
            throw ValidationException::withMessages(['opening_balance' => 'Boshlang‘ich qoldiq manfiy bo‘lishi mumkin emas.']);
        }

        return DB::transaction(function () use ($filialId, $actor, $openingBalance, $date, $cashierId): CashSession {
            $existing = CashSession::query()
                ->where('filial_id', $filialId)
                ->where('cashier_id', $cashierId)
                ->whereDate('session_date', $date->toDateString())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages(['cash_session' => 'Bu kassir uchun ushbu sanada cash session allaqachon mavjud.']);
            }

            return CashSession::create([
                'filial_id' => $filialId,
                'cashier_id' => $cashierId,
                'opened_by_id' => $actor->id,
                'session_date' => $date->toDateString(),
                'status' => 'open',
                'opening_balance' => $openingBalance,
                'opened_at' => now(),
            ]);
        });
    }

    public function expectedCash(CashSession $session): float
    {
        $cashReceived = $this->effectiveSum(
            PaymentsModel::query()
                ->where('payment_type', 'cash')
                ->whereDate('created_at', $session->session_date->toDateString())
                ->where(function ($query) use ($session): void {
                    $query->where('cash_session_id', $session->id)
                        ->orWhere(function ($unassigned) use ($session): void {
                            $unassigned->whereNull('cash_session_id')->where(function ($branch) use ($session): void {
                                $branch->where('filial_id', $session->filial_id)
                                    ->orWhereHas('order', fn ($order) => $order->where('filial_id', $session->filial_id))
                                    ->orWhereHas('document', fn ($document) => $document->where('filial_id', $session->filial_id));
                            });
                        });
                })
        );

        return round((float) $session->opening_balance + $cashReceived, 2);
    }

    public function closeCashSession(CashSession $session, float $actualCash, User $actor, ?string $notes = null): CashSession
    {
        $this->assertBranchAccess((int) $session->filial_id, $actor);
        $actualCash = round($actualCash, 2);

        if ($actualCash < 0) {
            throw ValidationException::withMessages(['actual_cash' => 'Amaldagi kassa summasi manfiy bo‘lishi mumkin emas.']);
        }

        return DB::transaction(function () use ($session, $actualCash, $actor, $notes): CashSession {
            $session = CashSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($session->status !== 'open') {
                throw ValidationException::withMessages(['cash_session' => 'Faqat ochiq cash session yopiladi.']);
            }

            $expected = $this->expectedCash($session);
            $variance = round($actualCash - $expected, 2);
            $session->forceFill([
                'status' => 'closed',
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'variance' => $variance,
                'closed_by_id' => $actor->id,
                'closed_at' => now(),
                'notes' => $notes,
            ])->save();

            CashReconciliation::query()->updateOrCreate(
                [
                    'filial_id' => $session->filial_id,
                    'reconciliation_date' => $session->session_date->toDateString(),
                ],
                [
                    'cash_session_id' => $session->id,
                    'expected_amount' => $expected,
                    'actual_amount' => $actualCash,
                    'difference' => $variance,
                    'status' => abs($variance) <= 0.01 ? 'balanced' : 'variance',
                    'recorded_by_id' => $actor->id,
                    'notes' => $notes,
                ]
            );

            return $session->fresh(['reconciliation']);
        });
    }

    public function reconcileCashSession(CashSession $session, User $actor, ?string $notes = null): CashSession
    {
        $this->assertBranchAccess((int) $session->filial_id, $actor);

        return DB::transaction(function () use ($session, $actor, $notes): CashSession {
            $session = CashSession::query()->lockForUpdate()->findOrFail($session->id);
            if ($session->status !== 'closed') {
                throw ValidationException::withMessages(['cash_session' => 'Faqat yopilgan cash session reconciliation qilinadi.']);
            }

            $session->forceFill([
                'status' => 'reconciled',
                'reconciled_at' => now(),
                'notes' => $notes ?: $session->notes,
            ])->save();

            return $session->fresh();
        });
    }

    private function resolveCashSession(?int $filialId, string $paymentType, mixed $cashSessionId, ?int $cashierId): ?CashSession
    {
        if ($cashSessionId !== null) {
            $session = CashSession::query()->whereKey($cashSessionId)->firstOrFail();
            if ($session->status !== 'open' || ($filialId !== null && (int) $session->filial_id !== $filialId)) {
                throw ValidationException::withMessages(['cash_session_id' => 'Cash session ochiq va shu filialga tegishli bo‘lishi kerak.']);
            }

            return $session;
        }

        if ($paymentType !== 'cash' || $filialId === null) {
            return null;
        }

        return CashSession::query()
            ->where('filial_id', $filialId)
            ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
            ->where('status', 'open')
            ->whereDate('session_date', today()->toDateString())
            ->latest('id')
            ->first();
    }

    private function receiptNumber(PaymentsModel $payment, ?int $filialId): string
    {
        $code = $filialId ? FilialModel::query()->whereKey($filialId)->value('code') : null;
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', (string) ($code ?: 'FIL')));
        $code = substr($code ?: 'FIL', 0, 12);

        return sprintf('RCPT-%s-%s-%06d', $code, now()->format('Ymd'), $payment->id);
    }

    private function recordHistory(
        PaymentsModel $payment,
        ?string $from,
        string $to,
        ?User $actor,
        ?string $reason
    ): void {
        PaymentStatusHistory::query()->firstOrCreate([
            'payment_id' => $payment->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by_id' => $actor?->id,
            'reason' => $reason,
        ]);
    }

    private function assertBranchAccess(int $filialId, User $actor): void
    {
        if ($actor->hasAnyRole(['super_admin', 'admin_manager'])) {
            return;
        }

        if ($actor->hasRole('admin_filial') && (int) $actor->filial_id === $filialId) {
            return;
        }

        abort(403);
    }
}
