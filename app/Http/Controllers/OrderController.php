<?php

namespace App\Http\Controllers;

use App\Models\ClientsModel;
use App\Models\CashSession;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\OrderChecklist;
use App\Models\OrderDelivery;
use App\Models\PaymentsModel;
use App\Models\User;
use App\Services\OrderCaseService;
use App\Services\OrderPaymentLinkService;
use App\Services\PackageProductService;
use App\Services\PaymentLedgerService;
use App\Services\PricingService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderCaseService $orders,
        private readonly OrderPaymentLinkService $paymentLinks,
        private readonly PackageProductService $packageProducts,
        private readonly PaymentLedgerService $ledger,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()
            ->visibleTo($request->user())
            ->with([
                'client:id,name,phone_number',
                'filial:id,name',
                'createdBy:id,name',
                'responsibleUser:id,name',
                'packageTemplate:id,name,product_code',
                'documents:id,order_id,document_code,service_id,final_price,paid_amount,status_doc',
                'documents.service:id,name',
                'deliveries.courier:id,name',
            ])
            ->withCount(['documents', 'checklists']);

        if ($request->filled('status') && in_array($request->input('status'), Order::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($client) use ($search): void {
                        $client->where('name', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        $visibleQuery = Order::query()->visibleTo($request->user());

        $summary = [
            'count' => (clone $visibleQuery)->count(),
            'active' => (clone $visibleQuery)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'revenue' => (float) (clone $visibleQuery)->sum('total_amount'),
            'paid' => (float) (clone $visibleQuery)->sum('paid_amount'),
            'profit' => (float) (clone $visibleQuery)->sum('profit_amount'),
        ];

        return view('orders.index', [
            'orders' => $orders,
            'summary' => $summary,
            'statuses' => Order::STATUS_LABELS,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Order::class);

        $clientQuery = ClientsModel::query()
            ->visibleTo($request->user())
            ->with('filial:id,name')
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $clientQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $clients = $clientQuery->limit(200)->get(['id', 'name', 'phone_number', 'filial_id']);
        $selectedFilialId = $request->user()->filial_id ?: ($request->filled('filial_id') ? $request->integer('filial_id') : null);
        $filials = $request->user()->filial_id === null
            ? FilialModel::query()->orderBy('name')->get(['id', 'name'])
            : FilialModel::query()->whereKey($request->user()->filial_id)->get(['id', 'name']);
        $responsibleQuery = User::query()
            ->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['employee', 'admin_filial', 'admin_manager'])->where('guard_name', 'web'))
            ->orderBy('name');
        if ($request->user()->filial_id !== null) {
            $responsibleQuery->where('filial_id', $request->user()->filial_id);
        } else {
            $responsibleQuery->whereNotNull('filial_id');
        }
        $responsibles = $responsibleQuery->get(['id', 'name', 'filial_id']);

        $packageProductsByFilial = [];
        foreach ($filials as $filial) {
            $packageProductsByFilial[(string) $filial->id] = $this->packageProducts->catalog((int) $filial->id)->all();
        }

        return view('orders.create', [
            'clients' => $clients,
            'filials' => $filials,
            'selectedFilialId' => $selectedFilialId,
            'responsibles' => $responsibles,
            'packageProducts' => $this->packageProducts->catalog($selectedFilialId),
            'packageProductsByFilial' => $packageProductsByFilial,
            'search' => $request->input('q'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'filial_id' => [
                'nullable',
                'integer',
                'exists:filial,id',
                Rule::requiredIf(fn (): bool => $request->user()->filial_id === null),
            ],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'package_template_id' => ['nullable', 'integer', 'exists:package_templates,id'],
            'package_variant' => ['nullable', Rule::in(PackageProductService::VARIANTS)],
            'package_addon_ids' => ['nullable', 'array', 'max:20'],
            'package_addon_ids.*' => ['integer', 'exists:service_addons,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:80'],
            'customer_source' => ['nullable', 'string', 'max:80'],
            'delivery_type' => ['nullable', Rule::in(['pickup', 'courier', 'digital', 'branch'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'promised_at' => ['nullable', 'date'],
        ]);

        $filialId = (int) ($request->user()->filial_id ?: $data['filial_id']);
        $client = ClientsModel::query()
            ->visibleTo($request->user())
            ->findOrFail($data['client_id']);

        if ($client->filial_id !== null && (int) $client->filial_id !== $filialId) {
            return back()
                ->withInput()
                ->withErrors(['client_id' => 'Mijoz tanlangan filialga tegishli emas.']);
        }

        if ($client->filial_id === null) {
            $client->forceFill(['filial_id' => $filialId])->save();
        }

        $responsibleId = isset($data['responsible_user_id'])
            ? (int) $data['responsible_user_id']
            : ($request->user()->filial_id === $filialId
                ? (int) $request->user()->id
                : (int) (User::query()
                    ->where('filial_id', $filialId)
                    ->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['employee', 'admin_filial'])->where('guard_name', 'web'))
                    ->orderBy('id')
                    ->value('id')));

        if ($responsibleId > 0 && ! User::query()->whereKey($responsibleId)->where('filial_id', $filialId)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['responsible_user_id' => 'Mas’ul xodim tanlangan filialga tegishli emas.']);
        }

        $product = null;
        $quote = null;
        if (! empty($data['package_template_id'])) {
            $product = \App\Models\PackageTemplate::query()->findOrFail((int) $data['package_template_id']);
            $quote = $this->packageProducts->quote($product, $filialId, $data['package_variant'] ?? 'standard');
        }

        $order = DB::transaction(function () use ($client, $filialId, $request, $data, $responsibleId, $product, $quote): \App\Models\Order {
            $order = $this->orders->createForClient($client, $filialId, $request->user()->id, [
                'title' => ($data['title'] ?? null) ?: ($product?->name ?: 'Yangi buyurtma'),
                'description' => $data['description'] ?? null,
                'source' => $data['customer_source'] ?? ($data['source'] ?? null),
                'customer_source' => $data['customer_source'] ?? ($data['source'] ?? null),
                'priority' => $data['priority'],
                'responsible_user_id' => $responsibleId,
                'delivery_type' => $data['delivery_type'] ?? ($quote['delivery_type'] ?? 'pickup'),
                'promised_at' => $data['promised_at'] ?? ($quote ? now()->addDays($quote['deadline_days']) : null),
            ]);

            if ($product) {
                $order = $this->packageProducts->attachToOrder(
                    $order,
                    (int) $product->id,
                    $data['package_variant'] ?? 'standard',
                    $data['package_addon_ids'] ?? []
                );
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order muvaffaqiyatli yaratildi. Endi unga hujjatlar qo‘shishingiz mumkin.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'client',
            'filial',
            'createdBy',
            'responsibleUser',
            'packageTemplate.items.service',
            'packageTemplate.items.documentType',
            'packageTemplate.packageAddons.serviceAddon',
            'documents.service',
            'documents.documentType',
            'documents.files',
            'documents.checklists.completedBy',
            'documents.latestQaReview.reviewer',
            'documents.courierAssignment.courier',
            'priceLines.document',
            'payments.paidByAdmin',
            'payments.cashier',
            'payments.cashSession',
            'payments.refunds',
            'invoice.lines',
            'checklists.document',
            'checklists.completedBy',
            'statusHistories.changedBy',
            'deliveries.courier',
            'deliveries.assignedBy',
            'notifications',
            'paymentLinks',
            'costs.recordedBy',
        ]);

        $couriers = User::query()
            ->whereHas('roles', fn ($roles) => $roles->where('name', 'courier')->where('guard_name', 'web'))
            ->where('filial_id', $order->filial_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $cashSessions = CashSession::query()
            ->where('filial_id', $order->filial_id)
            ->where('status', 'open')
            ->whereDate('session_date', today()->toDateString())
            ->with('cashier:id,name')
            ->latest('id')
            ->get();

        return view('orders.show', [
            'order' => $order,
            'couriers' => $couriers,
            'cashSessions' => $cashSessions,
            'statuses' => Order::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->orders->updateStatus($order, $data['status'], $request->user(), $data['reason'] ?? null);

        return redirect()->back()->with('success', 'Order holati yangilandi.');
    }

    public function toggleChecklist(Request $request, Order $order, OrderChecklist $checklist)
    {
        $this->authorize('update', $order);
        abort_unless((int) $checklist->order_id === (int) $order->id, 404);

        $data = $request->validate([
            'is_completed' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->orders->toggleChecklist(
            $checklist,
            (bool) $data['is_completed'],
            $request->user(),
            $data['notes'] ?? null
        );

        return redirect()->back()->with('success', 'Checklist yangilandi.');
    }

    public function recordPayment(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_type' => ['required', Rule::in(PaymentsModel::TYPES)],
            'document_id' => ['nullable', 'integer', 'exists:documents,id'],
            'cash_session_id' => ['nullable', 'integer', 'exists:cash_sessions,id'],
            'online_transaction_id' => ['nullable', 'string', 'max:160'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->orders->recordPayment(
            $order,
            (float) $data['amount'],
            $data['payment_type'],
            $request->user(),
            isset($data['document_id']) ? (int) $data['document_id'] : null,
            [
                'cash_session_id' => isset($data['cash_session_id']) ? (int) $data['cash_session_id'] : null,
                'online_transaction_id' => $data['online_transaction_id'] ?? null,
                'payment_proof' => $request->file('payment_proof'),
            ]
        );

        return redirect()->back()->with('success', 'Order to‘lovi saqlandi.');
    }

    public function createPaymentLink(Order $order)
    {
        $this->authorize('update', $order);
        $link = $this->paymentLinks->ensure($order->fresh());

        return redirect()->back()->with(
            $link ? 'success' : 'error',
            $link ? 'Payment link yaratildi: ' . $link->url : 'To‘lov qoldig‘i mavjud emas.'
        );
    }

    public function notifyCustomer(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'channel' => ['required', Rule::in(['sms', 'telegram', 'email', 'whatsapp', 'internal'])],
            'recipient' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->orders->queueCustomerNotification(
            $order,
            $data['channel'],
            $data['message'],
            $data['recipient'] ?? null
        );

        return redirect()->back()->with('success', 'Customer notification queue’ga qo‘shildi.');
    }

    public function assignDelivery(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'courier_id' => ['required', 'integer', 'exists:users,id'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:1000'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $courier = User::query()
            ->whereHas('roles', fn ($roles) => $roles->where('name', 'courier')->where('guard_name', 'web'))
            ->where('filial_id', $order->filial_id)
            ->findOrFail($data['courier_id']);

        $pricingContext = [
            'variant' => 'standard',
            'filial_id' => (int) $order->filial_id,
            'as_of' => now(),
        ];
        $hasManualFee = array_key_exists('fee', $data) && $data['fee'] !== null && $data['fee'] !== '';
        $courierQuote = app(PricingService::class)->resolveFixed(
            'courier',
            null,
            'courier',
            'Courier delivery',
            0,
            0,
            (int) $order->filial_id,
            $pricingContext,
        );
        $deliveryFee = $hasManualFee ? round((float) $data['fee'], 2) : (float) $courierQuote['price'];

        $delivery = $order->deliveries()->create([
            'courier_id' => $courier->id,
            'assigned_by_id' => $request->user()->id,
            'status' => 'sent',
            'tracking_code' => 'DLV-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10)),
            'recipient_name' => $data['recipient_name'] ?? $order->client?->name,
            'recipient_phone' => $data['recipient_phone'] ?? $order->client?->phone_number,
            'address' => $data['address'],
            'fee' => $deliveryFee,
            'notes' => $data['notes'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
        $order->forceFill(['delivery_type' => 'courier'])->save();

        $order->priceLines()->where('line_type', 'delivery')->delete();
        if ((float) $delivery->fee > 0) {
            $order->priceLines()->create([
                'line_type' => 'delivery',
                'pricing_line_type' => 'courier',
                'price_tariff_id' => $hasManualFee ? null : $courierQuote['tariff_id'],
                'name' => 'Courier delivery',
                'quantity' => 1,
                'unit_price' => (float) $delivery->fee,
                'total_price' => (float) $delivery->fee,
                'metadata' => [
                    'tracking_code' => $delivery->tracking_code,
                    'pricing_source' => $hasManualFee ? 'manual' : $courierQuote['source'],
                    'effective_to' => $courierQuote['effective_to'] ?? null,
                ],
                'pricing_context' => $hasManualFee
                    ? [...$pricingContext, 'source' => 'manual']
                    : [...$courierQuote['context'], 'source' => 'tariff'],
                'effective_from' => $hasManualFee ? null : $courierQuote['effective_from'],
            ]);
        }
        $this->orders->recalculate($order);

        $this->orders->updateStatus($order, 'courier_sent', $request->user(), 'Order kuryerga biriktirildi.');
        $this->orders->createNotification($order, 'delivery_assigned', 'Buyurtma kuryerga berildi: ' . $delivery->tracking_code);

        return redirect()->back()->with('success', 'Buyurtma kuryerga biriktirildi.');
    }

    public function recordCost(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->costs()->create([
            'category' => $data['category'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'recorded_by_id' => $request->user()->id,
        ]);
        $this->orders->recalculate($order);

        return redirect()->back()->with('success', 'Order xarajati qo‘shildi, foyda qayta hisoblandi.');
    }

    public function invoice(Order $order)
    {
        $this->authorize('view', $order);
        $this->ledger->issueInvoice($order, auth()->user());
        $order->load(['client', 'filial', 'partner', 'responsibleUser', 'packageTemplate', 'documents.service', 'priceLines', 'payments', 'invoice']);

        return view('orders.invoice', compact('order'));
    }

    public function qr(Order $order)
    {
        $this->authorize('view', $order);

        $renderer = new ImageRenderer(
            new RendererStyle(300, 12),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $trackingUrl = $order->partner
            ? route('orders.partner-track', [
                'partnerCode' => $order->partner->code,
                'trackingToken' => $order->tracking_token,
            ])
            : route('orders.portal', ['trackingToken' => $order->tracking_token]);
        $svg = $writer->writeString($trackingUrl);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function track(string $trackingToken)
    {
        return app(\App\Http\Controllers\CustomerPortalController::class)->show($trackingToken);
    }
}
