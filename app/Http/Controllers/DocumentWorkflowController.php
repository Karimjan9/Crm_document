<?php

namespace App\Http\Controllers;

use App\Models\DocumentsModel;
use App\Models\DocumentChecklist;
use App\Services\DocumentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentWorkflowController extends Controller
{
    public function __construct(private readonly DocumentWorkflowService $workflow)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('workflowViewAny', DocumentsModel::class);

        $filters = $request->only(['filial_id', 'status', 'queue', 'priority', 'assigned_to_id', 'q']);
        return view('document_workflow.index', [
            'dashboard' => $this->workflow->dashboard($request->user(), $filters),
            'filters' => $filters,
            'filials' => $request->user()->hasAnyRole(['super_admin', 'admin_manager'])
                ? \App\Models\FilialModel::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }

    public function data(Request $request)
    {
        $this->authorize('workflowViewAny', DocumentsModel::class);

        return response()->json([
            'data' => $this->workflow->dashboard($request->user(), $request->only([
                'filial_id', 'status', 'queue', 'priority', 'assigned_to_id', 'q',
            ])),
        ]);
    }

    public function history(Request $request, DocumentsModel $document)
    {
        $this->authorize('workflowView', $document);

        $document->load([
            'statusHistories.changedBy',
            'assignmentHistories.assignedTo',
            'assignmentHistories.qaUser',
            'assignmentHistories.assignedBy',
            'checklists.completedBy',
            'latestQaReview.reviewer',
        ]);

        $checklist = app(\App\Services\DocumentChecklistService::class)->progress($document);
        $qaStatus = app(\App\Services\DocumentChecklistService::class)->qaStatus($document);

        return response()->json([
            'data' => [
                'document' => [
                    'id' => $document->id,
                    'document_code' => $document->document_code,
                    'status' => $document->workflow_status,
                    'status_label' => $document->status_label,
                ],
                'status_history' => $document->statusHistories->map(fn ($item) => [
                    'from_status' => $item->from_status,
                    'from_label' => $this->workflow->label($item->from_status),
                    'to_status' => $item->to_status,
                    'to_label' => $this->workflow->label($item->to_status),
                    'changed_by' => $item->changedBy?->name ?: 'System',
                    'reason' => $item->reason,
                    'comment' => $item->comment,
                    'created_at' => optional($item->created_at)->format('d.m.Y H:i'),
                ])->values(),
                'assignment_history' => $document->assignmentHistories->map(fn ($item) => [
                    'assigned_to' => $item->assignedTo?->name,
                    'qa_user' => $item->qaUser?->name,
                    'assigned_by' => $item->assignedBy?->name ?: 'System',
                    'queue' => $item->queue,
                    'priority' => $item->priority,
                    'workload_minutes' => $item->estimated_workload_minutes,
                    'source' => $item->source,
                    'notes' => $item->notes,
                    'created_at' => optional($item->created_at)->format('d.m.Y H:i'),
                ])->values(),
                'checklist' => [
                    'total' => $checklist['total'],
                    'completed' => $checklist['completed'],
                    'remaining' => $checklist['remaining'],
                    'complete' => $checklist['complete'],
                    'items' => $checklist['items']->map(fn ($item) => [
                        'id' => $item->id,
                        'code' => $item->code,
                        'title' => $item->title,
                        'is_required' => $item->is_required,
                        'requires_file' => $item->requires_file,
                        'is_completed' => $item->is_completed,
                        'completed_by' => $item->completedBy?->name,
                        'completed_at' => optional($item->completed_at)->format('d.m.Y H:i'),
                        'notes' => $item->notes,
                    ])->values(),
                ],
                'qa' => [
                    'status' => $qaStatus,
                    'reviewer' => $document->latestQaReview?->reviewer?->name,
                    'reason' => $document->latestQaReview?->reason,
                    'comment' => $document->latestQaReview?->comment,
                    'created_at' => optional($document->latestQaReview?->created_at)->format('d.m.Y H:i'),
                ],
            ],
        ]);
    }

    public function checklist(Request $request, DocumentsModel $document, DocumentChecklist $checklist)
    {
        $this->authorize('checklistUpdate', $document);
        abort_unless((int) $checklist->document_id === (int) $document->id, 404);

        $data = $request->validate([
            'is_completed' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        app(\App\Services\DocumentChecklistService::class)->toggle(
            $checklist,
            (bool) $data['is_completed'],
            $request->user(),
            $data['notes'] ?? null,
        );

        return $this->respond($request, $document, 'Checklist yangilandi.');
    }

    public function qaReview(Request $request, DocumentsModel $document)
    {
        $this->authorize('qaReview', $document);

        $data = $request->validate([
            'result' => ['required', Rule::in(['passed', 'failed'])],
            'reason' => ['nullable', 'string', 'max:500'],
            'comment' => ['nullable', 'string', 'max:4000'],
        ]);

        app(\App\Services\DocumentChecklistService::class)->review(
            $document,
            $data['result'],
            $request->user(),
            $data['reason'] ?? null,
            $data['comment'] ?? null,
        );

        return $this->respond($request, $document, 'QA natijasi saqlandi.');
    }

    public function transition(Request $request, DocumentsModel $document)
    {
        $this->authorize('workflowUpdate', $document);

        $data = $request->validate([
            'status' => ['required', Rule::in(DocumentsModel::STATUSES)],
            'reason' => ['nullable', 'string', 'max:500'],
            'comment' => ['nullable', 'string', 'max:4000'],
        ]);

        $force = $request->user()->hasAnyRole(['super_admin', 'admin_manager']) && $request->boolean('force');
        $document = $this->workflow->transition(
            $document,
            $data['status'],
            $request->user(),
            $data['reason'] ?? null,
            $data['comment'] ?? null,
            [],
            $force,
        );

        return $this->respond($request, $document, 'Hujjat statusi yangilandi.');
    }

    public function assign(Request $request, DocumentsModel $document)
    {
        $this->authorize('workflowAssign', $document);

        $data = $request->validate([
            'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'],
            'qa_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'queue' => ['nullable', Rule::in(DocumentsModel::QUEUES)],
            'priority' => ['nullable', Rule::in(DocumentsModel::PRIORITIES)],
            'estimated_workload_minutes' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->boolean('auto_assign')) {
            $document = $this->workflow->autoAssign($document, $request->user());
        } else {
            $document = $this->workflow->assign(
                $document,
                isset($data['assigned_to_id']) ? (int) $data['assigned_to_id'] : null,
                isset($data['qa_user_id']) ? (int) $data['qa_user_id'] : null,
                $data['queue'] ?? null,
                $data['priority'] ?? null,
                isset($data['estimated_workload_minutes']) ? (int) $data['estimated_workload_minutes'] : null,
                $request->user(),
                'manual',
                $data['notes'] ?? null,
            );
        }

        return $this->respond($request, $document, 'Mas’ul xodim va QA biriktirildi.');
    }

    private function respond(Request $request, DocumentsModel $document, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message, 'data' => [
                'id' => $document->id,
                'status' => $document->workflow_status,
                'status_label' => $document->status_label,
                'assigned_to_id' => $document->assigned_to_id,
                'qa_user_id' => $document->qa_user_id,
                'priority' => $document->priority,
                'queue' => $document->queue,
            ]]);
        }

        return redirect()->back()->with('success', $message);
    }
}
