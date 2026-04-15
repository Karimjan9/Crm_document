<?php

namespace App\Support;

use App\Models\DocumentCourier;
use App\Models\DocumentsModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DeadlineBellData
{
    public static function buildFor(?User $user): array
    {
        $empty = self::emptyState();

        if (!$user) {
            return $empty;
        }

        try {
            if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
                return self::buildFromDocuments(
                    $user,
                    self::baseDocumentsQuery(),
                    [
                        'title' => 'Barcha deadlinelar',
                        'subtitle' => 'Tizimdagi barcha aktiv hujjatlar nazoratda.',
                        'index_url' => route('superadmin.document.index'),
                        'scope' => 'global',
                        'empty_message' => "Hozircha aktiv deadline yo'q.",
                    ]
                );
            }

            if ($user->hasRole('admin_filial')) {
                $query = self::baseDocumentsQuery()
                    ->where(function (Builder $builder) use ($user) {
                        if ($user->filial_id) {
                            $builder
                                ->where('filial_id', $user->filial_id)
                                ->orWhere('user_id', $user->id);
                        } else {
                            $builder->where('user_id', $user->id);
                        }
                    });

                return self::buildFromDocuments(
                    $user,
                    $query,
                    [
                        'title' => 'Filial deadlinelari',
                        'subtitle' => 'Filialingiz va sizga tegishli aktiv hujjatlar.',
                        'index_url' => route('admin_filial.document.index'),
                        'scope' => 'admin_filial',
                        'empty_message' => "Filial bo'yicha aktiv deadline topilmadi.",
                    ]
                );
            }

            if ($user->hasRole('employee')) {
                return self::buildFromDocuments(
                    $user,
                    self::baseDocumentsQuery()->where('user_id', $user->id),
                    [
                        'title' => 'Mening deadlinelarim',
                        'subtitle' => 'Siz yuritayotgan aktiv hujjatlar.',
                        'index_url' => route('employee.document.index'),
                        'scope' => 'employee',
                        'empty_message' => "Sizda aktiv deadline yo'q.",
                    ]
                );
            }

            if ($user->hasRole('courier')) {
                return self::buildFromCourierAssignments($user);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $empty;
    }

    protected static function buildFromDocuments(User $user, Builder $query, array $config): array
    {
        $now = Carbon::now();
        $documents = (clone $query)->get();

        $documentsWithDueAt = $documents->map(function (DocumentsModel $document) {
            return [
                'document' => $document,
                'due_at' => WorkdayCalendar::resolveDueAt($document->created_at, $document->deadline_time),
            ];
        });

        $total = $documentsWithDueAt->count();
        $overdueCount = $documentsWithDueAt
            ->filter(fn (array $item) => $item['due_at']->lessThan($now))
            ->count();
        $todayCount = $documentsWithDueAt
            ->filter(fn (array $item) => $item['due_at']->between($now->copy()->startOfDay(), $now->copy()->endOfDay()))
            ->count();

        $items = $documentsWithDueAt
            ->sortBy(fn (array $item) => $item['due_at']->getTimestamp())
            ->take(8)
            ->map(fn (array $item) => self::mapDocument($item['document'], $user, $config['scope'], $config['index_url'], $item['due_at']))
            ->values()
            ->all();

        return [
            'visible' => true,
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'index_url' => $config['index_url'],
            'empty_message' => $config['empty_message'],
            'items' => $items,
            'total' => $total,
            'overdue_count' => $overdueCount,
            'today_count' => $todayCount,
            'has_critical' => $overdueCount > 0,
        ];
    }

    protected static function buildFromCourierAssignments(User $user): array
    {
        $query = DocumentCourier::query()
            ->select('document_couriers.*')
            ->join('documents', 'documents.id', '=', 'document_couriers.document_id')
            ->with([
                'document.client:id,name',
                'document.service:id,name',
                'document.user:id,name',
                'sentBy:id,name',
            ])
            ->where('document_couriers.courier_id', $user->id)
            ->whereIn('document_couriers.status', ['sent', 'accepted'])
            ->where('documents.status_doc', '!=', 'finish');

        $indexUrl = route('courier.documents.index');
        $now = Carbon::now();
        $assignments = (clone $query)->get();

        $assignmentsWithDueAt = $assignments->map(function (DocumentCourier $assignment) {
            return [
                'assignment' => $assignment,
                'due_at' => WorkdayCalendar::resolveDueAt(
                    optional($assignment->document)->created_at,
                    optional($assignment->document)->deadline_time
                ),
            ];
        });

        $total = $assignmentsWithDueAt->count();
        $overdueCount = $assignmentsWithDueAt
            ->filter(fn (array $item) => $item['due_at']->lessThan($now))
            ->count();
        $todayCount = $assignmentsWithDueAt
            ->filter(fn (array $item) => $item['due_at']->between($now->copy()->startOfDay(), $now->copy()->endOfDay()))
            ->count();

        $items = $assignmentsWithDueAt
            ->sortBy(fn (array $item) => $item['due_at']->getTimestamp())
            ->take(8)
            ->map(fn (array $item) => self::mapCourierAssignment($item['assignment'], $indexUrl, $item['due_at']))
            ->values()
            ->all();

        return [
            'visible' => true,
            'title' => 'Courier deadlinelari',
            'subtitle' => 'Sizga biriktirilgan aktiv hujjatlar.',
            'index_url' => $indexUrl,
            'empty_message' => "Sizga biriktirilgan aktiv hujjat yo'q.",
            'items' => $items,
            'total' => $total,
            'overdue_count' => $overdueCount,
            'today_count' => $todayCount,
            'has_critical' => $overdueCount > 0,
        ];
    }

    protected static function baseDocumentsQuery(): Builder
    {
        return DocumentsModel::query()
            ->with([
                'client:id,name',
                'service:id,name',
                'user:id,name',
                'filial:id,name',
            ])
            ->where('status_doc', '!=', 'finish');
    }

    protected static function mapDocument(DocumentsModel $document, User $user, string $scope, string $indexUrl, ?Carbon $dueAt = null): array
    {
        $dueAt = $dueAt ?? WorkdayCalendar::resolveDueAt($document->created_at, $document->deadline_time);

        $meta = match ($scope) {
            'global' => trim(sprintf(
                'Filial: %s | Masul: %s',
                $document->filial->name ?? "Noma'lum",
                $document->user->name ?? "Noma'lum"
            )),
            'admin_filial' => $document->user_id === $user->id
                ? 'Siz kiritgan hujjat'
                : "Mas'ul: " . ($document->user->name ?? "Noma'lum"),
            'employee' => 'Xizmat: ' . ($document->service->name ?? "Noma'lum"),
            default => 'Aktiv hujjat',
        };

        return [
            'url' => $indexUrl,
            'doc_code' => $document->document_code ?: 'DOC-' . $document->id,
            'title' => $document->client->name ?? 'Mijoz nomi topilmadi',
            'subtitle' => $document->service->name ?? 'Xizmat topilmadi',
            'meta' => $meta,
            'due_at' => $dueAt->format('d.m.Y H:i'),
            'due_label' => self::buildDueLabel($dueAt),
            'remaining' => $document->deadline_remaining,
            'urgency' => self::resolveUrgency($dueAt),
            'flag' => $scope === 'admin_filial' && $document->user_id === $user->id ? 'Meniki' : null,
        ];
    }

    protected static function mapCourierAssignment(DocumentCourier $assignment, string $indexUrl, ?Carbon $dueAt = null): array
    {
        $document = $assignment->document;
        $dueAt = $dueAt ?? WorkdayCalendar::resolveDueAt(optional($document)->created_at, optional($document)->deadline_time);

        $statusLabel = $assignment->status === 'accepted' ? 'Qabul qilingan' : 'Yuborilgan';
        $senderName = $assignment->sentBy->name ?? "Noma'lum";

        return [
            'url' => $indexUrl,
            'doc_code' => $document->document_code ?? ('DOC-' . $assignment->document_id),
            'title' => $document->client->name ?? 'Mijoz nomi topilmadi',
            'subtitle' => $document->service->name ?? 'Xizmat topilmadi',
            'meta' => 'Holat: ' . $statusLabel . ' | Yuborgan: ' . $senderName,
            'due_at' => $dueAt->format('d.m.Y H:i'),
            'due_label' => self::buildDueLabel($dueAt),
            'remaining' => $document->deadline_remaining ?? "Noma'lum",
            'urgency' => self::resolveUrgency($dueAt),
            'flag' => $assignment->status === 'accepted' ? 'Qabul qildim' : 'Kutilyapti',
        ];
    }

    protected static function resolveDueAt($createdAt, $deadlineDays): Carbon
    {
        return WorkdayCalendar::resolveDueAt($createdAt, $deadlineDays);
    }

    protected static function buildDueLabel(Carbon $dueAt): string
    {
        $now = Carbon::now();

        if ($dueAt->lessThan($now)) {
            $hours = $dueAt->diffInHours($now);

            if ($hours < 24) {
                return $hours . ' soat kechikdi';
            }

            return $dueAt->diffInDays($now) . ' kun kechikdi';
        }

        if ($dueAt->isSameDay($now)) {
            return 'Bugun';
        }

        if ($dueAt->isSameDay($now->copy()->addDay())) {
            return 'Ertaga';
        }

        $hours = $now->diffInHours($dueAt);

        if ($hours < 24) {
            return $hours . ' soat qoldi';
        }

        return $now->diffInDays($dueAt) . ' kun qoldi';
    }

    protected static function resolveUrgency(Carbon $dueAt): string
    {
        $now = Carbon::now();

        if ($dueAt->lessThan($now)) {
            return 'overdue';
        }

        if ($dueAt->isSameDay($now)) {
            return 'today';
        }

        if ($dueAt->lessThanOrEqualTo($now->copy()->addDays(2))) {
            return 'soon';
        }

        return 'normal';
    }

    protected static function emptyState(): array
    {
        return [
            'visible' => false,
            'title' => 'Deadline',
            'subtitle' => '',
            'index_url' => null,
            'empty_message' => 'Deadline topilmadi.',
            'items' => [],
            'total' => 0,
            'overdue_count' => 0,
            'today_count' => 0,
            'has_critical' => false,
        ];
    }
}
