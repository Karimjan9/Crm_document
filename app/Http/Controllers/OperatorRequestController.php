<?php

namespace App\Http\Controllers;

use App\Models\OperatorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OperatorRequestController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(OperatorRequest::STATUS_LABELS))],
        ]);
        $query = OperatorRequest::query()->with('handledBy');
        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                foreach (['name', 'phone', 'telegram_username', 'message'] as $field) {
                    $query->orWhere($field, 'like', '%'.$search.'%');
                }
            });
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $counts = OperatorRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('operators.index', [
            'requests' => $query->latest('id')->paginate(20)->withQueryString(),
            'counts' => $counts, 'statuses' => OperatorRequest::STATUS_LABELS,
            'search' => $search, 'selectedStatus' => $filters['status'] ?? '',
        ]);
    }

    public function update(Request $request, OperatorRequest $operatorRequest)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(OperatorRequest::STATUS_LABELS))]]);
        DB::transaction(function () use ($request, $operatorRequest, $data): void {
            $operatorRequest = OperatorRequest::query()->lockForUpdate()->findOrFail($operatorRequest->id);
            if ($operatorRequest->status === $data['status']) {
                return;
            }
            $operatorRequest->forceFill([
                'status' => $data['status'],
                'handled_by_id' => $data['status'] === 'new' ? null : $request->user()->id,
                'handled_at' => $data['status'] === 'new' ? null : now(),
            ])->save();
            $operatorRequest->workItem?->forceFill([
                'status' => ['new' => 'open', 'contacted' => 'in_progress', 'resolved' => 'done'][$data['status']],
                'completed_at' => $data['status'] === 'resolved' ? now() : null,
            ])->save();
        });

        return back()->with('success', 'Murojaat holati yangilandi.');
    }
}
