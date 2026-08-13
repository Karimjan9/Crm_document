<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Services\ExpenseManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseAdminController extends Controller
{
    public function __construct(private readonly ExpenseManagementService $expenseService)
    {
    }

    public function index()
    {
        $expenses = $this->scopedExpenses()
            ->with(['filial', 'category', 'vendor'])
            ->orderByDesc('id')
            ->get();

        return view('admin.expense.part.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
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

        $user = auth()->user();
        abort_unless($user->filial_id !== null, 422, 'Foydalanuvchiga filial biriktirilmagan.');

        $data['user_id'] = $user->id;
        $data['filial_id'] = $user->filial_id;
        $receipt = $request->file('receipt');
        unset($data['receipt']);
        $this->expenseService->save($data, null, $receipt, $user);

        /*
         * The service also creates the default branch allocation. Keeping the
         * branch controller thin ensures admin and employee expense flows use
         * exactly the same approval, receipt and recurring rules.
         */
        /* legacy payload shape retained for existing forms */
        /*
        ExpenseAdminModel::create([
            'user_id' => $user->id,
            'filial_id' => $user->filial_id,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);
        */

        return redirect()
            ->route('admin_filial.expense_admin.index')
            ->with('success', 'Xarajat muvaffaqiyatli qo\'shildi.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
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

        $expense = $this->scopedExpenses()->findOrFail($id);
        $data['filial_id'] = $expense->filial_id;
        $data['user_id'] = $expense->user_id;
        $receipt = $request->file('receipt');
        unset($data['receipt']);
        $this->expenseService->save($data, $expense, $receipt, auth()->user());

        return redirect()
            ->route('admin_filial.expense_admin.index')
            ->with('success', 'Xarajat muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $this->expenseService->delete($this->scopedExpenses()->findOrFail($id));

        return redirect()
            ->route('admin_filial.expense_admin.index')
            ->with('success', 'Xarajat o\'chirildi.');
    }

    public function statistika(Request $request)
    {
        $user = auth()->user();
        $userFilter = $request->input('user_id');
        $monthYear = $request->input('month_year');
        [$yearFilter, $monthFilter] = $this->parseMonthYear($monthYear);

        $query = $this->scopedExpenses();

        if ($user->hasRole('admin_filial') && $userFilter) {
            $query->where('user_id', (int) $userFilter);
        }

        if ($yearFilter && $monthFilter) {
            $query->whereYear('created_at', $yearFilter)
                ->whereMonth('created_at', $monthFilter);
        }

        $expenses = $query->with('filial')->orderByDesc('id')->get();
        $totalAmount = $expenses->sum('amount');
        $chartData = $expenses->groupBy('user_id')->map(fn ($items) => $items->sum('amount'));

        $users = $user->hasRole('admin_filial')
            ? \App\Models\User::query()
                ->where('filial_id', $user->filial_id)
                ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'employee'))
                ->orderBy('name')
                ->get()
            : collect([$user]);

        return view('admin.expense.part.statistika', [
            'expenses' => $expenses,
            'users' => $users,
            'user_filter' => $userFilter,
            'month_year' => $monthYear,
            'year_filter' => $yearFilter,
            'month_filter' => $monthFilter,
            'total_amount' => $totalAmount,
            'chartData' => $chartData,
            'user' => $user,
        ]);
    }

    protected function scopedExpenses()
    {
        $user = auth()->user();

        return ExpenseAdminModel::query()
            ->when(
                $user->hasRole('admin_filial'),
                fn ($query) => $query->where('filial_id', $user->filial_id),
                fn ($query) => $query->where('user_id', $user->id)
            );
    }

    protected function parseMonthYear(?string $monthYear): array
    {
        if (!$monthYear || !preg_match('/^(\d{4})-(\d{1,2})$/', $monthYear, $matches)) {
            return [null, null];
        }

        return [(int) $matches[1], (int) $matches[2]];
    }
}
