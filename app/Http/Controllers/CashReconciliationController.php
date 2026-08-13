<?php

namespace App\Http\Controllers;

use App\Models\FilialModel;
use App\Services\CashReconciliationService;
use Illuminate\Http\Request;

class CashReconciliationController extends Controller
{
    public function __construct(private readonly CashReconciliationService $reconciliation)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'filial_id' => ['required', 'integer', 'exists:filial,id'],
            'reconciliation_date' => ['required', 'date'],
            'actual_amount' => ['required', 'numeric', 'min:0'],
            'expected_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = $this->reconciliation->record(
            (int) $data['filial_id'],
            now()->parse($data['reconciliation_date']),
            (float) $data['actual_amount'],
            array_key_exists('expected_amount', $data) && $data['expected_amount'] !== null
                ? (float) $data['expected_amount']
                : null,
            $data['notes'] ?? null,
            $request->user(),
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $record], 201);
        }

        return redirect()->back()->with('success', 'Naqd pul reconciliation saqlandi.');
    }
}
