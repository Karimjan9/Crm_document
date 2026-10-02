<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('external_id')->nullable()->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('work_item_id')->nullable()->constrained('work_items')->nullOnDelete();
            $table->string('telegram_chat_id', 80);
            $table->string('telegram_user_id', 80)->nullable();
            $table->string('telegram_username', 120)->nullable();
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->string('name', 160);
            $table->string('phone', 40)->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->text('message');
            $table->string('reason', 160);
            $table->string('status', 30)->default('new');
            $table->foreignId('handled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
            $table->unique(['telegram_chat_id', 'telegram_message_id'], 'operator_request_message_dedup');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_requests');
    }
};
