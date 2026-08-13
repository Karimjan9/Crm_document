<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Partner;
use App\Services\PackageProductService;
use App\Services\PartnerOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerOrderController extends Controller
{
    public function __construct(
        private readonly PartnerOrderService $orders,
        private readonly PackageProductService $products,
    ) {
    }

    public function index(Request $request)
    {
        $partner = $this->partner($request);
        $orders = $partner->orders()
            ->with(['client:id,name,phone_number,email', 'filial:id,name', 'partner:id,company_name,code', 'packageTemplate:id,name,product_code'])
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        $orders->setCollection($orders->getCollection()->map(fn (Order $order): array => $this->payload($order)));

        return response()->json($orders);
    }

    public function catalog(Request $request)
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'filial_id' => ['required', 'integer', 'exists:filial,id'],
        ]);
        $filialId = (int) $data['filial_id'];

        if (! $partner->canUseFilial($filialId)) {
            return response()->json(['message' => 'Bu filial partner uchun ruxsat etilmagan.'], 422);
        }

        return response()->json([
            'filial_id' => $filialId,
            'products' => $this->products->partnerCatalog($filialId)->values(),
        ]);
    }

    public function store(Request $request)
    {
        $partner = $this->partner($request);
        $data = $request->validate([
            'filial_id' => ['required', 'integer', 'exists:filial,id'],
            'client_name' => ['required', 'string', 'max:180'],
            'client_phone' => ['required', 'string', 'max:40'],
            'client_email' => ['nullable', 'email', 'max:180'],
            'package_template_id' => ['nullable', 'integer', 'exists:package_templates,id'],
            'package_variant' => ['nullable', Rule::in(PackageProductService::VARIANTS)],
            'package_addon_ids' => ['nullable', 'array', 'max:20'],
            'package_addon_ids.*' => ['integer', 'exists:service_addons,id'],
            'partner_reference' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'delivery_type' => ['nullable', Rule::in(['pickup', 'courier', 'digital', 'branch'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'promised_at' => ['nullable', 'date'],
        ]);
        $order = $this->orders->create($partner, $data);

        return response()->json($this->payload($order), 201);
    }

    public function show(Request $request, int $order)
    {
        return response()->json($this->payload($this->orders->ownedOrder($this->partner($request), $order)));
    }

    public function stats(Request $request)
    {
        $partner = $this->partner($request);
        $orders = $partner->orders();

        return response()->json([
            'partner' => ['code' => $partner->code, 'name' => $partner->company_name],
            'orders_total' => (clone $orders)->count(),
            'active_orders' => (clone $orders)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'month_total' => (float) (clone $orders)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'by_status' => (clone $orders)->select('status')->selectRaw('COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function invoices(Request $request)
    {
        $invoices = $this->partner($request)->invoices()
            ->with('lines:id,partner_invoice_id,order_id,order_code,description,subtotal_amount,discount_amount,total_amount')
            ->latest('period_end')
            ->paginate(25);
        $invoices->setCollection($invoices->getCollection()->map(fn ($invoice): array => $this->invoicePayload($invoice)));

        return response()->json($invoices);
    }

    private function partner(Request $request): Partner
    {
        $partner = $request->attributes->get('partner');
        abort_unless($partner instanceof Partner && $partner->status === 'active', 401);

        return $partner;
    }

    private function payload(Order $order): array
    {
        $trackingUrl = $order->partner
            ? route('orders.partner-track', [
                'partnerCode' => $order->partner->code,
                'trackingToken' => $order->tracking_token,
            ])
            : route('orders.portal', ['trackingToken' => $order->tracking_token]);

        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'partner_reference' => $order->partner_reference,
            'status' => $order->status,
            'billing_status' => $order->billing_status,
            'filial' => ['id' => $order->filial?->id, 'name' => $order->filial?->name],
            'client' => [
                'name' => $order->client?->name,
                'phone' => $order->client?->phone_number,
                'email' => $order->client?->email,
            ],
            'package' => $order->packageTemplate ? [
                'id' => $order->packageTemplate->id,
                'code' => $order->packageTemplate->product_code,
                'name' => $order->packageTemplate->name,
                'variant' => $order->package_variant,
            ] : null,
            'subtotal_amount' => (float) $order->subtotal_amount,
            'discount_amount' => (float) $order->discount_amount,
            'total_amount' => (float) $order->total_amount,
            'paid_amount' => (float) $order->paid_amount,
            'balance_amount' => $order->balance_amount,
            'tracking_url' => $trackingUrl,
            'created_at' => optional($order->created_at)->toISOString(),
        ];
    }

    private function invoicePayload($invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'period_start' => optional($invoice->period_start)->toDateString(),
            'period_end' => optional($invoice->period_end)->toDateString(),
            'issued_at' => optional($invoice->issued_at)->toISOString(),
            'due_at' => optional($invoice->due_at)->toDateString(),
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'subtotal_amount' => (float) $invoice->subtotal_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance_amount' => $invoice->balance_amount,
            'lines' => $invoice->lines->map(fn ($line): array => [
                'order_id' => $line->order_id,
                'order_code' => $line->order_code,
                'description' => $line->description,
                'subtotal_amount' => (float) $line->subtotal_amount,
                'discount_amount' => (float) $line->discount_amount,
                'total_amount' => (float) $line->total_amount,
            ])->values()->all(),
        ];
    }
}
