<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name', 180);
            $table->string('code', 40)->unique();
            $table->string('type', 40)->default('other');
            $table->string('contact_name', 120)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('tax_id', 40)->nullable();
            $table->string('billing_email', 180)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->unsignedSmallInteger('payment_terms_days')->default(30);
            $table->string('currency', 3)->default('UZS');
            $table->string('status', 20)->default('active');
            $table->string('brand_name', 180)->nullable();
            $table->string('brand_logo_url', 500)->nullable();
            $table->string('brand_primary_color', 7)->default('#2563eb');
            $table->string('brand_secondary_color', 7)->default('#0f172a');
            $table->string('tracking_title', 180)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('partner_id')
                ->nullable()
                ->after('filial_id')
                ->constrained('partners')
                ->nullOnDelete();
            $table->index(['partner_id', 'deleted_at']);
        });

        Schema::create('partner_filials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('filial_id')->constrained('filial')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['partner_id', 'filial_id']);
            $table->index(['filial_id', 'is_active']);
        });

        Schema::create('partner_api_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('token_prefix', 20);
            $table->string('token_hash', 64)->unique();
            $table->json('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'revoked_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('partner_id')
                ->nullable()
                ->after('client_id')
                ->constrained('partners')
                ->nullOnDelete();
            $table->string('partner_reference', 120)->nullable();
            $table->decimal('partner_discount_percent', 5, 2)->default(0);
            $table->decimal('partner_discount_amount', 12, 2)->default(0);
            $table->string('billing_status', 20)->default('unbilled');
            $table->json('partner_metadata')->nullable();

            $table->index(['partner_id', 'created_at']);
            $table->index(['partner_id', 'billing_status']);
        });

        Schema::create('partner_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->string('invoice_number', 80)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('currency', 3)->default('UZS');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('payment_terms_days')->default(30);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'period_start', 'period_end']);
            $table->index(['partner_id', 'status']);
        });

        Schema::create('partner_invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_invoice_id')->constrained('partner_invoices')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('order_code', 64)->nullable();
            $table->string('description', 255);
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['partner_invoice_id', 'order_id']);
            $table->index(['order_id', 'created_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('partner_invoice_id')
                ->nullable()
                ->after('billing_status')
                ->constrained('partner_invoices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'partner_invoice_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropForeign(['partner_invoice_id']);
                $table->dropColumn('partner_invoice_id');
            });
        }

        Schema::dropIfExists('partner_invoice_lines');
        Schema::dropIfExists('partner_invoices');

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'partner_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropForeign(['partner_id']);
                $table->dropIndex(['partner_id', 'created_at']);
                $table->dropIndex(['partner_id', 'billing_status']);
                $table->dropColumn([
                    'partner_id',
                    'partner_reference',
                    'partner_discount_percent',
                    'partner_discount_amount',
                    'billing_status',
                    'partner_metadata',
                ]);
            });
        }

        Schema::dropIfExists('partner_api_keys');
        Schema::dropIfExists('partner_filials');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'partner_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropForeign(['partner_id']);
                $table->dropIndex(['partner_id', 'deleted_at']);
                $table->dropColumn('partner_id');
            });
        }

        Schema::dropIfExists('partners');
    }
};
