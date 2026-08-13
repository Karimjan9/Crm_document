<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filial', function (Blueprint $table): void {
            $table->string('phone', 40)->nullable()->after('description');
            $table->text('address')->nullable()->after('phone');
            $table->foreignId('manager_id')->nullable()->after('address')->constrained('users')->nullOnDelete();
            $table->time('work_start_time')->nullable()->after('manager_id');
            $table->time('work_end_time')->nullable()->after('work_start_time');
            $table->json('working_days')->nullable()->after('work_end_time');
            $table->json('holiday_dates')->nullable()->after('working_days');
            $table->decimal('monthly_expense', 15, 2)->default(0)->after('holiday_dates');
            $table->decimal('target_amount', 15, 2)->default(0)->after('monthly_expense');
            $table->decimal('commission_percent', 5, 2)->default(0)->after('target_amount');
            $table->unsignedInteger('daily_capacity')->nullable()->after('commission_percent');

            $table->index(['manager_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('filial', function (Blueprint $table): void {
            $table->dropIndex(['manager_id', 'name']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'phone', 'address', 'manager_id', 'work_start_time', 'work_end_time',
                'working_days', 'holiday_dates', 'monthly_expense', 'target_amount',
                'commission_percent', 'daily_capacity',
            ]);
        });
    }
};
