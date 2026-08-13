<?php

namespace App\Http\Controllers;

use App\Models\FilialModel;
use App\Models\User;
use App\Services\FilialPnlService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FilialPnlController extends Controller
{
    public function __construct(private readonly FilialPnlService $pnl)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $filials = $this->visibleFilials($request->user())->get();
        $selectedId = isset($filters['filial_id']) ? (int) $filters['filial_id'] : null;

        if ($selectedId !== null && ! $filials->contains('id', $selectedId)) {
            abort(403, 'Bu filial P&L hisobotiga kirish huquqi yo\'q.');
        }

        $reports = $this->pnl->buildMany($filials, $filters['date_from'], $filters['date_to']);
        $selectedReport = collect($reports)->first(
            fn (array $report): bool => $selectedId !== null && (int) $report['profile']['id'] === $selectedId
        );

        if ($request->expectsJson()) {
            return response()->json([
                'period' => [
                    'from' => $filters['date_from']->toDateString(),
                    'to' => $filters['date_to']->toDateString(),
                ],
                'branches' => $reports,
                'selected' => $selectedReport,
            ]);
        }

        return view('admin.finance.filials', [
            'reports' => $reports,
            'selectedReport' => $selectedReport,
            'filials' => $filials,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, FilialModel $filial)
    {
        $this->authorizeFilial($request->user(), $filial);
        $filters = $this->filters($request);
        $report = $this->pnl->build($filial, $filters['date_from'], $filters['date_to']);

        if ($request->expectsJson()) {
            return response()->json($report);
        }

        return view('admin.finance.filials', [
            'reports' => [$report],
            'selectedReport' => $report,
            'filials' => $this->visibleFilials($request->user())->get(),
            'filters' => $filters,
        ]);
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);

        return [
            'date_from' => Carbon::parse($data['date_from'] ?? today()->startOfMonth()->toDateString())->startOfDay(),
            'date_to' => Carbon::parse($data['date_to'] ?? today()->toDateString())->endOfDay(),
            'filial_id' => $data['filial_id'] ?? null,
        ];
    }

    private function visibleFilials(?User $user): Builder
    {
        return FilialModel::query()
            ->with('manager:id,name')
            ->when(! $user?->hasAnyRole(['super_admin', 'admin_manager']), function (Builder $query) use ($user): void {
                $query->where('id', $user?->filial_id ?: -1);
            })
            ->orderBy('name');
    }

    private function authorizeFilial(?User $user, FilialModel $filial): void
    {
        if ($user?->hasAnyRole(['super_admin', 'admin_manager'])) {
            return;
        }

        abort_unless($user && $user->hasRole('admin_filial') && (int) $user->filial_id === (int) $filial->id, 403);
    }
}
