<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('token', 96)->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->json('answers')->nullable();
            $table->foreignId('recommended_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->unsignedInteger('estimated_deadline_days')->nullable();
            $table->json('required_files')->nullable();
            $table->json('recommended_addons')->nullable();
            $table->string('status', 20)->default('started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });

        Schema::create('document_custody_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 30);
            $table->timestamp('event_at');
            $table->string('signature', 128)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'event_at']);
            $table->index(['event_type', 'event_at']);
        });

        Schema::create('margin_leaks', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint', 160)->unique();
            $table->string('leak_type', 50);
            $table->string('severity', 20)->default('medium');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('message', 500);
            $table->json('metadata')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['severity', 'resolved_at']);
            $table->index(['filial_id', 'detected_at']);
            $table->index(['order_id', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margin_leaks');
        Schema::dropIfExists('document_custody_events');
        Schema::dropIfExists('intake_sessions');
    }
};
