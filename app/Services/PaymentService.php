<?php

namespace App\Services;

use App\Models\DocumentsModel;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly PaymentLedgerService $ledger)
    {
    }

    public function record(
        Order $order,
        float $amount,
        string $paymentType,
        ?User $actor = null,
        ?int $documentId = null,
        array $options = []
    ): PaymentsModel {
        return DB::transaction(function () use ($order, $amount, $paymentType, $actor, $documentId, $options): PaymentsModel {
            $amount = round($amount, 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'To‘lov summasi 0 dan katta bo‘lishi kerak.']);
            }
            if (! in_array($paymentType, PaymentsModel::TYPES, true)) {
                throw ValidationException::withMessages(['payment_type' => 'To‘lov turi noto‘g‘ri.']);
            }

            $document = null;
            if ($documentId !== null) {
                $document = DocumentsModel::query()
                    ->whereKey($documentId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $document->order_id !== (int) $order->id) {
                    throw ValidationException::withMessages([
                        'document_id' => 'Hujjat ushbu orderga tegishli emas.',
                    ]);
                }
            }

            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $paid = $this->ledger->effectiveSum(PaymentsModel::query()->where('order_id', $order->id));
            $balance = round((float) $order->total_amount - $paid, 2);
            if ($balance <= 0 || $amount > $balance) {
                throw ValidationException::withMessages(['amount' => 'To‘lov order qoldig‘idan oshib ketmasligi kerak.']);
            }

            if ($documentId !== null) {
                $documentPaid = $this->ledger->effectiveSum(PaymentsModel::query()->where('document_id', $document->id));
                $documentBalance = round((float) $document->final_price - $documentPaid, 2);
                if ($amount > $documentBalance) {
                    throw ValidationException::withMessages(['amount' => 'To‘lov tanlangan hujjat qoldig‘idan oshib ketdi.']);
                }
            }

            $payment = $this->ledger->create($order, $document, $amount, $paymentType, $actor, $options);
            $this->ledger->syncAggregates($payment);

            return $payment->fresh();
        });
    }

    public function recordForDocument(
        DocumentsModel $document,
        float $amount,
        string $paymentType,
        ?User $actor = null,
        array $options = []
    ): PaymentsModel {
        return DB::transaction(function () use ($document, $amount, $paymentType, $actor, $options): PaymentsModel {
            $document = DocumentsModel::query()->lockForUpdate()->findOrFail($document->id);
            $amount = round($amount, 2);
            $paid = $this->ledger->effectiveSum(PaymentsModel::query()->where('document_id', $document->id));
            $balance = round((float) $document->final_price - $paid, 2);

            if ($amount <= 0 || $amount > $balance) {
                throw ValidationException::withMessages(['amount' => 'To‘lov summasi hujjat qoldig‘idan oshmasligi kerak.']);
            }

            $payment = $this->ledger->create(null, $document, $amount, $paymentType, $actor, $options);
            $this->ledger->syncAggregates($payment);

            return $payment->fresh();
        });
    }
}
