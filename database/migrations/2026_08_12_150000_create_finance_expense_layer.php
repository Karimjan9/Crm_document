<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 80)->unique();
            $table->string('expense_type', 30)->default('branch');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('phone', 40)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('tax_id', 80)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('cost_centers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 80)->unique();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'period_start', 'period_end']);
            $table->index(['status', 'period_end']);
        });

        Schema::table('expense_admin', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('filial_id')->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('category_id')->constrained('expense_vendors')->nullOnDelete();
            $table->string('payment_method', 30)->default('cash')->after('description');
            $table->string('receipt_path')->nullable()->after('payment_method');
            $table->string('receipt_original_name')->nullable()->after('receipt_path');
            $table->foreignId('approver_id')->nullable()->after('receipt_original_name')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approver_id');
            $table->string('approval_status', 20)->default('approved')->after('approved_at');
            $table->boolean('is_recurring')->default(false)->after('approval_status');
            $table->string('recurrence_rule', 30)->nullable()->after('is_recurring');
            $table->date('recurrence_start')->nullable()->after('recurrence_rule');
            $table->date('recurrence_end')->nullable()->after('recurrence_start');
            $table->date('next_occurrence')->nullable()->after('recurrence_end');
            $table->foreignId('budget_id')->nullable()->after('next_occurrence')->constrained('budgets')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->after('budget_id')->constrained('cost_centers')->nullOnDelete();
            $table->string('expense_type', 30)->default('branch')->after('cost_center_id');
            $table->date('expense_date')->nullable()->after('expense_type');
            $table->string('currency', 3)->default('UZS')->after('expense_date');
            $table->json('metadata')->nullable()->after('currency');

            $table->index(['filial_id', 'expense_type', 'expense_date']);
            $table->index(['approval_status', 'next_occurrence']);
            $table->index(['category_id', 'expense_date']);
        });

        Schema::create('expense_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expense_id')->constrained('expense_admin')->cascadeOnDelete();
            $table->foreignId('filial_id')->constrained('filial')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 7, 3)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['expense_id', 'filial_id']);
            $table->index(['filial_id', 'created_at']);
        });

        $defaultCategoryId = DB::table('expense_categories')->insertGetId([
            'name' => 'Umumiy xarajat',
            'code' => 'general',
            'expense_type' => 'branch',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expense_admin')->whereNull('category_id')->update([
            'category_id' => $defaultCategoryId,
            'expense_type' => 'branch',
            'approval_status' => 'approved',
        ]);

        DB::table('expense_admin')
            ->select(['id', 'filial_id', 'amount'])
            ->orderBy('id')
            ->get()
            ->each(function (object $expense): void {
                DB::table('expense_allocations')->insert([
                    'expense_id' => $expense->id,
                    'filial_id' => $expense->filial_id,
                    'amount' => $expense->amount,
                    'percentage' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_allocations');

        Schema::table('expense_admin', function (Blueprint $table): void {
            $columns = [
                'category_id', 'vendor_id', 'payment_method', 'receipt_path',
                'receipt_original_name', 'approver_id', 'approved_at', 'approval_status',
                'is_recurring', 'recurrence_rule', 'recurrence_start', 'recurrence_end',
                'next_occurrence', 'budget_id', 'cost_center_id', 'expense_type',
                'expense_date', 'currency', 'metadata',
            ];

            foreach (['category_id', 'vendor_id', 'approver_id', 'budget_id', 'cost_center_id'] as $foreignColumn) {
                $table->dropForeign([$foreignColumn]);
            }

            $table->dropIndex('expense_admin_filial_id_expense_type_expense_date_index');
            $table->dropIndex('expense_admin_approval_status_next_occurrence_index');
            $table->dropIndex('expense_admin_category_id_expense_date_index');

            $table->dropColumn($columns);
        });

        Schema::dropIfExists('budgets');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('expense_vendors');
        Schema::dropIfExists('expense_categories');
    }
};
