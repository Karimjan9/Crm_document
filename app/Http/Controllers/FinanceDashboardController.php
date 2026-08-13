<?php

namespace App\Http\Controllers;

use App\Services\FinanceDashboardService;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    public function __construct(private readonly FinanceDashboardService $finance)
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);
        $dashboard = $this->finance->build($request->user(), $data);

        if ($request->expectsJson()) {
            return response()->json($dashboard);
        }

        return view('admin.finance.dashboard', compact('dashboard'));
    }
}
