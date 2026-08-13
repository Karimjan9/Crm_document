<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('telegram_chat_id', 80)->nullable()->after('email');
            $table->string('whatsapp_phone', 40)->nullable()->after('telegram_chat_id');
            $table->index('telegram_chat_id');
            $table->index('whatsapp_phone');
        });

        Schema::table('order_notifications', function (Blueprint $table): void {
            $table->unsignedTinyInteger('attempts')->default(0)->after('status');
            $table->timestamp('last_attempt_at')->nullable()->after('sent_at');
            $table->string('provider_message_id', 180)->nullable()->after('error_message');
        });

        Schema::table('document_custody_events', function (Blueprint $table): void {
            $table->foreignId('signed_by_id')->nullable()->after('to_user_id')->constrained('users')->nullOnDelete();
            $table->string('previous_signature', 128)->nullable()->after('signature');
            $table->index(['document_id', 'signature']);
        });

        Schema::create('cash_reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->constrained('filial')->cascadeOnDelete();
            $table->date('reconciliation_date');
            $table->decimal('expected_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            $table->string('status', 20)->default('open');
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['filial_id', 'reconciliation_date']);
            $table->index(['status', 'reconciliation_date']);
        });

        Schema::create('intake_ocr_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('intake_session_id')->constrained('intake_sessions')->cascadeOnDelete();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('status', 30)->default('pending_review');
            $table->string('provider', 60)->default('manual');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->json('extracted_data')->nullable();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['intake_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_ocr_documents');
        Schema::dropIfExists('cash_reconciliations');

        Schema::table('document_custody_events', function (Blueprint $table): void {
            $table->dropForeign(['signed_by_id']);
            $table->dropIndex(['document_id', 'signature']);
            $table->dropColumn(['signed_by_id', 'previous_signature']);
        });

        Schema::table('order_notifications', function (Blueprint $table): void {
            $table->dropColumn(['attempts', 'last_attempt_at', 'provider_message_id']);
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['telegram_chat_id']);
            $table->dropIndex(['whatsapp_phone']);
            $table->dropColumn(['telegram_chat_id', 'whatsapp_phone']);
        });
    }
};
