<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tariffs', function (Blueprint $table): void {
            $table->id();
            $table->string('line_type', 40);
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('service_addon_id')->nullable()->constrained('service_addons')->nullOnDelete();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('price_key', 100)->nullable();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('variant', 20)->default('standard');
            $table->string('season_code', 60)->nullable();
            $table->string('name', 255);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->unsignedInteger('deadline')->nullable();
            $table->string('currency', 3)->default('UZS');
            $table->timestamp('effective_from');
            $table->timestamp('effective_to')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['line_type', 'service_id', 'effective_from']);
            $table->index(['line_type', 'service_addon_id', 'effective_from']);
            $table->index(['filial_id', 'variant', 'effective_from']);
            $table->index(['partner_id', 'variant', 'effective_from']);
            $table->index(['price_key', 'effective_from']);
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->json('pricing_snapshot')->nullable()->after('discount');
            $table->json('pricing_context')->nullable()->after('pricing_snapshot');
            $table->timestamp('pricing_locked_at')->nullable()->after('pricing_context');
            $table->string('discount_approval_status', 20)->default('not_required')->after('pricing_locked_at');
        });

        Schema::table('order_price_lines', function (Blueprint $table): void {
            $table->foreignId('price_tariff_id')
                ->nullable()
                ->after('source_id')
                ->constrained('price_tariffs')
                ->nullOnDelete();
            $table->string('pricing_line_type', 40)->nullable()->after('line_type');
            $table->json('pricing_context')->nullable()->after('metadata');
            $table->timestamp('effective_from')->nullable()->after('pricing_context');

            $table->index(['document_id', 'pricing_line_type']);
        });

        Schema::create('pricing_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['document_id', 'status']);
            $table->index(['order_id', 'status']);
        });

        $this->backfillLegacyPricing();
    }

    private function backfillLegacyPricing(): void
    {
        $now = now();

        if (Schema::hasTable('services')) {
            DB::table('services')->orderBy('id')->get()->each(function (object $service) use ($now): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => 'base_service',
                    'service_id' => $service->id,
                    'variant' => 'standard',
                    'name' => $service->name,
                    'price' => $service->price ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $service->deadline ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $service->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        if (Schema::hasTable('service_addons')) {
            DB::table('service_addons')->orderBy('id')->get()->each(function (object $addon) use ($now): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => 'addon',
                    'service_id' => $addon->service_id,
                    'service_addon_id' => $addon->id,
                    'variant' => 'standard',
                    'name' => $addon->name,
                    'price' => $addon->price ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $addon->deadline ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $addon->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        foreach ([
            ['table' => 'document_type_addition', 'line_type' => 'addon', 'key_prefix' => 'document_addon:', 'parent' => 'document_type_id'],
            ['table' => 'document_direction_addition', 'line_type' => 'addon', 'key_prefix' => 'direction_addon:', 'parent' => 'document_direction_id'],
        ] as $definition) {
            if (!Schema::hasTable($definition['table'])) {
                continue;
            }

            DB::table($definition['table'])->orderBy('id')->get()->each(function (object $addon) use ($now, $definition): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => $definition['line_type'],
                    'source_id' => $addon->id,
                    'price_key' => $definition['key_prefix'] . $addon->id,
                    'variant' => 'standard',
                    'name' => $addon->name,
                    'price' => $addon->amount ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $addon->day ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $addon->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        if (Schema::hasTable('apostil_static')) {
            DB::table('apostil_static')->orderBy('id')->get()->each(function (object $apostil) use ($now): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => 'apostille',
                    'source_id' => $apostil->id,
                    'price_key' => 'apostille:' . $apostil->id,
                    'variant' => 'standard',
                    'name' => $apostil->name,
                    'price' => $apostil->price ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $apostil->days ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $apostil->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        if (Schema::hasTable('consul')) {
            DB::table('consul')->orderBy('id')->get()->each(function (object $consul) use ($now): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => 'consulate',
                    'source_id' => $consul->id,
                    'price_key' => 'consul:' . $consul->id,
                    'variant' => 'standard',
                    'name' => $consul->name,
                    'price' => $consul->amount ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $consul->day ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $consul->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        if (Schema::hasTable('consulates_type')) {
            DB::table('consulates_type')->orderBy('id')->get()->each(function (object $consulate) use ($now): void {
                DB::table('price_tariffs')->insert([
                    'line_type' => 'consulate',
                    'source_id' => $consulate->id,
                    'price_key' => 'consulate:' . $consulate->id,
                    'variant' => 'standard',
                    'name' => $consulate->name,
                    'price' => $consulate->amount ?? $consulate->price ?? 0,
                    'cost_amount' => 0,
                    'deadline' => $consulate->day ?? 0,
                    'currency' => 'UZS',
                    'effective_from' => $consulate->created_at ?? $now,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }

        if (Schema::hasTable('documents')) {
            DB::table('documents')->orderBy('id')->get()->each(function (object $document) use ($now): void {
                $subtotal = round((float) ($document->service_price ?? 0) + (float) ($document->addons_total_price ?? 0), 2);
                $discount = round(max($subtotal - (float) ($document->final_price ?? 0), 0), 2);
                $lineItems = [[
                    'line_type' => 'base_service',
                    'source_id' => $document->service_id,
                    'name' => 'Legacy service',
                    'quantity' => 1,
                    'unit_price' => (float) ($document->service_price ?? 0),
                    'total_price' => (float) ($document->service_price ?? 0),
                    'cost_amount' => 0,
                ]];
                if ((float) ($document->addons_total_price ?? 0) > 0) {
                    $lineItems[] = [
                        'line_type' => 'addon',
                        'source_id' => null,
                        'name' => 'Legacy addons',
                        'quantity' => 1,
                        'unit_price' => (float) $document->addons_total_price,
                        'total_price' => (float) $document->addons_total_price,
                        'cost_amount' => 0,
                    ];
                }
                if ($discount > 0) {
                    $lineItems[] = [
                        'line_type' => 'discount',
                        'source_id' => null,
                        'name' => 'Legacy discount',
                        'quantity' => 1,
                        'unit_price' => -$discount,
                        'total_price' => -$discount,
                        'cost_amount' => 0,
                    ];
                }

                DB::table('documents')->where('id', $document->id)->update([
                    'pricing_snapshot' => json_encode([
                        'version' => 1,
                        'legacy' => true,
                        'calculated_at' => ($document->updated_at ?? $now),
                        'line_items' => $lineItems,
                        'subtotal' => $subtotal,
                        'discount_amount' => $discount,
                        'final_price' => (float) ($document->final_price ?? 0),
                        'currency' => 'UZS',
                    ]),
                    'pricing_context' => json_encode([
                        'variant' => 'standard',
                        'source' => 'legacy_backfill',
                        'as_of' => ($document->created_at ?? $now),
                    ]),
                    'pricing_locked_at' => $document->created_at ?? $now,
                    'discount_approval_status' => 'not_required',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_approvals');

        if (Schema::hasTable('order_price_lines')) {
            Schema::table('order_price_lines', function (Blueprint $table): void {
                $table->dropForeign(['price_tariff_id']);
                $table->dropIndex(['document_id', 'pricing_line_type']);
                $table->dropColumn([
                    'price_tariff_id',
                    'pricing_line_type',
                    'pricing_context',
                    'effective_from',
                ]);
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->dropColumn([
                    'pricing_snapshot',
                    'pricing_context',
                    'pricing_locked_at',
                    'discount_approval_status',
                ]);
            });
        }

        Schema::dropIfExists('price_tariffs');
    }
};
