<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAdminModel;
use Illuminate\Http\Request;

class ExpenseAdminController extends Controller
{

    public function index()
    {
        $user_id  = auth()->user()->id;
        $expenses = ExpenseAdminModel::where('user_id', $user_id)->get();
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

        return redirect()->route('admin_filial.expense.index')
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
        // dd(123);
        $filial_id = auth()->user()->filial_id;

        // Filterlar
        $user_filter = $request->input('user_id');
        $amount_min  = $request->input('amount_min');
        $amount_max  = $request->input('amount_max');

        $query = ExpenseAdminModel::where('filial_id', $filial_id);

        if ($user_filter) {
            $query->where('user_id', $user_filter);
        }

        if ($amount_min) {
            $query->where('amount', '>=', $amount_min);
        }

        if ($amount_max) {
            $query->where('amount', '<=', $amount_max);
        }

        $expenses = $query->orderBy('id', 'desc')->get();

    
        $total_amount = $expenses->sum('amount');

 
       $users = \App\Models\User::where('filial_id', $filial_id)->get() ?? collect([]);
        $users = $users ?: collect([]);
        $chartData = $expenses->groupBy('user_id')->map(function ($items) {
            return $items->sum('amount');
        });

        return view('admin.expense.part.statistika', compact(
            'expenses', 'users', 'user_filter', 'amount_min', 'amount_max', 'total_amount', 'chartData'
        ));
    }

}
