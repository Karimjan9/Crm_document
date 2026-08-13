<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\FilialModel;
use App\Models\User;
use App\Services\ExpenseManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseManagementService $expenseService)
    {
    }

    protected array $monthNames = [
        1 => 'Yanvar',
        2 => 'Fevral',
        3 => 'Mart',
        4 => 'Aprel',
        5 => 'May',
        6 => 'Iyun',
        7 => 'Iyul',
        8 => 'Avgust',
        9 => 'Sentabr',
        10 => 'Oktabr',
        11 => 'Noyabr',
        12 => 'Dekabr',
    ];

    protected function routePrefix(): string
    {
        return request()->routeIs('superadmin.*') ? 'superadmin' : 'admin';
    }
    
    public function index(Request $request)
    {
        $query = ExpenseAdminModel::query()
            ->with([
                'user' => fn ($builder) => $builder->withTrashed()->select('id', 'name', 'login'),
                'filial:id,name',
                'category:id,name,expense_type',
                'vendor:id,name',
                'approver:id,name',
            ])
            ->when($request->filled('filial_id'), fn ($builder) => $builder->where('filial_id', $request->integer('filial_id')))
            ->when($request->filled('user_id'), fn ($builder) => $builder->where('user_id', $request->integer('user_id')))
            ->when($request->filled('date_from'), fn ($builder) => $builder->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($builder) => $builder->whereDate('created_at', '<=', $request->input('date_to')))
            ->orderByDesc('id');

        $expenses = $query->paginate(25)->withQueryString();
        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name']);
        $users = User::withTrashed()
            ->whereIn('id', ExpenseAdminModel::query()->select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'login', 'filial_id']);

        return view('admin.expense.crud-index', [
            'expenses' => $expenses,
            'filials' => $filials,
            'users' => $users,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create()
    {
        return view('admin.expense.form', [
            'expense' => new ExpenseAdminModel(),
            ...$this->formLookups(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedExpense($request);
        $data['user_id'] = $this->resolveExpenseUserId($data['user_id'] ?? null);
        $this->ensureUserCanBeAssignedToFilial($data['user_id'], (int) $data['filial_id']);

        $receipt = $request->file('receipt');
        unset($data['receipt']);
        $this->expenseService->save($data, null, $receipt, $request->user());

        return redirect()
            ->route($this->routePrefix() . '.expense.index')
            ->with('success', 'Xarajat muvaffaqiyatli qo\'shildi.');
    }

    public function show($id)
    {
        $expense = ExpenseAdminModel::query()
            ->with([
                'user' => fn ($builder) => $builder->withTrashed(),
                'filial',
                'category',
                'vendor',
                'approver',
                'budget',
                'costCenter',
                'allocations.filial',
            ])
            ->findOrFail($id);

        return view('admin.expense.show', [
            'expense' => $expense,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function edit($id)
    {
        $expense = ExpenseAdminModel::findOrFail($id);

        return view('admin.expense.form', [
            'expense' => $expense,
            ...$this->formLookups(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $expense = ExpenseAdminModel::findOrFail($id);
        $data = $this->validatedExpense($request);
        $data['user_id'] = $this->resolveExpenseUserId($data['user_id'] ?? $expense->user_id);
        $this->ensureUserCanBeAssignedToFilial($data['user_id'], (int) $data['filial_id']);

        $receipt = $request->file('receipt');
        unset($data['receipt']);
        $this->expenseService->save($data, $expense, $receipt, $request->user());

        return redirect()
            ->route($this->routePrefix() . '.expense.index')
            ->with('success', 'Xarajat muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $this->expenseService->delete(ExpenseAdminModel::findOrFail($id));

        return redirect()
            ->route($this->routePrefix() . '.expense.index')
            ->with('success', 'Xarajat o\'chirildi.');
    }

    public function receipt(ExpenseAdminModel $expense)
    {
        $user = request()->user();
        $canView = match (true) {
            $user?->hasAnyRole(['super_admin', 'admin_manager']) => true,
            $user?->hasRole('admin_filial') => (int) $user->filial_id === (int) $expense->filial_id,
            $user?->hasRole('employee') => (int) $user->id === (int) $expense->user_id
                && (int) $user->filial_id === (int) $expense->filial_id,
            default => false,
        };

        abort_unless($canView && $expense->receipt_path, 404);

        $disk = Storage::disk('private');
        abort_unless($disk->exists($expense->receipt_path), 404);

        return $disk->download($expense->receipt_path, $expense->receipt_original_name ?: basename($expense->receipt_path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(ExpenseAdminModel $expense)
    {
        $expense->forceFill([
            'approval_status' => 'approved',
            'approver_id' => auth()->id(),
            'approved_at' => now(),
        ])->save();

        return redirect()->back()->with('success', 'Xarajat tasdiqlandi.');
    }

    protected function validatedExpense(Request $request): array
    {
        return $request->validate([
            'filial_id' => ['required', 'integer', 'exists:filial,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:expense_vendors,id'],
            'payment_method' => ['nullable', Rule::in(['cash', 'card', 'bank_transfer', 'online', 'other'])],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'approver_id' => ['nullable', 'integer', 'exists:users,id'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', Rule::in(['weekly', 'monthly', 'yearly'])],
            'recurrence_start' => ['nullable', 'date'],
            'recurrence_end' => ['nullable', 'date', 'after_or_equal:recurrence_start'],
            'next_occurrence' => ['nullable', 'date'],
            'budget_id' => ['nullable', 'integer', 'exists:budgets,id'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'expense_type' => ['nullable', Rule::in(['direct', 'branch', 'overhead'])],
            'expense_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.filial_id' => ['required_with:allocations', 'integer', 'exists:filial,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'gt:0'],
            'allocations.*.note' => ['nullable', 'string', 'max:500'],
        ]);
    }

    protected function formLookups(): array
    {
        return [
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'login', 'filial_id']),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'expense_type']),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'budgets' => Budget::query()->where('status', 'active')->orderByDesc('period_end')->get(['id', 'name', 'amount']),
            'costCenters' => CostCenter::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function resolveExpenseUserId(?int $userId): int
    {
        return (int) ($userId ?: auth()->id());
    }

    protected function ensureUserCanBeAssignedToFilial(int $userId, int $filialId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->filial_id !== null && (int) $user->filial_id !== $filialId) {
            throw ValidationException::withMessages([
                'user_id' => 'Tanlangan foydalanuvchi ushbu filialga tegishli emas.',
            ]);
        }
    }

    public function statistika(Request $request)
    {
        $selectedYear = (int) ($request->input('year') ?: now()->year);
        $selectedMonth = $request->filled('month') ? (int) $request->input('month') : null;

        $query = ExpenseAdminModel::query();
        $this->applyFilters($query, $request, $selectedYear, $selectedMonth);

        $expenses = (clone $query)
            ->with(['user' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login'), 'filial:id,name'])
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name']);
        $users = User::withTrashed()
            ->whereIn('id', ExpenseAdminModel::query()->select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'login', 'filial_id']);

        return view('admin.expense.statistika', [
            'routePrefix' => $this->routePrefix(),
            'filters' => $request->query(),
            'filials' => $filials,
            'users' => $users,
            'monthNames' => $this->monthNames,
            'yearOptions' => $this->yearOptions($selectedYear),
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'summary' => $this->summaryForQuery(clone $query),
            'monthlyStats' => $this->monthlyStats($request, $selectedYear),
            'filialStats' => $this->groupedStats(clone $query, 'filial_id', $filials->pluck('name', 'id')->all()),
            'userStats' => $this->groupedStats(clone $query, 'user_id', $users->pluck('name', 'id')->all()),
            'expenses' => $expenses,
        ]);
    }

    protected function applyFilters($query, Request $request, int $selectedYear, ?int $selectedMonth, array $ignore = []): void
    {
        if (!in_array('filial_id', $ignore, true) && $request->filled('filial_id')) {
            $query->where('filial_id', $request->integer('filial_id'));
        }

        if (!in_array('user_id', $ignore, true) && $request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if (!in_array('year', $ignore, true)) {
            $query->whereYear('created_at', $selectedYear);
        }

        if (!in_array('month', $ignore, true) && $selectedMonth) {
            $query->whereMonth('created_at', $selectedMonth);
        }

        if (!in_array('date_from', $ignore, true) && $request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if (!in_array('date_to', $ignore, true) && $request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
    }

    protected function summaryForQuery($query): array
    {
        return [
            'total_amount' => (float) (clone $query)->sum('amount'),
            'expense_count' => (clone $query)->count(),
            'filial_count' => (clone $query)->distinct('filial_id')->count('filial_id'),
            'user_count' => (clone $query)->distinct('user_id')->count('user_id'),
            'average_amount' => (float) ((clone $query)->avg('amount') ?: 0),
        ];
    }

    protected function monthlyStats(Request $request, int $selectedYear): array
    {
        $query = ExpenseAdminModel::query();
        $this->applyFilters($query, $request, $selectedYear, null, ['month', 'date_from', 'date_to']);
        $monthExpression = $this->monthExpression();

        $rows = $query
            ->selectRaw("{$monthExpression} as month, COUNT(*) as expense_count, COALESCE(SUM(amount), 0) as total_amount")
            ->groupByRaw($monthExpression)
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function (int $month) use ($rows) {
            $row = $rows->get($month);

            return [
                'month' => $month,
                'label' => $this->monthNames[$month],
                'expenses' => (int) ($row->expense_count ?? 0),
                'amount' => (float) ($row->total_amount ?? 0),
            ];
        })->all();
    }

    protected function groupedStats($query, string $column, array $labels): array
    {
        return $query
            ->selectRaw("{$column}, COUNT(*) as expense_count, COALESCE(SUM(amount), 0) as total_amount, COALESCE(AVG(amount), 0) as average_amount")
            ->groupBy($column)
            ->orderByDesc('total_amount')
            ->limit(12)
            ->get()
            ->map(function ($row) use ($column, $labels) {
                $id = $row->{$column};

                return [
                    'id' => $id,
                    'label' => $labels[$id] ?? 'Noma\'lum',
                    'expenses' => (int) $row->expense_count,
                    'amount' => (float) $row->total_amount,
                    'average' => (float) $row->average_amount,
                ];
            })
            ->all();
    }

    protected function yearOptions(int $selectedYear)
    {
        $yearExpression = $this->yearExpression();

        $years = ExpenseAdminModel::query()
            ->selectRaw("{$yearExpression} as year")
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->values();

        return $years->contains($selectedYear)
            ? $years
            : $years->push($selectedYear)->unique()->sortDesc()->values();
    }

    protected function yearExpression(): string
    {
        return ExpenseAdminModel::query()->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';
    }

    protected function monthExpression(): string
    {
        return ExpenseAdminModel::query()->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';
    }
}
