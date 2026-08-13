<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'draft',
        'waiting_documents',
        'received',
        'priced',
        'awaiting_payment',
        'partially_paid',
        'in_processing',
        'waiting_review',
        'qa_failed',
        'ready_for_delivery',
        'courier_sent',
        'delivered',
        'completed',
        'cancelled',
        'refunded',
    ];

    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            // Legacy enum only allowed process/finish. A string keeps the
            // existing column name and lets old integrations continue to
            // write their values while the application migrates them.
            $table->string('status_doc', 40)->default('received')->change();
            $table->foreignId('assigned_to_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('qa_user_id')->nullable()->after('assigned_to_id')->constrained('users')->nullOnDelete();
            $table->string('priority', 20)->default('normal')->after('status_doc');
            $table->string('queue', 40)->default('general')->after('priority');
            $table->unsignedInteger('estimated_workload_minutes')->default(0)->after('queue');
            $table->unsignedInteger('rework_count')->default(0)->after('estimated_workload_minutes');
            $table->string('assignment_source', 30)->default('manual')->after('rework_count');
            $table->timestamp('last_status_changed_at')->nullable()->after('assignment_source');
            $table->index(['filial_id', 'status_doc', 'priority']);
            $table->index(['assigned_to_id', 'status_doc']);
            $table->index(['qa_user_id', 'status_doc']);
            $table->index(['queue', 'status_doc']);
        });

        Schema::create('document_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'created_at']);
            $table->index(['to_status', 'created_at']);
        });

        Schema::create('document_assignment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('qa_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('queue', 40)->default('general');
            $table->string('priority', 20)->default('normal');
            $table->unsignedInteger('estimated_workload_minutes')->default(0);
            $table->string('source', 30)->default('manual');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'created_at']);
            $table->index(['assigned_to_id', 'created_at']);
            $table->index(['qa_user_id', 'created_at']);
        });

        $this->backfillLegacyWorkflow();
    }

    private function backfillLegacyWorkflow(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')->orderBy('id')->get()->each(function (object $document): void {
            $status = match ((string) ($document->status_doc ?? '')) {
                'process' => 'in_processing',
                'finish' => ((float) ($document->final_price ?? 0) > (float) ($document->paid_amount ?? 0))
                    ? 'ready_for_delivery'
                    : 'completed',
                default => in_array((string) ($document->status_doc ?? ''), self::STATUSES, true)
                    ? (string) $document->status_doc
                    : 'received',
            };
            $changedAt = $document->updated_at ?? now();

            DB::table('documents')->where('id', $document->id)->update([
                'status_doc' => $status,
                'assigned_to_id' => $document->user_id,
                'priority' => 'normal',
                'queue' => $status === 'completed' ? 'completed' : 'general',
                'assignment_source' => 'legacy_backfill',
                'last_status_changed_at' => $changedAt,
            ]);

            DB::table('document_status_histories')->insert([
                'document_id' => $document->id,
                'from_status' => null,
                'to_status' => $status,
                'changed_by_id' => $document->user_id,
                'reason' => 'Legacy status backfill',
                'comment' => 'Old process/finish status yangi workflow statusiga o‘tkazildi.',
                'metadata' => json_encode(['legacy_status' => $document->status_doc]),
                'created_at' => $changedAt,
                'updated_at' => $changedAt,
            ]);

            DB::table('document_assignment_histories')->insert([
                'document_id' => $document->id,
                'assigned_to_id' => $document->user_id,
                'qa_user_id' => null,
                'assigned_by_id' => $document->user_id,
                'queue' => $status === 'completed' ? 'completed' : 'general',
                'priority' => 'normal',
                'estimated_workload_minutes' => 0,
                'source' => 'legacy_backfill',
                'notes' => 'Legacy responsible user avtomatik mas’ul qilib belgilandi.',
                'metadata' => json_encode([]),
                'created_at' => $changedAt,
                'updated_at' => $changedAt,
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('documents')) {
            DB::table('documents')->orderBy('id')->get()->each(function (object $document): void {
                $legacyStatus = in_array((string) $document->status_doc, ['completed', 'delivered'], true)
                    ? 'finish'
                    : 'process';
                DB::table('documents')->where('id', $document->id)->update(['status_doc' => $legacyStatus]);
            });
        }

        Schema::dropIfExists('document_assignment_histories');
        Schema::dropIfExists('document_status_histories');

        Schema::table('documents', function (Blueprint $table): void {
            $table->dropForeign(['assigned_to_id']);
            $table->dropForeign(['qa_user_id']);
            $table->dropIndex(['filial_id', 'status_doc', 'priority']);
            $table->dropIndex(['assigned_to_id', 'status_doc']);
            $table->dropIndex(['qa_user_id', 'status_doc']);
            $table->dropIndex(['queue', 'status_doc']);
            $table->dropColumn([
                'assigned_to_id',
                'qa_user_id',
                'priority',
                'queue',
                'estimated_workload_minutes',
                'rework_count',
                'assignment_source',
                'last_status_changed_at',
            ]);
            $table->enum('status_doc', ['process', 'finish'])->default('process')->change();
        });
    }
};
