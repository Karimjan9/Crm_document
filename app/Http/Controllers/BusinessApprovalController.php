<?php

namespace App\Http\Controllers;

use App\Models\BusinessApproval;
use App\Services\BusinessApprovalService;
use Illuminate\Http\Request;

class BusinessApprovalController extends Controller
{
    public function __construct(private readonly BusinessApprovalService $approvals) {}
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin_manager']), 403);
        $approvals = BusinessApproval::query()->with(['requestedBy:id,name', 'resolvedBy:id,name'])->latest()->paginate(40);
        return view('operations.approvals', compact('approvals'));
    }
    public function resolve(Request $request, BusinessApproval $approval)
    {
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'note' => ['nullable', 'string', 'max:2000']]);
        $this->approvals->resolve($approval, $request->user(), $data['decision'] === 'approved', $data['note'] ?? null);
        return back()->with('success', 'Tasdiq so‘rovi ko‘rib chiqildi.');
    }
}
