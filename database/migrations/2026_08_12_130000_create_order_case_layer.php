<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('filial_id')->constrained('filial')->restrictOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_code', 64)->unique();
            $table->string('tracking_token', 96)->unique();
            $table->string('status', 40)->default('received');
            $table->string('priority', 20)->default('normal');
            $table->string('source', 80)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('UZS');
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('profit_amount', 12, 2)->default(0);
            $table->timestamp('promised_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->foreignId('order_id')
                ->nullable()
                ->after('id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->index(['order_id', 'status_doc']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('order_id')
                ->nullable()
                ->after('document_id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_price_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('line_type', 50);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'line_type']);
            $table->index('document_id');
        });

        Schema::create('order_checklists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('title');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'is_completed']);
        });

        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('sent');
            $table->string('tracking_code', 80)->unique()->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 40)->nullable();
            $table->text('address')->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['courier_id', 'status']);
            $table->index(['order_id', 'status']);
        });

        Schema::create('order_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('event', 60);
            $table->string('channel', 30)->default('internal');
            $table->string('recipient', 120)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index(['status', 'channel']);
        });

        Schema::create('order_costs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('category', 80)->default('other');
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'category']);
        });

        $this->backfillLegacyDocuments();
    }

    private function backfillLegacyDocuments(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')
            ->whereNull('order_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $document): void {
                $token = bin2hex(random_bytes(32));
                $finalPrice = (float) ($document->final_price ?? 0);
                $paidAmount = (float) ($document->paid_amount ?? 0);
                $status = ($document->status_doc ?? 'process') === 'finish'
                    ? ($finalPrice <= 0 || $paidAmount >= $finalPrice ? 'ready_for_delivery' : ($paidAmount > 0 ? 'partially_paid' : 'awaiting_payment'))
                    : 'in_processing';
                $now = now();

                $orderId = DB::table('orders')->insertGetId([
                    'client_id' => $document->client_id,
                    'filial_id' => $document->filial_id,
                    'created_by_id' => $document->user_id,
                    'order_code' => 'ORD-LEGACY-' . $document->id,
                    'tracking_token' => $token,
                    'status' => $status,
                    'priority' => 'normal',
                    'title' => 'Legacy document ' . ($document->document_code ?: $document->id),
                    'currency' => 'UZS',
                    'subtotal_amount' => (float) ($document->service_price ?? 0) + (float) ($document->addons_total_price ?? 0),
                    'discount_amount' => max(
                        ((float) ($document->service_price ?? 0) + (float) ($document->addons_total_price ?? 0))
                            - (float) ($document->final_price ?? 0),
                        0
                    ),
                    'total_amount' => $finalPrice,
                    'paid_amount' => $paidAmount,
                    'cost_amount' => 0,
                    'profit_amount' => (float) ($document->final_price ?? 0),
                    'created_at' => $document->created_at ?? $now,
                    'updated_at' => $now,
                ]);

                DB::table('documents')->where('id', $document->id)->update([
                    'order_id' => $orderId,
                ]);

                DB::table('payments')
                    ->where('document_id', $document->id)
                    ->update(['order_id' => $orderId]);

                $legacySubtotal = (float) ($document->service_price ?? 0) + (float) ($document->addons_total_price ?? 0);
                $legacyDiscount = max($legacySubtotal - (float) ($document->final_price ?? 0), 0);

                DB::table('order_price_lines')->insert([
                    'order_id' => $orderId,
                    'document_id' => $document->id,
                    'line_type' => 'legacy_total',
                    'name' => 'Legacy document total',
                    'quantity' => 1,
                    'unit_price' => $legacySubtotal,
                    'total_price' => $legacySubtotal,
                    'cost_amount' => 0,
                    'metadata' => json_encode(['document_code' => $document->document_code]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($legacyDiscount > 0) {
                    DB::table('order_price_lines')->insert([
                        'order_id' => $orderId,
                        'document_id' => $document->id,
                        'line_type' => 'discount',
                        'name' => 'Legacy discount',
                        'quantity' => 1,
                        'unit_price' => -$legacyDiscount,
                        'total_price' => -$legacyDiscount,
                        'cost_amount' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('order_checklists')->insert([
                    'order_id' => $orderId,
                    'document_id' => $document->id,
                    'title' => 'Legacy hujjat ma\'lumotlari tekshiruvi',
                    'is_required' => true,
                    'is_completed' => ($document->status_doc ?? 'process') === 'finish',
                    'sort_order' => 1,
                    'completed_at' => ($document->status_doc ?? 'process') === 'finish' ? ($document->updated_at ?? $now) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('order_status_histories')->insert([
                    'order_id' => $orderId,
                    'from_status' => null,
                    'to_status' => $status,
                    'changed_by_id' => $document->user_id,
                    'reason' => 'Legacy document backfill',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id', 'created_at']);
            $table->dropColumn('order_id');
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id', 'status_doc']);
            $table->dropColumn('order_id');
        });

        Schema::dropIfExists('order_costs');
        Schema::dropIfExists('order_notifications');
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_checklists');
        Schema::dropIfExists('order_price_lines');
        Schema::dropIfExists('orders');
    }
};
