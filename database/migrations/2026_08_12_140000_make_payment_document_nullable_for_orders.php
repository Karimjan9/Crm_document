<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('document_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('payments')->whereNull('document_id')->exists()) {
            throw new RuntimeException('Cannot rollback payment document_id while order-level payments exist.');
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('document_id')->nullable(false)->change();
        });
    }
};
