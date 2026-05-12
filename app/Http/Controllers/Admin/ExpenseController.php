<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\User;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
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
    
    public function index()
    {
        return view('admin.expense.index');
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    
    public function update(Request $request, $id)
    {
        //
    }

   
    public function destroy($id)
    {
        //
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
