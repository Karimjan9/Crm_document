<?php

namespace App\Services;

use App\Models\DocumentsModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentWorkflowService
{
    public const TERMINAL_STATUSES = ['completed', 'cancelled', 'refunded'];

    private const QUEUE_BY_STATUS = [
        'draft' => 'intake', 'waiting_documents' => 'intake', 'received' => 'intake',
        'priced' => 'pricing', 'awaiting_payment' => 'pricing', 'partially_paid' => 'pricing',
        'in_processing' => 'processing', 'waiting_review' => 'review', 'qa_failed' => 'correction',
        'ready_for_delivery' => 'delivery', 'courier_sent' => 'delivery', 'delivered' => 'delivery',
        'completed' => 'completed', 'cancelled' => 'cancelled', 'refunded' => 'completed',
    ];

    private const TRANSITIONS = [
        'draft' => ['waiting_documents', 'received', 'priced', 'cancelled'],
        'waiting_documents' => ['received', 'priced', 'cancelled'],
        'received' => ['waiting_documents', 'priced', 'awaiting_payment', 'in_processing', 'cancelled'],
        'priced' => ['awaiting_payment', 'partially_paid', 'in_processing', 'cancelled'],
        'awaiting_payment' => ['partially_paid', 'in_processing', 'cancelled'],
        'partially_paid' => ['awaiting_payment', 'in_processing', 'cancelled'],
        'in_processing' => ['waiting_review', 'qa_failed', 'ready_for_delivery', 'courier_sent', 'completed', 'cancelled'],
        'waiting_review' => ['qa_failed', 'in_processing', 'ready_for_delivery', 'completed', 'cancelled'],
        'qa_failed' => ['in_processing', 'waiting_review', 'cancelled'],
        'ready_for_delivery' => ['courier_sent', 'delivered', 'completed', 'cancelled'],
        'courier_sent' => ['ready_for_delivery', 'delivered', 'cancelled'],
        'delivered' => ['completed', 'refunded'], 'completed' => ['refunded'],
        'cancelled' => ['refunded'], 'refunded' => [],
    ];

    public function normalizeStatus(?string $status): string
    {
        return match ((string) $status) {
            'process' => 'in_processing', 'finish' => 'completed',
            default => in_array((string) $status, DocumentsModel::STATUSES, true) ? (string) $status : 'received',
        };
    }

    public function label(?string $status): string
    {
        $status = $this->normalizeStatus($status);
        return DocumentsModel::STATUS_LABELS[$status] ?? $status;
    }

    public function allowedTransitions(?string $status): array
    {
        return self::TRANSITIONS[$this->normalizeStatus($status)] ?? [];
    }

    public function isCompleted(?string $status): bool
    {
        return in_array($this->normalizeStatus($status), ['ready_for_delivery', 'courier_sent', 'delivered', 'completed'], true);
    }

    public function queueForStatus(?string $status): string
    {
        return self::QUEUE_BY_STATUS[$this->normalizeStatus($status)] ?? 'general';
    }

    public function initialize(DocumentsModel $document, ?User $actor = null): DocumentsModel
    {
        $status = $this->normalizeStatus($document->status_doc);
        if (! $document->statusHistories()->exists()) {
            $document->forceFill([
                'status_doc' => $status,
                'queue' => $this->queueForStatus($status),
                'last_status_changed_at' => $document->updated_at ?: now(),
            ])->save();
            $document->statusHistories()->create([
                'from_status' => null, 'to_status' => $status, 'changed_by_id' => $actor?->id,
                'reason' => 'Hujjat workflow’ga qo‘shildi.', 'metadata' => ['source' => 'initialization'],
            ]);
        }
        return $document->fresh(['assignedTo', 'qaUser']);
    }

    public function transition(
        DocumentsModel $document,
        string $status,
        ?User $actor = null,
        ?string $reason = null,
        ?string $comment = null,
        array $metadata = [],
        bool $force = false,
    ): DocumentsModel {
        $target = $this->normalizeStatus($status);
        if (! in_array($target, DocumentsModel::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Hujjat statusi noto‘g‘ri.']);
        }
        if (in_array($target, ['in_processing', 'ready_for_delivery', 'completed'], true)) {
            app(DocumentChecklistService::class)->syncForService($document);
        }

        return DB::transaction(function () use ($document, $target, $actor, $reason, $comment, $metadata, $force): DocumentsModel {
            $locked = DocumentsModel::query()->lockForUpdate()->findOrFail($document->id);
            $from = $this->normalizeStatus($locked->status_doc);
            if ($from === $target) return $locked->fresh(['assignedTo', 'qaUser']);

            $forced = $force && $actor?->hasAnyRole(['super_admin', 'admin_manager']);
            if (! $forced && ! in_array($target, $this->allowedTransitions($from), true)) {
                throw ValidationException::withMessages([
                    'status' => "{$this->label($from)} holatidan {$this->label($target)} holatiga o‘tish mumkin emas.",
                ]);
            }
            if ($target === 'cancelled' && ! $actor?->hasAnyRole(['admin_filial', 'admin_manager', 'super_admin'])) {
                throw ValidationException::withMessages(['status' => 'Hujjatni bekor qilish uchun admin huquqi kerak.']);
            }
            if ($target === 'refunded' && ! $actor?->hasAnyRole(['admin_manager', 'super_admin'])) {
                throw ValidationException::withMessages(['status' => 'Qaytarish statusi faqat menejer yoki super admin uchun.']);
            }
            if ($target === 'ready_for_delivery' && (float) $locked->final_price > (float) $locked->paid_amount) {
                throw ValidationException::withMessages(['status' => 'Topshirishdan oldin to‘lovni to‘liq qabul qiling.']);
            }
            if ($target === 'completed' && (float) $locked->final_price > (float) $locked->paid_amount && ! $forced) {
                throw ValidationException::withMessages(['status' => 'Hujjatni yakunlashdan oldin to‘lovni to‘liq qabul qiling.']);
            }
            if ($target === 'courier_sent' && ! $locked->courierAssignment()->whereIn('status', ['sent', 'accepted'])->exists()) {
                throw ValidationException::withMessages(['status' => 'Courier assignment avval yaratilishi kerak.']);
            }
            if ($target === 'in_processing') {
                app(DocumentChecklistService::class)->assertChecklistComplete($locked);
            }
            if (in_array($target, ['ready_for_delivery', 'completed'], true)) {
                app(DocumentChecklistService::class)->assertReady($locked);
            }
            if ($target === 'qa_failed') $locked->increment('rework_count');

            $locked->forceFill([
                'status_doc' => $target,
                'queue' => $this->queueForStatus($target),
                'last_status_changed_at' => now(),
            ])->save();
            $locked->statusHistories()->create([
                'from_status' => $from, 'to_status' => $target, 'changed_by_id' => $actor?->id,
                'reason' => $reason, 'comment' => $comment,
                'metadata' => array_merge($metadata, ['forced' => $forced]),
            ]);
            return $locked->fresh(['assignedTo', 'qaUser']);
        });
    }

    public function syncDerivedStatus(DocumentsModel $document, ?User $actor = null): DocumentsModel
    {
        $document = $this->initialize($document, $actor);
        $document = app(DocumentChecklistService::class)->syncForService($document);
        $current = $this->normalizeStatus($document->status_doc);
        if (in_array($current, ['waiting_review', 'qa_failed', 'ready_for_delivery', 'courier_sent', 'delivered', 'completed', 'cancelled', 'refunded'], true)) return $document;

        $hasFiles = $document->files()->exists();
        $final = (float) $document->final_price;
        $paid = (float) $document->paid_amount;
        $target = ! $hasFiles ? 'waiting_documents' : ($final > 0 && $paid <= 0 ? 'awaiting_payment' : ($final > 0 && $paid < $final ? 'partially_paid' : 'in_processing'));
        if ($target === 'in_processing' && ! app(DocumentChecklistService::class)->isComplete($document)) {
            if (in_array($current, ['draft', 'received'], true)) {
                $target = 'waiting_documents';
            } else {
                return $document;
            }
        }
        return $current === $target ? $document : $this->transition($document, $target, $actor, 'Hujjat va to‘lov ma’lumotlari asosida avtomatik yangilandi.');
    }

    public function assign(DocumentsModel $document, ?int $assignedToId, ?int $qaUserId, ?string $queue, ?string $priority, ?int $workloadMinutes, ?User $actor = null, string $source = 'manual', ?string $notes = null): DocumentsModel
    {
        return DB::transaction(function () use ($document, $assignedToId, $qaUserId, $queue, $priority, $workloadMinutes, $actor, $source, $notes): DocumentsModel {
            $locked = DocumentsModel::query()->lockForUpdate()->findOrFail($document->id);
            $this->assertUserInBranch($locked, $assignedToId, 'assigned_to_id');
            $this->assertUserInBranch($locked, $qaUserId, 'qa_user_id');
            $priority = $priority ?: ($locked->priority ?: 'normal');
            $queue = $queue ?: ($locked->queue ?: $this->queueForStatus($locked->status_doc));
            if (! in_array($priority, DocumentsModel::PRIORITIES, true)) throw ValidationException::withMessages(['priority' => 'Priority noto‘g‘ri.']);
            if (! in_array($queue, DocumentsModel::QUEUES, true)) throw ValidationException::withMessages(['queue' => 'Queue noto‘g‘ri.']);
            $minutes = $workloadMinutes ?? max((int) $locked->estimated_workload_minutes, (int) $locked->deadline_time * 60);
            if ($minutes < 0 || $minutes > 100000) throw ValidationException::withMessages(['estimated_workload_minutes' => 'Workload 0 dan 100000 daqiqagacha bo‘lishi kerak.']);

            $previousAssignee = $locked->assigned_to_id;
            $locked->forceFill([
                'assigned_to_id' => $assignedToId, 'qa_user_id' => $qaUserId, 'queue' => $queue,
                'priority' => $priority, 'estimated_workload_minutes' => $minutes, 'assignment_source' => $source,
            ])->save();
            if ($locked->order_id && $assignedToId !== null) {
                DB::table('orders')->where('id', $locked->order_id)->update([
                    'responsible_user_id' => $assignedToId,
                    'updated_at' => now(),
                ]);
            }
            $locked->assignmentHistories()->create([
                'assigned_to_id' => $assignedToId, 'qa_user_id' => $qaUserId, 'assigned_by_id' => $actor?->id,
                'queue' => $queue, 'priority' => $priority, 'estimated_workload_minutes' => $minutes,
                'source' => $source, 'notes' => $notes, 'metadata' => ['previous_assigned_to_id' => $previousAssignee],
            ]);
            return $locked->fresh(['assignedTo', 'qaUser']);
        });
    }

    public function autoAssign(DocumentsModel $document, ?User $actor = null): DocumentsModel
    {
        $worker = $this->workersQuery($document->filial_id)->get()->sortBy(fn (User $user): array => [
            $this->workloadMinutes($user->id, $document->filial_id), $this->activeCount($user->id, $document->filial_id), $user->id,
        ])->first();
        if (! $worker) {
            return $this->assign(
                $document,
                null,
                $document->qa_user_id,
                $this->queueForStatus($document->status_doc),
                $document->priority ?: 'normal',
                (int) ($document->estimated_workload_minutes ?: max((int) $document->deadline_time * 60, 0)),
                $actor,
                'pending',
                'Filialda mos xodim topilmadi; menejer biriktirishi kutilmoqda.',
            );
        }
        return $this->assign($document, $worker->id, $document->qa_user_id, $this->queueForStatus($document->status_doc), $document->priority ?: 'normal', (int) ($document->estimated_workload_minutes ?: max((int) $document->deadline_time * 60, 0)), $actor, 'auto', 'Yuklama balansiga ko‘ra avtomatik taqsimlandi.');
    }

    public function workersQuery(?int $filialId = null): Builder
    {
        return User::query()->when($filialId, fn (Builder $query) => $query->where('filial_id', $filialId))
            ->whereHas('roles', fn (Builder $roles) => $roles->whereIn('name', ['employee', 'admin_filial', 'admin_manager'])->where('guard_name', 'web'))->orderBy('name');
    }

    public function visibleDocuments(User $actor, array $filters = []): Builder
    {
        $query = DocumentsModel::query()->with(['client:id,name,phone_number', 'service:id,name', 'assignedTo:id,name,filial_id', 'qaUser:id,name,filial_id', 'checklists', 'latestQaReview']);
        if ($actor->hasAnyRole(['super_admin', 'admin_manager'])) {
            $query->when(! empty($filters['filial_id']), fn (Builder $q) => $q->where('filial_id', (int) $filters['filial_id']));
        } elseif ($actor->hasRole('admin_filial')) {
            $query->where('filial_id', $actor->filial_id);
        } elseif ($actor->hasRole('employee')) {
            $query->where('filial_id', $actor->filial_id)->where(fn (Builder $q) => $q->where('user_id', $actor->id)->orWhere('assigned_to_id', $actor->id)->orWhere('qa_user_id', $actor->id));
        } else $query->whereKey(-1);
        return $query->when(! empty($filters['status']) && in_array($filters['status'], DocumentsModel::STATUSES, true), fn (Builder $q) => $q->where('status_doc', $filters['status']))
            ->when(! empty($filters['queue']) && in_array($filters['queue'], DocumentsModel::QUEUES, true), fn (Builder $q) => $q->where('queue', $filters['queue']))
            ->when(! empty($filters['priority']) && in_array($filters['priority'], DocumentsModel::PRIORITIES, true), fn (Builder $q) => $q->where('priority', $filters['priority']))
            ->when(! empty($filters['assigned_to_id']), fn (Builder $q) => $q->where('assigned_to_id', (int) $filters['assigned_to_id']))
            ->when(! empty($filters['q']), function (Builder $q) use ($filters): void { $s = trim((string) $filters['q']); $q->where(fn (Builder $inner) => $inner->where('document_code', 'like', "%{$s}%")->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$s}%"))->orWhereHas('service', fn (Builder $service) => $service->where('name', 'like', "%{$s}%"))); });
    }

    public function dashboard(User $actor, array $filters = []): array
    {
        $query = $this->visibleDocuments($actor, $filters);
        $documents = (clone $query)->orderByRaw("CASE priority WHEN 'urgent' THEN 4 WHEN 'high' THEN 3 WHEN 'normal' THEN 2 WHEN 'low' THEN 1 ELSE 0 END DESC")->orderByDesc('last_status_changed_at')->limit(300)->get();
        $columns = collect(DocumentsModel::STATUSES)->map(fn (string $status): array => [
            'key' => $status, 'label' => $this->label($status),
            'color' => match ($status) { 'qa_failed', 'cancelled', 'refunded' => 'danger', 'completed', 'delivered', 'courier_sent', 'ready_for_delivery' => 'success', 'waiting_review' => 'info', 'priced', 'awaiting_payment', 'partially_paid' => 'warning', 'in_processing' => 'primary', default => 'secondary' },
            'count' => $documents->filter(fn (DocumentsModel $document): bool => $this->normalizeStatus($document->status_doc) === $status)->count(),
            'documents' => $documents->filter(fn (DocumentsModel $document): bool => $this->normalizeStatus($document->status_doc) === $status)->map(fn (DocumentsModel $document): array => $this->card($document))->values()->all(),
        ])->values()->all();
        $base = $this->visibleDocuments($actor, $filters);
        $filialId = $filters['filial_id'] ?? ($actor->filial_id ?: null);
        $workers = $this->workersQuery($filialId)->get(['id', 'name', 'filial_id'])->map(fn (User $worker): array => [
            'id' => $worker->id, 'name' => $worker->name, 'filial_id' => $worker->filial_id,
            'active_count' => $this->activeCount($worker->id, $filialId), 'workload_minutes' => $this->workloadMinutes($worker->id, $filialId),
            'review_count' => DocumentsModel::query()->where('qa_user_id', $worker->id)->whereIn('status_doc', ['waiting_review', 'qa_failed'])->count(),
        ])->values()->all();
        return [
            'metrics' => ['total' => (clone $base)->count(), 'active' => (clone $base)->whereNotIn('status_doc', self::TERMINAL_STATUSES)->count(), 'urgent' => (clone $base)->whereIn('priority', ['urgent', 'high'])->whereNotIn('status_doc', self::TERMINAL_STATUSES)->count(), 'review' => (clone $base)->whereIn('status_doc', ['waiting_review', 'qa_failed'])->count(), 'workload_minutes' => (int) (clone $base)->whereNotIn('status_doc', self::TERMINAL_STATUSES)->sum('estimated_workload_minutes')],
            'columns' => $columns,
            'workers' => $workers,
            'statuses' => array_map(fn (string $status): array => ['key' => $status, 'label' => $this->label($status)], DocumentsModel::STATUSES),
            'priorities' => array_map(fn (string $priority): array => ['key' => $priority, 'label' => ucfirst($priority)], DocumentsModel::PRIORITIES),
            'queues' => array_map(fn (string $queue): array => ['key' => $queue, 'label' => ucfirst(str_replace('_', ' ', $queue))], DocumentsModel::QUEUES),
        ];
    }

    private function card(DocumentsModel $document): array
    {
        $status = $this->normalizeStatus($document->status_doc);
        return ['id' => $document->id, 'document_code' => $document->document_code, 'status' => $status, 'status_label' => $this->label($status), 'allowed_transitions' => array_map(fn (string $key): array => ['key' => $key, 'label' => $this->label($key)], array_merge([$status], $this->allowedTransitions($status))), 'priority' => $document->priority ?: 'normal', 'queue' => $document->queue ?: $this->queueForStatus($status), 'client' => $document->client?->name ?: 'Mijoz ko‘rsatilmagan', 'phone' => $document->client?->phone_number, 'service' => $document->service?->name ?: 'Xizmat', 'assigned_to_id' => $document->assigned_to_id, 'assigned_to' => $document->assignedTo?->name, 'qa_user_id' => $document->qa_user_id, 'qa_user' => $document->qaUser?->name, 'workload_minutes' => (int) $document->estimated_workload_minutes, 'rework_count' => (int) $document->rework_count, 'deadline' => $document->deadline_due_at?->format('d.m.Y H:i'), 'final_price' => (float) $document->final_price, 'paid_amount' => (float) $document->paid_amount, 'created_at' => $document->created_at?->format('d.m.Y H:i')];
    }

    private function assertUserInBranch(DocumentsModel $document, ?int $userId, string $field): void
    {
        if ($userId === null) return;
        $user = User::query()->find($userId);
        if (! $user || (int) $user->filial_id !== (int) $document->filial_id || ! $user->hasAnyRole(['employee', 'admin_filial', 'admin_manager'])) throw ValidationException::withMessages([$field => 'Xodim hujjat filialiga tegishli emas yoki workflow roli yo‘q.']);
    }

    private function workloadMinutes(int $userId, ?int $filialId): int
    {
        return (int) DocumentsModel::query()->where('assigned_to_id', $userId)->when($filialId, fn (Builder $q) => $q->where('filial_id', $filialId))->whereNotIn('status_doc', self::TERMINAL_STATUSES)->sum('estimated_workload_minutes');
    }

    private function activeCount(int $userId, ?int $filialId): int
    {
        return (int) DocumentsModel::query()->where('assigned_to_id', $userId)->when($filialId, fn (Builder $q) => $q->where('filial_id', $filialId))->whereNotIn('status_doc', self::TERMINAL_STATUSES)->count();
    }
}
