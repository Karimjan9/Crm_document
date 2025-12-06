<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use Illuminate\Http\Request;

class ExpenseAdminController extends Controller
{

    public function index()
    {
        $user      = auth()->user();
        $filial_id = $user->filial_id;

        $query = ExpenseAdminModel::with('filial');

        // Agar admin_filial bo'lsa → shu filialdagi barcha xarajatlar
        if ($user->hasRole('admin_filial')) {

            $query->where('filial_id', $filial_id);

        } else {
            // Employee bo'lsa → faqat o'zining xarajatlari
            $query->where('user_id', $user->id);
        }

        $expenses = $query->orderBy('id', 'desc')->get();

        return view('admin.expense.part.index', compact('expenses'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // Validatsiya
        $request->validate([
            'amount'      => 'required|numeric|min:1000', // Majburiy, raqam, minimal 1000
            'description' => 'nullable|string',           // ixtiyoriy, maksimal 255 belgi
        ]);

        $user_id   = auth()->user()->id;
        $filial_id = auth()->user()->filial_id;

        $expense              = new ExpenseAdminModel();
        $expense->user_id     = $user_id;
        $expense->filial_id   = $filial_id;
        $expense->amount      = $request->amount;
        $expense->description = $request->description;
        $expense->save();

        return redirect()->route('admin_filial.expense_admin.index')
            ->with('success', 'Xarajat muvaffaqiyatli qo\'shildi.');
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
        $user = auth()->user();
        // dd($user->hasRole('admin_filial'));
        $user      = auth()->user();
        $filial_id = $user->filial_id;

        $user_filter = $request->input('user_id');
        $month_year  = $request->input('month_year');

        $year_filter  = null;
        $month_filter = null;

        if ($month_year) {
            [$year_filter, $month_filter] = explode('-', $month_year);
        }

        $query = ExpenseAdminModel::query();

        if ($user->hasRole('admin_filial')) {
            $query->where('filial_id', $filial_id);
            if ($user_filter) {
                $query->where('user_id', $user_filter);
            }

            // Filialdagi faqat employee roli bo‘lgan xodimlar
            $users = \App\Models\User::where('filial_id', $filial_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'employee');
                })
                ->get();
        } else {
            $query->where('user_id', $user->id);
            $users = collect([$user]);
        }

        if ($year_filter && $month_filter) {
            $query->whereYear('created_at', $year_filter)
                ->whereMonth('created_at', $month_filter);
        }

        $expenses     = $query->orderBy('id', 'desc')->get();
        $total_amount = $expenses->sum('amount');

        $chartData = $expenses->groupBy('user_id')->map(function ($items) {
            return $items->sum('amount');
        });
        // dd($users);
        return view('admin.expense.part.statistika', compact(
            'expenses', 'users', 'user_filter', 'month_year',
            'year_filter', 'month_filter', 'total_amount', 'chartData', 'user'
        ));
    }

}
