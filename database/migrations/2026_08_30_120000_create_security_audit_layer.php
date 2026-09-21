<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('event', 80);
                $table->string('auditable_type', 180)->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->string('method', 10)->nullable();
                $table->string('url', 1000)->nullable();
                $table->string('user_agent', 1000)->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['auditable_type', 'auditable_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['event', 'created_at']);
                $table->index(['ip_address', 'created_at']);
            });
        }

        if (! Schema::hasTable('security_alerts')) {
            Schema::create('security_alerts', function (Blueprint $table): void {
                $table->id();
                $table->string('fingerprint', 128)->unique();
                $table->string('type', 80);
                $table->string('severity', 20)->default('warning');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedInteger('occurrences')->default(1);
                $table->json('context')->nullable();
                $table->dateTime('first_seen_at');
                $table->dateTime('last_seen_at');
                $table->timestamp('notified_at')->nullable();

                $table->index(['type', 'last_seen_at']);
                $table->index(['severity', 'last_seen_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
