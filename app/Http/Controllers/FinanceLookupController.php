<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Models\FilialModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceLookupController extends Controller
{
    public function index()
    {
        return view('admin.finance.lookups', [
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'vendors' => ExpenseVendor::query()->orderBy('name')->get(),
            'budgets' => Budget::query()->with(['filial', 'category'])->latest('id')->get(),
            'costCenters' => CostCenter::query()->with('filial')->orderBy('name')->get(),
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function category(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:expense_categories,code'],
            'expense_type' => ['required', Rule::in(['direct', 'branch', 'overhead'])],
        ]);
        ExpenseCategory::create([...$data, 'is_active' => true]);

        return back()->with('success', 'Expense category saqlandi.');
    }

    public function vendor(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'tax_id' => ['nullable', 'string', 'max:80'],
        ]);
        ExpenseVendor::create([...$data, 'is_active' => true]);

        return back()->with('success', 'Vendor saqlandi.');
    }

    public function budget(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        Budget::create([...$data, 'status' => 'active']);

        return back()->with('success', 'Budget saqlandi.');
    }

    public function costCenter(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:cost_centers,code'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);
        CostCenter::create([...$data, 'is_active' => true]);

        return back()->with('success', 'Cost center saqlandi.');
    }
}
