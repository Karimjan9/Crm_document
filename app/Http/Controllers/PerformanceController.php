<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use App\Models\DocumentsModel;
use App\Models\Order;
use App\Models\PerformanceTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin_manager', 'admin_filial']), 403);
        $from = Carbon::parse($request->input('from', today()->startOfMonth()))->startOfDay(); $to = Carbon::parse($request->input('to', today()))->endOfDay();
        $users = User::query()->whereNotNull('filial_id')->with('roles')->get();
        $rows = $users->map(function (User $user) use ($from, $to) {
            $orders = Order::query()->where('responsible_user_id', $user->id)->whereBetween('created_at', [$from, $to])->whereNot('status', 'cancelled')->get();
            $documents = DocumentsModel::query()->where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assigned_to_id', $user->id)->orWhere('qa_user_id', $user->id); })->whereBetween('created_at', [$from, $to])->get();
            $rework = (int) $documents->sum('rework_count'); $completed = $documents->whereIn('status_doc', ['finish', 'completed', 'delivered'])->count();
            $feedback = CustomerFeedback::query()->whereIn('order_id', $orders->modelKeys())->avg('rating');
            $target = PerformanceTarget::query()->where('user_id', $user->id)->whereDate('period_start', '<=', $to)->whereDate('period_end', '>=', $from)->latest()->first();
            $revenue = (float) $orders->sum('total_amount'); $targetProgress = $target && (float) $target->revenue_target > 0 ? round($revenue / (float) $target->revenue_target * 100, 1) : null;
            $score = round(min(100, ($targetProgress ?? 50) * .30 + min(100, $completed ? 100 - ($rework / $completed * 20) : 0) * .30 + (($feedback ?: 3) / 5 * 100) * .20 + 80 * .20), 1);
            return compact('user', 'orders', 'completed', 'rework', 'feedback', 'target', 'revenue', 'targetProgress', 'score');
        })->sortByDesc('score')->values();
        return view('operations.performance', compact('rows', 'from', 'to'));
    }
}
