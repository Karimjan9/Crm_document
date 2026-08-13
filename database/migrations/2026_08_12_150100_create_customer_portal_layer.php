<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('email', 180)->nullable()->after('phone_number');
            $table->index('email');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('repeat_of_order_id')->nullable()->after('created_by_id')->constrained('orders')->nullOnDelete();
            $table->index(['repeat_of_order_id', 'created_at']);
        });

        Schema::create('order_payment_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('token', 96)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UZS');
            $table->string('provider', 40)->default('manual');
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('order_support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->string('contact', 180)->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_support_tickets');
        Schema::dropIfExists('order_payment_links');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['repeat_of_order_id']);
            $table->dropIndex(['repeat_of_order_id', 'created_at']);
            $table->dropColumn('repeat_of_order_id');
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });
    }
};
