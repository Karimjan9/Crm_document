<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('name', 160);
            $table->string('phone', 40)->nullable();
            $table->string('source', 80)->nullable();
            $table->string('campaign', 120)->nullable();
            $table->string('interested_service', 180)->nullable();
            $table->string('status', 30)->default('new');
            $table->decimal('estimated_amount', 14, 2)->default(0);
            $table->decimal('quoted_amount', 14, 2)->nullable();
            $table->string('lost_reason', 255)->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['filial_id', 'status']);
            $table->index(['assigned_to_id', 'next_follow_up_at']);
            $table->index(['source', 'created_at']);
        });

        Schema::create('lead_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->default('note');
            $table->text('body');
            $table->timestamp('happened_at');
            $table->timestamps();
            $table->index(['lead_id', 'happened_at']);
        });

        Schema::create('workflow_responsibility_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->string('trigger_status', 50);
            $table->string('responsible_role', 50);
            $table->string('action_type', 50)->default('workflow');
            $table->string('title', 255);
            $table->unsignedInteger('due_hours')->default(24);
            $table->string('escalate_to_role', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['filial_id', 'trigger_status', 'responsible_role'], 'workflow_rule_unique');
        });

        Schema::create('work_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->cascadeOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_role', 50)->nullable();
            $table->string('type', 50)->default('manual');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['assigned_to_id', 'status', 'due_at']);
            $table->index(['filial_id', 'status', 'due_at']);
        });

        Schema::create('business_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50);
            $table->string('status', 30)->default('pending');
            $table->decimal('amount', 14, 2)->nullable();
            $table->decimal('projected_margin_percent', 8, 2)->nullable();
            $table->string('required_role', 50)->default('admin_manager');
            $table->text('reason');
            $table->text('resolution_note')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'type', 'filial_id']);
        });

        Schema::create('margin_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->decimal('minimum_margin_percent', 8, 2)->default(20);
            $table->decimal('refund_auto_approve_limit', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['filial_id', 'service_id'], 'margin_policy_scope_unique');
        });

        Schema::create('performance_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filial')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('revenue_target', 14, 2)->default(0);
            $table->unsignedInteger('order_target')->default(0);
            $table->decimal('bonus_rate', 8, 2)->default(0);
            $table->json('weights')->nullable();
            $table->timestamps();
            $table->index(['filial_id', 'period_start', 'period_end']);
            $table->index(['user_id', 'period_start', 'period_end']);
        });

        Schema::create('customer_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->unsignedTinyInteger('nps_score')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique('order_id');
        });

        Schema::table('partners', function (Blueprint $table): void {
            $table->foreignId('account_manager_id')->nullable()->after('contact_name')->constrained('users')->nullOnDelete();
            $table->date('contract_starts_at')->nullable()->after('payment_terms_days');
            $table->date('contract_ends_at')->nullable()->after('contract_starts_at');
            $table->decimal('minimum_margin_percent', 8, 2)->nullable()->after('credit_limit');
        });

        Schema::table('order_deliveries', function (Blueprint $table): void {
            $table->string('delivery_otp_hash')->nullable()->after('tracking_code');
            $table->string('received_by', 160)->nullable()->after('recipient_phone');
            $table->decimal('delivered_latitude', 10, 7)->nullable()->after('address');
            $table->decimal('delivered_longitude', 10, 7)->nullable()->after('delivered_latitude');
            $table->string('proof_path')->nullable()->after('notes');
            $table->string('proof_original_name')->nullable()->after('proof_path');
            $table->timestamp('received_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table): void {
            $table->dropColumn(['delivery_otp_hash', 'received_by', 'delivered_latitude', 'delivered_longitude', 'proof_path', 'proof_original_name', 'received_at']);
        });
        Schema::table('partners', function (Blueprint $table): void {
            $table->dropForeign(['account_manager_id']);
            $table->dropColumn(['account_manager_id', 'contract_starts_at', 'contract_ends_at', 'minimum_margin_percent']);
        });
        Schema::dropIfExists('customer_feedback');
        Schema::dropIfExists('performance_targets');
        Schema::dropIfExists('margin_policies');
        Schema::dropIfExists('business_approvals');
        Schema::dropIfExists('work_items');
        Schema::dropIfExists('workflow_responsibility_rules');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
