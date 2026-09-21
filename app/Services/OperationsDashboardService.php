<?php

namespace App\Services;

use App\Models\BusinessApproval;
use App\Models\DocumentsModel;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Builder;

class OperationsDashboardService
{
    public function __construct(private readonly DeadlineRadarService $radar) {}

    public function build(User $user): array
    {
        $tasks = WorkItem::query()->visibleTo($user)->open()->with(['order:id,order_code', 'lead:id,name,phone'])->orderByRaw('due_at IS NULL, due_at')->limit(20)->get();
        $orders = Order::query()->visibleTo($user)->with(['client:id,name', 'documents.files', 'checklists', 'deliveries', 'statusHistories'])->whereNotIn('status', ['completed', 'cancelled', 'delivered'])->get();
        $riskOrders = $orders->map(fn (Order $order) => ['order' => $order, 'risk' => $this->radar->assess($order)])
            ->filter(fn (array $row) => in_array($row['risk']['level'], ['yellow', 'red'], true))->sortByDesc(fn (array $row) => $row['risk']['score'])->take(12)->values();
        $readyWithDebt = $orders->filter(fn (Order $order) => in_array($order->status, ['ready_for_delivery', 'delivered'], true) && $order->balance_amount > 0)->take(12)->values();
        $qaQueue = DocumentsModel::query()->whereIn('status_doc', ['waiting_review', 'qa_review'])->when(! $user->hasAnyRole(['super_admin', 'admin_manager']), fn (Builder $q) => $q->where('filial_id', $user->filial_id))->when($user->hasRole('employee'), fn (Builder $q) => $q->where('qa_user_id', $user->id))->with('order:id,order_code')->latest('updated_at')->limit(12)->get();
        $leadFollowUps = Lead::query()->visibleTo($user)->whereNotIn('status', ['won', 'lost'])->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<=', now()->addDay())->orderBy('next_follow_up_at')->limit(12)->get();
        $paymentReview = PaymentsModel::query()->where('status', 'pending')->when(! $user->hasAnyRole(['super_admin', 'admin_manager']), fn (Builder $q) => $q->where('filial_id', $user->filial_id))->with('order:id,order_code')->latest('id')->limit(12)->get();
        $approvals = $user->hasAnyRole(['super_admin', 'admin_manager']) ? BusinessApproval::query()->where('status', 'pending')->with('subject')->latest()->limit(12)->get() : collect();

        return compact('tasks', 'riskOrders', 'readyWithDebt', 'qaQueue', 'leadFollowUps', 'paymentReview', 'approvals') + [
            'counts' => [
                'tasks' => $tasks->count(), 'risk' => $riskOrders->count(), 'debt' => $readyWithDebt->count(),
                'qa' => $qaQueue->count(), 'leads' => $leadFollowUps->count(), 'approvals' => $approvals->count(),
            ],
        ];
    }
}
