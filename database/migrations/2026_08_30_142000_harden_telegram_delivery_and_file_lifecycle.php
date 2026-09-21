<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table): void {
            $table->string('delivery_status', 20)->default('received')->after('sent_at');
            $table->string('delivery_error', 120)->nullable()->after('delivery_status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_error');
            $table->timestamp('file_expires_at')->nullable()->after('attachment_path');
            $table->index(['delivery_status', 'created_at']); $table->index('file_expires_at');
        });
        Schema::table('bot_webhook_events', function (Blueprint $table): void {
            $table->foreignId('telegram_message_id')->nullable()->after('telegram_chat_id')->constrained('telegram_messages')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('bot_webhook_events', function (Blueprint $table): void { $table->dropForeign(['telegram_message_id']); $table->dropColumn('telegram_message_id'); });
        Schema::table('telegram_messages', function (Blueprint $table): void { $table->dropIndex(['delivery_status', 'created_at']); $table->dropIndex(['file_expires_at']); $table->dropColumn(['delivery_status', 'delivery_error', 'delivered_at', 'file_expires_at']); });
    }
};
