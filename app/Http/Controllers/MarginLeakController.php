<?php

namespace App\Http\Controllers;

use App\Models\MarginLeak;
use App\Models\FilialModel;
use App\Services\MarginLeakDetectorService;
use Illuminate\Http\Request;

class MarginLeakController extends Controller
{
    public function __construct(private readonly MarginLeakDetectorService $detector)
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);
        $leaks = $this->detector->open($data['filial_id'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['data' => $leaks]);
        }

        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.finance.margin-leaks', compact('leaks', 'filials'));
    }

    public function scan(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);
        $from = now()->parse($data['date_from'] ?? today()->startOfMonth()->toDateString())->startOfDay();
        $to = now()->parse($data['date_to'] ?? today()->toDateString())->endOfDay();
        $this->detector->scan($from, $to, $data['filial_id'] ?? null);

        return redirect()->route('finance.margin-leaks.index')->with('success', 'Margin leak tekshiruvi bajarildi.');
    }

    public function resolve(MarginLeak $marginLeak)
    {
        $this->detector->resolve($marginLeak);

        return redirect()->back()->with('success', 'Signal yopildi.');
    }
}
