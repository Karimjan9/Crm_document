<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_intake_requests', function (Blueprint $table): void {
            $table->id(); $table->uuid('external_id')->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->json('payload'); $table->timestamps();
        });
        Schema::create('telegram_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('telegram_chat_id', 80); $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->uuid('event_id')->nullable()->unique(); $table->string('direction', 10); $table->string('type', 30)->default('text');
            $table->text('body')->nullable(); $table->string('attachment_path')->nullable(); $table->json('attachment_meta')->nullable();
            $table->foreignId('sent_by_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('sent_at')->nullable(); $table->timestamps();
            $table->unique(['telegram_chat_id', 'telegram_message_id', 'direction'], 'telegram_message_dedup');
            $table->index(['client_id', 'created_at']); $table->index(['lead_id', 'created_at']);
        });
        Schema::create('bot_contents', function (Blueprint $table): void {
            $table->id(); $table->string('key', 120)->unique(); $table->text('text'); $table->json('metadata')->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('bot_marketing_consents', function (Blueprint $table): void {
            $table->id(); $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); $table->string('telegram_chat_id', 80)->unique();
            $table->boolean('consent')->default(false); $table->timestamp('consented_at')->nullable(); $table->timestamp('revoked_at')->nullable(); $table->timestamps();
        });
        Schema::create('bot_webhook_events', function (Blueprint $table): void {
            $table->id(); $table->uuid('event_id')->unique(); $table->string('type', 80); $table->string('telegram_chat_id', 80)->nullable();
            $table->json('payload'); $table->string('status', 20)->default('queued'); $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('dispatched_at')->nullable(); $table->text('last_error')->nullable(); $table->timestamps(); $table->index(['status', 'created_at']);
        });
        Schema::create('bot_delivery_reports', function (Blueprint $table): void {
            $table->id(); $table->uuid('event_id')->unique(); $table->string('telegram_chat_id', 80); $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->string('status', 20); $table->timestamp('reported_at'); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bot_delivery_reports'); Schema::dropIfExists('bot_webhook_events'); Schema::dropIfExists('bot_marketing_consents');
        Schema::dropIfExists('bot_contents'); Schema::dropIfExists('telegram_messages'); Schema::dropIfExists('bot_intake_requests');
    }
};
