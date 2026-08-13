<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_ITEMS = [
        ['code' => 'passport', 'title' => 'Passport', 'requires_file' => true],
        ['code' => 'original_document', 'title' => 'Original document', 'requires_file' => true],
        ['code' => 'notarized_copy', 'title' => 'Notarized copy', 'requires_file' => true],
        ['code' => 'photo', 'title' => 'Photo', 'requires_file' => true],
        ['code' => 'application_form', 'title' => 'Application form', 'requires_file' => false],
        ['code' => 'translation', 'title' => 'Translation', 'requires_file' => true],
        ['code' => 'apostille_requirement', 'title' => 'Apostille requirement', 'requires_file' => false],
    ];

    public function up(): void
    {
        Schema::create('service_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_file')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'code']);
            $table->index(['service_id', 'is_active', 'sort_order']);
        });

        Schema::create('document_checklists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('service_checklist_item_id')->nullable()->constrained('service_checklist_items')->nullOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_file')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'code']);
            $table->index(['document_id', 'is_required', 'is_completed']);
        });

        Schema::create('document_qa_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('result', 20)->default('pending');
            $table->string('reason')->nullable();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'created_at']);
            $table->index(['document_id', 'result']);
        });

        $this->seedServiceRequirements();
        $this->backfillDocumentChecklists();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_qa_reviews');
        Schema::dropIfExists('document_checklists');
        Schema::dropIfExists('service_checklist_items');
    }

    private function seedServiceRequirements(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        $now = now();
        foreach (DB::table('services')->pluck('id') as $serviceId) {
            foreach (self::DEFAULT_ITEMS as $sort => $item) {
                $exists = DB::table('service_checklist_items')
                    ->where('service_id', $serviceId)
                    ->where('code', $item['code'])
                    ->exists();

                if (! $exists) {
                    DB::table('service_checklist_items')->insert([
                        'service_id' => $serviceId,
                        'code' => $item['code'],
                        'title' => $item['title'],
                        'is_required' => true,
                        'requires_file' => $item['requires_file'],
                        'is_active' => true,
                        'sort_order' => $sort + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function backfillDocumentChecklists(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        $itemsByService = DB::table('service_checklist_items')
            ->orderBy('service_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('service_id');

        foreach (DB::table('documents')->whereNotNull('service_id')->get() as $document) {
            foreach ($itemsByService->get($document->service_id, collect()) as $item) {
                $legacyAlreadyInProcess = in_array((string) $document->status_doc, [
                    'in_processing', 'process', 'waiting_review', 'qa_failed',
                    'ready_for_delivery', 'courier_sent', 'delivered', 'completed', 'finish',
                ], true);

                DB::table('document_checklists')->updateOrInsert(
                    ['document_id' => $document->id, 'code' => $item->code],
                    [
                        'service_id' => $document->service_id,
                        'service_checklist_item_id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'is_required' => (bool) $item->is_required,
                        'requires_file' => (bool) $item->requires_file,
                        'is_completed' => $legacyAlreadyInProcess,
                        'completed_by_id' => $legacyAlreadyInProcess ? $document->user_id : null,
                        'completed_at' => $legacyAlreadyInProcess ? ($document->updated_at ?: now()) : null,
                        'sort_order' => $item->sort_order,
                        'created_at' => $document->created_at ?: now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }
};
