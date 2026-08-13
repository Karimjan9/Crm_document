<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'status')) {
            return;
        }

        DB::table('documents')
            ->where('paid_amount', '>', 0)
            ->orderBy('id')
            ->get(['id', 'order_id', 'filial_id', 'user_id', 'paid_amount'])
            ->each(function (object $document): void {
                $recorded = $this->effectiveDocumentPaid((int) $document->id);
                $difference = round((float) $document->paid_amount - $recorded, 2);

                if ($difference > 0.01) {
                    $this->insertAdjustment(
                        $difference,
                        $document->id,
                        $document->order_id,
                        $document->filial_id,
                        $document->user_id,
                        'Legacy document paid_amount reconciliation'
                    );
                }
            });

        DB::table('orders')
            ->where('paid_amount', '>', 0)
            ->orderBy('id')
            ->get(['id', 'filial_id', 'created_by_id', 'paid_amount'])
            ->each(function (object $order): void {
                $recorded = $this->effectiveOrderPaid((int) $order->id);
                $difference = round((float) $order->paid_amount - $recorded, 2);

                if ($difference > 0.01) {
                    $this->insertAdjustment(
                        $difference,
                        null,
                        $order->id,
                        $order->filial_id,
                        $order->created_by_id,
                        'Legacy order paid_amount reconciliation'
                    );
                }
            });
    }

    private function effectiveDocumentPaid(int $documentId): float
    {
        return round((float) DB::table('payments')
            ->where('document_id', $documentId)
            ->whereIn('status', ['confirmed', 'partially_refunded'])
            ->selectRaw('COALESCE(SUM(amount - COALESCE(refund_amount, 0)), 0) AS total')
            ->value('total'), 2);
    }

    private function effectiveOrderPaid(int $orderId): float
    {
        return round((float) DB::table('payments')
            ->where('order_id', $orderId)
            ->whereIn('status', ['confirmed', 'partially_refunded'])
            ->selectRaw('COALESCE(SUM(amount - COALESCE(refund_amount, 0)), 0) AS total')
            ->value('total'), 2);
    }

    private function insertAdjustment(
        float $amount,
        ?int $documentId,
        ?int $orderId,
        ?int $filialId,
        ?int $actorId,
        string $reason
    ): void {
        $now = now();
        $id = DB::table('payments')->insertGetId([
            'document_id' => $documentId,
            'order_id' => $orderId,
            'filial_id' => $filialId,
            'amount' => $amount,
            'payment_type' => 'admin_entry',
            'status' => 'confirmed',
            'confirmation_status' => 'confirmed',
            'paid_by_admin_id' => $actorId,
            'confirmed_by_id' => $actorId,
            'confirmed_at' => $now,
            'cashier_id' => $actorId,
            'refund_amount' => 0,
            'metadata' => json_encode(['legacy_adjustment' => true, 'reason' => $reason]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payments')->where('id', $id)->update([
            'receipt_number' => 'LEGACY-ADJ-RCPT-' . $id,
        ]);

        DB::table('payment_status_histories')->insert([
            'payment_id' => $id,
            'from_status' => null,
            'to_status' => 'confirmed',
            'changed_by_id' => $actorId,
            'reason' => $reason,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Financial backfill rows are intentionally retained for auditability.
    }
};
