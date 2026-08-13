<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('payment_type', 30)->default('admin_entry')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->enum('payment_type', ['cash', 'card', 'online', 'admin_entry'])
                ->default('admin_entry')
                ->change();
        });
    }
};
