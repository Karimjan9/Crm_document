<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('package_templates', 'product_code')) {
            Schema::table('package_templates', function (Blueprint $table): void {
                $table->string('product_code', 64)->nullable()->unique()->after('name');
                $table->decimal('standard_price', 12, 2)->default(0)->after('promo_price');
                $table->decimal('express_price', 12, 2)->nullable()->after('standard_price');
                $table->unsignedInteger('standard_deadline_days')->default(0)->after('express_price');
                $table->unsignedInteger('express_deadline_days')->nullable()->after('standard_deadline_days');
                $table->decimal('margin_percent', 5, 2)->default(0)->after('express_deadline_days');
                $table->string('delivery_type', 30)->default('pickup')->after('margin_percent');
                $table->boolean('is_sellable')->default(true)->after('is_active');
            });
        }

        if (! Schema::hasTable('package_template_filials')) {
            Schema::create('package_template_filials', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('package_template_id')->constrained('package_templates')->cascadeOnDelete();
                $table->foreignId('filial_id')->constrained('filial')->cascadeOnDelete();
                $table->boolean('is_available')->default(true);
                $table->decimal('standard_price', 12, 2)->nullable();
                $table->decimal('express_price', 12, 2)->nullable();
                $table->unsignedInteger('standard_deadline_days')->nullable();
                $table->unsignedInteger('express_deadline_days')->nullable();
                $table->timestamps();

                $table->unique(['package_template_id', 'filial_id']);
                $table->index(['filial_id', 'is_available']);
            });
        }

        if (! Schema::hasTable('package_template_addons')) {
            Schema::create('package_template_addons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('package_template_id')->constrained('package_templates')->cascadeOnDelete();
                $table->foreignId('service_addon_id')->constrained('service_addons')->restrictOnDelete();
                $table->boolean('is_included')->default(false);
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('price_override', 12, 2)->nullable();
                $table->unsignedInteger('deadline_days_override')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['package_template_id', 'service_addon_id'], 'pkg_tpl_addons_tpl_addon_unique');
                $table->index(['package_template_id', 'is_included', 'is_active'], 'pkg_tpl_addons_included_active_idx');
            });
        } else {
            if (! $this->hasIndex(
                'package_template_addons',
                'pkg_tpl_addons_tpl_addon_unique',
                ['package_template_id', 'service_addon_id'],
                true,
            )) {
                Schema::table('package_template_addons', function (Blueprint $table): void {
                    $table->unique(['package_template_id', 'service_addon_id'], 'pkg_tpl_addons_tpl_addon_unique');
                });
            }
            if (! $this->hasIndex(
                'package_template_addons',
                'pkg_tpl_addons_included_active_idx',
                ['package_template_id', 'is_included', 'is_active'],
                false,
            )) {
                Schema::table('package_template_addons', function (Blueprint $table): void {
                    $table->index(['package_template_id', 'is_included', 'is_active'], 'pkg_tpl_addons_included_active_idx');
                });
            }
        }

        if (! Schema::hasColumn('orders', 'responsible_user_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->foreignId('responsible_user_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
                $table->foreignId('package_template_id')->nullable()->after('responsible_user_id')->constrained('package_templates')->nullOnDelete();
                $table->string('package_variant', 20)->default('standard')->after('package_template_id');
                $table->decimal('package_price', 12, 2)->nullable()->after('package_variant');
                $table->unsignedInteger('package_deadline_days')->nullable()->after('package_price');
                $table->decimal('package_margin_percent', 5, 2)->nullable()->after('package_deadline_days');
                $table->string('package_name_snapshot')->nullable()->after('package_margin_percent');
                $table->string('delivery_type', 30)->default('pickup')->after('package_name_snapshot');
                $table->string('customer_source', 80)->nullable()->after('source');

                $table->index(['package_template_id', 'package_variant']);
                $table->index(['responsible_user_id', 'status']);
                $table->index(['customer_source', 'created_at']);
            });
        }

        DB::table('package_templates')
            ->orderBy('id')
            ->get(['id', 'base_price', 'promo_price', 'is_active'])
            ->each(function (object $template): void {
                DB::table('package_templates')
                    ->where('id', $template->id)
                    ->update([
                        'product_code' => 'PKG-' . str_pad((string) $template->id, 6, '0', STR_PAD_LEFT),
                        'standard_price' => (float) ($template->promo_price ?: $template->base_price ?: 0),
                        'is_sellable' => (bool) $template->is_active,
                    ]);
            });

        DB::table('orders')->update([
            'responsible_user_id' => DB::raw('created_by_id'),
            'customer_source' => DB::raw('source'),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('package_template_addons');
        Schema::dropIfExists('package_template_filials');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['responsible_user_id']);
            $table->dropForeign(['package_template_id']);
            $table->dropIndex(['package_template_id', 'package_variant']);
            $table->dropIndex(['responsible_user_id', 'status']);
            $table->dropIndex(['customer_source', 'created_at']);
            $table->dropColumn([
                'responsible_user_id',
                'package_template_id',
                'package_variant',
                'package_price',
                'package_deadline_days',
                'package_margin_percent',
                'package_name_snapshot',
                'delivery_type',
                'customer_source',
            ]);
        });

        Schema::table('package_templates', function (Blueprint $table): void {
            $table->dropUnique(['product_code']);
            $table->dropColumn([
                'product_code',
                'standard_price',
                'express_price',
                'standard_deadline_days',
                'express_deadline_days',
                'margin_percent',
                'delivery_type',
                'is_sellable',
            ]);
        });
    }

    private function hasIndex(string $table, string $index, array $columns = [], ?bool $unique = null): bool
    {
        return collect(Schema::getIndexes($table))->contains(
            function (array $definition) use ($index, $columns, $unique): bool {
                if (($definition['name'] ?? null) === $index) {
                    return true;
                }

                return $columns !== []
                    && ($definition['columns'] ?? []) === $columns
                    && ($unique === null || (bool) ($definition['unique'] ?? false) === $unique);
            }
        );
    }
};
