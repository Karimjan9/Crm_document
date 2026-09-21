<?php

namespace App\Services;

use App\Models\DocumentsModel;
use App\Models\User;
use App\Models\WorkflowResponsibilityRule;
use App\Models\WorkItem;

class WorkflowResponsibilityService
{
    public function sync(DocumentsModel $document, string $status, ?User $actor = null): void
    {
        WorkItem::query()->where('document_id', $document->id)->where('type', 'workflow')->whereIn('status', ['open', 'in_progress', 'blocked'])->update(['status' => 'done', 'completed_at' => now()]);
        if (in_array($status, ['completed', 'cancelled', 'refunded'], true)) return;
        $rule = WorkflowResponsibilityRule::query()->where('is_active', true)->where('trigger_status', $status)->where(fn ($q) => $q->where('filial_id', $document->filial_id)->orWhereNull('filial_id'))->orderByRaw('filial_id IS NULL')->first();
        $fallback = $this->fallback($status);
        $role = $rule?->responsible_role ?: $fallback['role'];
        $assignee = $this->assignee($document, $role);
        WorkItem::create([
            'filial_id' => $document->filial_id, 'order_id' => $document->order_id, 'document_id' => $document->id,
            'assigned_to_id' => $assignee?->id, 'created_by_id' => $actor?->id, 'assigned_role' => $role,
            'type' => 'workflow', 'title' => $rule?->title ?: $fallback['title'], 'description' => 'Hujjat: ' . ($document->document_code ?: '#'.$document->id),
            'status' => 'open', 'priority' => $document->priority ?: 'normal',
            'due_at' => $document->deadline_due_at ?: now()->addHours($rule?->due_hours ?: $fallback['hours']),
            'metadata' => ['trigger_status' => $status, 'escalate_to_role' => $rule?->escalate_to_role],
        ]);
    }

    public function reassign(DocumentsModel $document): void
    {
        $open = WorkItem::query()->where('document_id', $document->id)->where('type', 'workflow')->whereIn('status', ['open', 'in_progress', 'blocked'])->latest('id')->first();
        if (! $open) return;
        $user = $this->assignee($document, (string) $open->assigned_role);
        $open->forceFill(['assigned_to_id' => $user?->id])->save();
    }

    private function assignee(DocumentsModel $document, string $role): ?User
    {
        return match ($role) {
            'qa' => $document->qaUser,
            'employee' => $document->assignedTo ?: $document->user,
            'admin_filial' => User::query()->where('filial_id', $document->filial_id)->whereHas('roles', fn ($q) => $q->where('name', 'admin_filial'))->orderBy('id')->first(),
            default => $document->assignedTo ?: $document->user,
        };
    }

    private function fallback(string $status): array
    {
        return match ($status) {
            'waiting_documents' => ['role' => 'employee', 'title' => 'Mijozdan yetishmayotgan hujjatlarni olish', 'hours' => 24],
            'awaiting_payment', 'partially_paid' => ['role' => 'admin_filial', 'title' => 'To‘lovni nazorat qilish', 'hours' => 24],
            'waiting_review' => ['role' => 'qa', 'title' => 'QA tekshiruvini bajarish', 'hours' => 8],
            'qa_failed' => ['role' => 'employee', 'title' => 'QA xatolarini tuzatish', 'hours' => 8],
            'ready_for_delivery' => ['role' => 'admin_filial', 'title' => 'Mijozga topshirishni tashkil qilish', 'hours' => 12],
            default => ['role' => 'employee', 'title' => 'Hujjat ustida ishlash', 'hours' => 24],
        };
    }
}
