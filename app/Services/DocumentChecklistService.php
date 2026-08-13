<?php

namespace App\Services;

use App\Models\DocumentChecklist;
use App\Models\DocumentsModel;
use App\Models\DocumentQaReview;
use App\Models\ServiceChecklistItem;
use App\Models\ServicesModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentChecklistService
{
    public const DEFAULT_ITEMS = [
        'passport' => ['title' => 'Passport', 'requires_file' => true],
        'original_document' => ['title' => 'Original document', 'requires_file' => true],
        'notarized_copy' => ['title' => 'Notarized copy', 'requires_file' => true],
        'photo' => ['title' => 'Photo', 'requires_file' => true],
        'application_form' => ['title' => 'Application form', 'requires_file' => false],
        'translation' => ['title' => 'Translation', 'requires_file' => true],
        'apostille_requirement' => ['title' => 'Apostille requirement', 'requires_file' => false],
    ];

    public function syncForService(DocumentsModel $document): DocumentsModel
    {
        return DB::transaction(function () use ($document): DocumentsModel {
            $existing = $document->checklists()->get();
            // A document owns a snapshot of its service requirements. Later
            // service configuration changes apply to new documents only.
            if ($existing->isNotEmpty()) {
                $serviceIds = $existing->pluck('service_id')->filter()->unique();
                if ($serviceIds->isNotEmpty() && $serviceIds->contains(fn ($serviceId): bool => (int) $serviceId !== (int) $document->service_id)) {
                    $document->checklists()->delete();
                    $existing = collect();
                } else {
                    return $document->fresh(['checklists', 'latestQaReview']);
                }
            }

            $items = ServiceChecklistItem::query()
                ->where('service_id', $document->service_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($items as $item) {
                $document->checklists()->create([
                    'service_id' => $document->service_id,
                    'service_checklist_item_id' => $item->id,
                    'code' => $item->code,
                    'title' => $item->title,
                    'description' => $item->description,
                    'is_required' => $item->is_required,
                    'requires_file' => $item->requires_file,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $document->fresh(['checklists', 'latestQaReview']);
        });
    }

    public function syncServiceRequirements(ServicesModel $service, ?array $selectedCodes = null): void
    {
        $selectedCodes ??= array_keys(self::DEFAULT_ITEMS);
        $selectedCodes = array_values(array_unique(array_filter(
            array_map('strval', $selectedCodes),
            fn (string $code): bool => array_key_exists($code, self::DEFAULT_ITEMS),
        )));

        foreach (self::DEFAULT_ITEMS as $code => $default) {
            $enabled = in_array($code, $selectedCodes, true);
            $position = array_search($code, $selectedCodes, true);
            ServiceChecklistItem::query()->updateOrCreate(
                ['service_id' => $service->id, 'code' => $code],
                [
                    'title' => $default['title'],
                    'is_required' => $enabled,
                    'requires_file' => $default['requires_file'],
                    'is_active' => $enabled,
                    'sort_order' => $position === false ? 999 : $position + 1,
                ],
            );
        }
    }

    public function progress(DocumentsModel $document): array
    {
        $items = $document->relationLoaded('checklists')
            ? $document->checklists
            : $document->checklists()->get();
        $required = $items->where('is_required', true)->values();
        $completed = $required->where('is_completed', true)->count();

        return [
            'total' => $required->count(),
            'completed' => $completed,
            'remaining' => max($required->count() - $completed, 0),
            'complete' => $completed === $required->count(),
            'items' => $items->values(),
        ];
    }

    public function isComplete(DocumentsModel $document): bool
    {
        return (bool) $this->progress($document)['complete'];
    }

    public function qaStatus(DocumentsModel $document): string
    {
        $progress = $this->progress($document);
        if ($progress['total'] === 0) {
            return 'not_required';
        }

        $review = $document->qaReviews()->latest('id')->first();

        return $review?->result ?: 'not_started';
    }

    public function toggle(DocumentChecklist $item, bool $completed, ?User $actor = null, ?string $notes = null): DocumentChecklist
    {
        return DB::transaction(function () use ($item, $completed, $actor, $notes): DocumentChecklist {
            $item = DocumentChecklist::query()->lockForUpdate()->findOrFail($item->id);
            $document = DocumentsModel::query()->lockForUpdate()->findOrFail($item->document_id);

            if ($completed && $item->requires_file && ! $document->files()->exists()) {
                throw ValidationException::withMessages([
                    'is_completed' => "{$item->title} uchun avval kamida bitta fayl yuklang.",
                ]);
            }

            $item->forceFill([
                'is_completed' => $completed,
                'completed_by_id' => $completed ? $actor?->id : null,
                'completed_at' => $completed ? now() : null,
                'notes' => $notes ?? $item->notes,
            ])->save();

            if (! $completed && $this->qaStatus($document) === 'passed') {
                $document->qaReviews()->create([
                    'reviewer_id' => $actor?->id,
                    'result' => 'pending',
                    'reason' => 'Checklist qayta ochildi.',
                    'metadata' => ['checklist_id' => $item->id],
                ]);
            }

            return $item->fresh(['completedBy']);
        });
    }

    public function review(
        DocumentsModel $document,
        string $result,
        ?User $reviewer = null,
        ?string $reason = null,
        ?string $comment = null,
    ): DocumentQaReview {
        if (! in_array($result, ['passed', 'failed'], true)) {
            throw ValidationException::withMessages(['result' => 'QA natijasi noto‘g‘ri.']);
        }

        $document = $this->syncForService($document);
        if ($result === 'passed' && ! $this->isComplete($document)) {
            throw ValidationException::withMessages([
                'result' => 'QA tasdig‘idan oldin barcha majburiy checklist bandlarini tugating.',
            ]);
        }

        return DB::transaction(function () use ($document, $result, $reviewer, $reason, $comment): DocumentQaReview {
            $review = $document->qaReviews()->create([
                'reviewer_id' => $reviewer?->id,
                'result' => $result,
                'reason' => $reason,
                'comment' => $comment,
                'metadata' => ['checklist_complete' => $this->isComplete($document)],
            ]);

            if ($result === 'failed') {
                $workflow = app(DocumentWorkflowService::class);
                $current = $workflow->normalizeStatus($document->status_doc);
                if ($current !== 'qa_failed' && in_array('qa_failed', $workflow->allowedTransitions($current), true)) {
                    $workflow->transition(
                        $document,
                        'qa_failed',
                        $reviewer,
                        $reason ?: 'QA tekshiruvi muvaffaqiyatsiz.',
                        $comment,
                        ['qa_review_id' => $review->id],
                    );
                }
            }

            return $review->fresh(['reviewer']);
        });
    }

    public function assertReady(DocumentsModel $document): void
    {
        $document = $this->syncForService($document);
        $progress = $this->progress($document);
        if (! $progress['complete']) {
            throw ValidationException::withMessages([
                'checklist' => "Majburiy checklist tugallanmagan: {$progress['completed']}/{$progress['total']}.",
            ]);
        }

        if ($progress['total'] === 0) {
            return;
        }

        if (! $document->qa_user_id) {
            throw ValidationException::withMessages([
                'qa_user_id' => 'Ready statusidan oldin QA xodimini biriktiring.',
            ]);
        }

        if ($this->qaStatus($document) !== 'passed') {
            throw ValidationException::withMessages([
                'qa' => 'Ready statusidan oldin QA tasdig‘ini oling.',
            ]);
        }
    }

    public function assertChecklistComplete(DocumentsModel $document): void
    {
        $document = $this->syncForService($document);
        $progress = $this->progress($document);
        if (! $progress['complete']) {
            throw ValidationException::withMessages([
                'checklist' => "Majburiy checklist tugallanmagan: {$progress['completed']}/{$progress['total']}.",
            ]);
        }
    }

    public function checklistItemsForService(ServicesModel $service): array
    {
        $items = $service->checklistItems()->get()->keyBy('code');

        return collect(self::DEFAULT_ITEMS)->map(function (array $default, string $code) use ($items): array {
            $item = $items->get($code);
            return [
                'code' => $code,
                'title' => $item?->title ?: $default['title'],
                'requires_file' => $item?->requires_file ?? $default['requires_file'],
                'is_required' => (bool) ($item?->is_required ?? false),
                'is_active' => (bool) ($item?->is_active ?? false),
            ];
        })->values()->all();
    }
}
