<?php

namespace App\Http\Controllers;

use App\Models\WorkItem;
use App\Services\OperationsDashboardService;
use Illuminate\Http\Request;

class OperationsDashboardController extends Controller
{
    public function __construct(private readonly OperationsDashboardService $dashboard) {}
    public function index(Request $request)
    {
        $data = $this->dashboard->build($request->user());
        return $request->expectsJson() ? response()->json($data) : view('operations.dashboard', $data);
    }
    public function complete(Request $request, WorkItem $workItem)
    {
        abort_unless(WorkItem::query()->visibleTo($request->user())->whereKey($workItem->id)->exists(), 403);
        $workItem->forceFill(['status' => 'done', 'completed_at' => now()])->save();
        return back()->with('success', 'Ish bajarilgan deb belgilandi.');
    }
}
