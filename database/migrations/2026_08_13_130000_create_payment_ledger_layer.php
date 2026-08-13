<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->constrained('filial')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('opened_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('session_date');
            $table->string('status', 20)->default('open');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('actual_cash', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->default(0);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['filial_id', 'cashier_id', 'session_date']);
            $table->index(['filial_id', 'status', 'session_date']);
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('issued_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number', 80)->unique();
            $table->string('status', 20)->default('issued');
            $table->string('currency', 3)->default('UZS');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['filial_id', 'status', 'issued_at']);
        });

        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('order_price_line_id')->nullable()->constrained('order_price_lines')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('line_type', 50)->default('service');
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'line_type']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('filial_id')->nullable()->after('order_id')->constrained('filial')->nullOnDelete();
            $table->string('receipt_number', 80)->nullable()->after('amount');
            $table->string('status', 30)->default('confirmed')->after('payment_type');
            $table->string('confirmation_status', 30)->default('confirmed')->after('status');
            $table->foreignId('confirmed_by_id')->nullable()->after('paid_by_admin_id')->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by_id');
            $table->foreignId('cashier_id')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->after('cashier_id')->constrained('cash_sessions')->nullOnDelete();
            $table->string('online_transaction_id', 160)->nullable()->after('cash_session_id');
            $table->string('payment_proof_path')->nullable()->after('online_transaction_id');
            $table->string('payment_proof_original_name')->nullable()->after('payment_proof_path');
            $table->string('payment_proof_mime', 100)->nullable()->after('payment_proof_original_name');
            $table->unsignedBigInteger('payment_proof_size')->nullable()->after('payment_proof_mime');
            $table->decimal('refund_amount', 15, 2)->default(0)->after('payment_proof_size');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->foreignId('refunded_by_id')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('refunded_by_id');
            $table->foreignId('cancelled_by_id')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by_id');
            $table->foreignId('invoice_id')->nullable()->after('cancellation_reason')->constrained('invoices')->nullOnDelete();
            $table->json('metadata')->nullable()->after('invoice_id');

            $table->unique('receipt_number');
            $table->unique('online_transaction_id');
            $table->index(['filial_id', 'status', 'created_at']);
            $table->index(['cash_session_id', 'status']);
            $table->index(['invoice_id', 'status']);
        });

        Schema::create('payment_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'created_at']);
        });

        Schema::create('payment_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('refunded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('refund_number', 80)->nullable()->unique();
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('confirmed');
            $table->string('reason', 500)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'status']);
        });

        Schema::table('cash_reconciliations', function (Blueprint $table): void {
            $table->foreignId('cash_session_id')->nullable()->after('recorded_by_id')->constrained('cash_sessions')->nullOnDelete();
            $table->index(['filial_id', 'cash_session_id']);
        });

        $this->backfillLegacyPayments();
    }

    private function backfillLegacyPayments(): void
    {
        DB::table('payments')
            ->orderBy('id')
            ->get()
            ->each(function (object $payment): void {
                $filialId = null;

                if ($payment->order_id) {
                    $filialId = DB::table('orders')->where('id', $payment->order_id)->value('filial_id');
                }

                if (! $filialId && $payment->document_id) {
                    $filialId = DB::table('documents')->where('id', $payment->document_id)->value('filial_id');
                }

                DB::table('payments')
                    ->where('id', $payment->id)
                    ->update([
                        'filial_id' => $filialId,
                        'receipt_number' => 'LEGACY-RCPT-' . $payment->id,
                        'status' => 'confirmed',
                        'confirmation_status' => 'confirmed',
                        'confirmed_by_id' => $payment->paid_by_admin_id,
                        'confirmed_at' => $payment->created_at,
                        'cashier_id' => $payment->paid_by_admin_id,
                    ]);

                DB::table('payment_status_histories')->insert([
                    'payment_id' => $payment->id,
                    'from_status' => null,
                    'to_status' => 'confirmed',
                    'changed_by_id' => $payment->paid_by_admin_id,
                    'reason' => 'Legacy payment ledger backfill',
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->created_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('cash_reconciliations', function (Blueprint $table): void {
            $table->dropForeign(['cash_session_id']);
            $table->dropIndex(['filial_id', 'cash_session_id']);
            $table->dropColumn('cash_session_id');
        });

        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_status_histories');

        Schema::table('payments', function (Blueprint $table): void {
            foreach (['filial_id', 'confirmed_by_id', 'cashier_id', 'cash_session_id', 'refunded_by_id', 'cancelled_by_id', 'invoice_id'] as $foreign) {
                if (Schema::hasColumn('payments', $foreign)) {
                    $table->dropForeign([$foreign]);
                }
            }

            foreach (['receipt_number', 'online_transaction_id'] as $unique) {
                if (Schema::hasColumn('payments', $unique)) {
                    $table->dropUnique([$unique]);
                }
            }

            $table->dropColumn([
                'filial_id', 'receipt_number', 'status', 'confirmation_status',
                'confirmed_by_id', 'confirmed_at', 'cashier_id', 'cash_session_id',
                'online_transaction_id', 'payment_proof_path', 'payment_proof_original_name',
                'payment_proof_mime', 'payment_proof_size', 'refund_amount', 'refunded_at',
                'refunded_by_id', 'cancelled_at', 'cancelled_by_id', 'cancellation_reason',
                'invoice_id', 'metadata',
            ]);
        });

        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('cash_sessions');
    }
};
