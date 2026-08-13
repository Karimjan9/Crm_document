<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Services\ExpenseManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseManagementService $expenseService)
    {
    }

    public function index()
    {
        $user = auth()->user();

        $expenses = ExpenseAdminModel::with('filial')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('employee.expense.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:2000',
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:expense_vendors,id'],
            'payment_method' => ['nullable', Rule::in(['cash', 'card', 'bank_transfer', 'online', 'other'])],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', Rule::in(['weekly', 'monthly', 'yearly'])],
            'recurrence_start' => ['nullable', 'date'],
            'recurrence_end' => ['nullable', 'date', 'after_or_equal:recurrence_start'],
            'expense_type' => ['nullable', Rule::in(['direct', 'branch', 'overhead'])],
            'expense_date' => ['nullable', 'date'],
        ]);

        abort_unless(auth()->user()->filial_id !== null, 422, 'Foydalanuvchiga filial biriktirilmagan.');
        $data['user_id'] = auth()->id();
        $data['filial_id'] = auth()->user()->filial_id;
        $receipt = $request->file('receipt');
        unset($data['receipt']);
        $this->expenseService->save($data, null, $receipt, $request->user());

        return redirect()->route('employee.expense_admin.index')
            ->with('success', 'Xarajat muvaffaqiyatli qo\'shildi.');
    }

    public function statistika(Request $request)
    {
        $user = auth()->user();
        $user_filter = $request->input('user_id');
        $month_year = $request->input('month_year');

        $year_filter = null;
        $month_filter = null;

        [$year_filter, $month_filter] = $this->parseMonthYear($month_year);

        $query = ExpenseAdminModel::query()
            ->where('user_id', $user->id);

        if ($year_filter && $month_filter) {
            $query->whereYear('created_at', $year_filter)
                ->whereMonth('created_at', $month_filter);
        }

        $expenses = $query->orderBy('id', 'desc')->get();
        $total_amount = $expenses->sum('amount');
        $chartData = $expenses->groupBy('user_id')->map(function ($items) {
            return $items->sum('amount');
        });

        $users = collect([$user]);

        return view('employee.expense.statistika', compact(
            'expenses',
            'users',
            'user_filter',
            'month_year',
            'year_filter',
            'month_filter',
            'total_amount',
            'chartData',
            'user'
        ));
    }

    private function parseMonthYear(?string $monthYear): array
    {
        if (! $monthYear || ! preg_match('/^(\d{4})-(\d{1,2})$/', $monthYear, $matches)) {
            return [null, null];
        }

        $month = (int) $matches[2];

        return $month >= 1 && $month <= 12
            ? [(int) $matches[1], $month]
            : [null, null];
    }
}
