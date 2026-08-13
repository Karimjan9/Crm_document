<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Services\OrderCaseService;
use App\Services\PaymentLedgerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FinanceLedgerController extends Controller
{
    public function __construct(
        private readonly PaymentLedgerService $ledger,
        private readonly OrderCaseService $orders,
    ) {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
            'status' => ['nullable', Rule::in(PaymentsModel::STATUSES)],
            'payment_type' => ['nullable', Rule::in(PaymentsModel::TYPES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $filialId = $this->filterFilial($request->user(), $data['filial_id'] ?? null);
        $query = PaymentsModel::query()
            ->with([
                'filial:id,name,code',
                'order:id,order_code,filial_id,client_id',
                'order.client:id,name,phone_number',
                'document:id,document_code,order_id,filial_id',
                'cashier:id,name',
                'cashSession:id,session_date,status',
            ])
            ->when($filialId, fn (Builder $builder) => $this->scopeFilial($builder, $filialId))
            ->when($data['date_from'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('created_at', '>=', $date))
            ->when($data['date_to'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('created_at', '<=', $date))
            ->when($data['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($data['payment_type'] ?? null, fn (Builder $builder, string $type) => $builder->where('payment_type', $type))
            ->when($data['q'] ?? null, function (Builder $builder, string $search): void {
                $builder->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('receipt_number', 'like', '%' . $search . '%')
                        ->orWhere('online_transaction_id', 'like', '%' . $search . '%')
                        ->orWhereHas('order', fn (Builder $order) => $order->where('order_code', 'like', '%' . $search . '%'))
                        ->orWhereHas('document', fn (Builder $document) => $document->where('document_code', 'like', '%' . $search . '%'));
                });
            });

        $summaryQuery = clone $query;
        $summary = [
            'gross' => round((float) $summaryQuery->sum('amount'), 2),
            'effective' => $this->ledger->effectiveSum(clone $query),
            'refunds' => round((float) (clone $query)->sum('refund_amount'), 2),
            'count' => (clone $query)->count(),
            'pending' => (clone $query)->whereIn('status', ['pending'])->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $payments = $query->latest('id')->paginate(30)->withQueryString();
        $debtQuery = Order::query()
            ->visibleTo($request->user())
            ->whereNotIn('status', ['cancelled'])
            ->whereRaw('COALESCE(total_amount, 0) > COALESCE(paid_amount, 0)')
            ->with(['client:id,name,phone_number', 'filial:id,name'])
            ->when($filialId, fn (Builder $builder) => $builder->where('filial_id', $filialId));
        $debt = (clone $debtQuery)->orderByDesc('created_at')->limit(100)->get();
        $debtTotal = round((float) (clone $debtQuery)
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) AS debt_total')
            ->value('debt_total'), 2);

        $filials = $request->user()->hasAnyRole(['super_admin', 'admin_manager'])
            ? FilialModel::query()->orderBy('name')->get(['id', 'name', 'code'])
            : FilialModel::query()->whereKey($request->user()->filial_id)->get(['id', 'name', 'code']);
        $sessions = CashSession::query()
            ->with(['filial:id,name', 'cashier:id,name'])
            ->when($filialId, fn (Builder $builder) => $builder->where('filial_id', $filialId))
            ->latest('session_date')
            ->latest('id')
            ->limit(30)
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $payments,
                'summary' => $summary,
                'debt' => $debt,
                'debt_total' => $debtTotal,
                'cash_sessions' => $sessions,
            ]);
        }

        return view('admin.finance.ledger', compact('payments', 'summary', 'debt', 'debtTotal', 'filials', 'sessions', 'data'));
    }

    public function confirm(Request $request, PaymentsModel $payment)
    {
        $this->assertPaymentAccess($request, $payment);
        $this->ledger->confirm($payment, $request->user(), $request->input('reason'));
        $this->syncOrder($payment);

        return redirect()->back()->with('success', 'To‘lov tasdiqlandi.');
    }

    public function cancel(Request $request, PaymentsModel $payment)
    {
        $this->assertPaymentAccess($request, $payment);
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $this->ledger->cancel($payment, $request->user(), $data['reason']);
        $this->syncOrder($payment);

        return redirect()->back()->with('success', 'To‘lov bekor qilindi, ledger saqlandi.');
    }

    public function refund(Request $request, PaymentsModel $payment)
    {
        $this->assertPaymentAccess($request, $payment);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $refund = $this->ledger->refund($payment, (float) $data['amount'], $request->user(), $data['reason']);
        $this->syncOrder($payment);

        return redirect()->back()->with('success', 'Refund saqlandi: ' . $refund->refund_number);
    }

    public function proof(Request $request, PaymentsModel $payment)
    {
        $this->assertPaymentAccess($request, $payment);
        abort_unless($payment->payment_proof_path, 404);
        $disk = Storage::disk('private');
        abort_unless($disk->exists($payment->payment_proof_path), 404);

        return $disk->download(
            $payment->payment_proof_path,
            $payment->payment_proof_original_name ?: basename($payment->payment_proof_path)
        );
    }

    public function openSession(Request $request)
    {
        $data = $request->validate([
            'filial_id' => ['required', 'integer', 'exists:filial,id'],
            'session_date' => ['nullable', 'date'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);
        $filialId = $this->filterFilial($request->user(), (int) $data['filial_id']);
        abort_unless($filialId, 403);
        $session = $this->ledger->openCashSession(
            $filialId,
            $request->user(),
            (float) $data['opening_balance'],
            isset($data['session_date']) ? now()->parse($data['session_date']) : null,
        );

        return redirect()->back()->with('success', 'Cash session ochildi: #' . $session->id);
    }

    public function closeSession(Request $request, CashSession $cashSession)
    {
        $this->assertSessionAccess($request, $cashSession);
        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $session = $this->ledger->closeCashSession($cashSession, (float) $data['actual_cash'], $request->user(), $data['notes'] ?? null);

        return redirect()->back()->with('success', 'Kassa yopildi. Farq: ' . number_format((float) $session->variance, 2, '.', ' '));
    }

    public function reconcileSession(Request $request, CashSession $cashSession)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin_manager']), 403);
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);
        $this->ledger->reconcileCashSession($cashSession, $request->user(), $data['notes'] ?? null);

        return redirect()->back()->with('success', 'Cash session reconciliation yakunlandi.');
    }

    private function syncOrder(PaymentsModel $payment): void
    {
        $payment->loadMissing(['order', 'document']);
        if ($payment->order) {
            $this->orders->recalculate($payment->order);
        }
    }

    private function assertPaymentAccess(Request $request, PaymentsModel $payment): void
    {
        $payment->loadMissing(['order', 'document']);
        $filialId = $payment->filial_id ?: $payment->order?->filial_id ?: $payment->document?->filial_id;
        if ($request->user()->hasAnyRole(['super_admin', 'admin_manager'])) {
            return;
        }

        abort_unless($request->user()->hasRole('admin_filial') && (int) $request->user()->filial_id === (int) $filialId, 403);
    }

    private function assertSessionAccess(Request $request, CashSession $session): void
    {
        abort_unless(
            $request->user()->hasAnyRole(['super_admin', 'admin_manager'])
                || ($request->user()->hasRole('admin_filial') && (int) $request->user()->filial_id === (int) $session->filial_id),
            403
        );
    }

    private function filterFilial($user, ?int $requested): ?int
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return $requested;
        }

        return $user->filial_id ? (int) $user->filial_id : null;
    }

    private function scopeFilial(Builder $query, int $filialId): Builder
    {
        return $query->where(function (Builder $scope) use ($filialId): void {
            $scope->where('filial_id', $filialId)
                ->orWhereHas('order', fn (Builder $order) => $order->where('filial_id', $filialId))
                ->orWhereHas('document', fn (Builder $document) => $document->where('filial_id', $filialId));
        });
    }
}
