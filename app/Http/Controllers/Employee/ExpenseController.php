<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $expenses = ExpenseAdminModel::with('filial')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('employee.expense.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
        ]);

        $expense = new ExpenseAdminModel();
        $expense->user_id = auth()->id();
        $expense->filial_id = auth()->user()->filial_id;
        $expense->amount = $request->amount;
        $expense->description = $request->description;
        $expense->save();

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

        if ($month_year) {
            [$year_filter, $month_filter] = explode('-', $month_year);
        }

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
}
