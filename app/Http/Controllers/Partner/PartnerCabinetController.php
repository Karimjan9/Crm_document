<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Partner;
use App\Services\PackageProductService;
use App\Services\PartnerApiKeyService;
use App\Services\PartnerOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PartnerCabinetController extends Controller
{
    public function __construct(
        private readonly PackageProductService $products,
        private readonly PartnerOrderService $partnerOrders,
        private readonly PartnerApiKeyService $apiKeys,
    ) {
    }

    public function dashboard(Request $request)
    {
        $partner = $this->partner($request);
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $orders = $partner->orders();

        $stats = [
            'orders' => (clone $orders)->count(),
            'active' => (clone $orders)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'month_total' => (float) (clone $orders)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount'),
            'outstanding' => (float) $partner->invoices()->whereIn('status', ['issued', 'partially_paid'])->get()->sum('balance_amount'),
        ];

        return view('partner.dashboard', [
            'partner' => $partner->load(['filials', 'apiKeys' => fn ($query) => $query->latest()]),
            'stats' => $stats,
            'recentOrders' => $partner->orders()->with(['client:id,name,phone_number', 'filial:id,name'])->latest()->limit(10)->get(),
            'statusStats' => $partner->orders()->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get(),
        ]);
    }

    public function orders(Request $request)
    {
        $partner = $this->partner($request);
        $query = $partner->orders()->with([
            'client:id,name,phone_number',
            'filial:id,name',
            'packageTemplate:id,name,product_code',
        ]);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('order_code', 'like', "%{$search}%")
                    ->orWhere('partner_reference', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'like', "%{$search}%")->orWhere('phone_number', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status') && in_array($request->input('status'), Order::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        return view('partner.orders.index', [
            'partner' => $partner,
            'orders' => $query->latest('id')->paginate(20)->withQueryString(),
            'statuses' => Order::STATUS_LABELS,
        ]);
    }

    public function createOrder(Request $request)
    {
        $partner = $this->partner($request);
        $filials = $partner->filials()->wherePivot('is_active', true)->orderBy('name')->get(['filial.id', 'filial.name']);
        $selectedFilialId = (int) ($request->integer('filial_id') ?: $filials->first()?->id);
        $catalogByFilial = [];
        foreach ($filials as $filial) {
            $catalogByFilial[(string) $filial->id] = $this->products->partnerCatalog((int) $filial->id)->all();
        }

        return view('partner.orders.create', [
            'partner' => $partner,
            'filials' => $filials,
            'selectedFilialId' => $selectedFilialId,
            'catalogByFilial' => $catalogByFilial,
            'products' => $catalogByFilial[(string) $selectedFilialId] ?? [],
        ]);
    }

    public function storeOrder(Request $request)
    {
        $partner = $this->partner($request);
        $data = $request->validate($this->orderRules());
        $order = $this->partnerOrders->create($partner, $data, $request->user());

        return redirect()->route('partner.orders.show', $order)->with('success', 'Partner order yaratildi.');
    }

    public function showOrder(Request $request, int $order)
    {
        $partner = $this->partner($request);

        return view('partner.orders.show', [
            'partner' => $partner,
            'order' => $this->partnerOrders->ownedOrder($partner, $order),
        ]);
    }

    public function invoices(Request $request)
    {
        $partner = $this->partner($request);

        return view('partner.invoices.index', [
            'partner' => $partner,
            'invoices' => $partner->invoices()->latest('period_end')->paginate(20),
        ]);
    }

    public function showInvoice(Request $request, int $invoice)
    {
        $partner = $this->partner($request);
        $invoice = $partner->invoices()->with('lines.order')->findOrFail($invoice);

        return view('partner.invoices.show', compact('partner', 'invoice'));
    }

    public function createApiKey(Request $request)
    {
        $this->assertPartnerAdmin($request);
        $partner = $this->partner($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:1095'],
        ]);
        $created = $this->apiKeys->create($partner, $data['name'], $data['expires_in_days'] ?? null);

        return redirect()->route('partner.dashboard')->with('newApiKey', $created['token']);
    }

    public function revokeApiKey(Request $request, int $apiKey)
    {
        $this->assertPartnerAdmin($request);
        $partner = $this->partner($request);
        $key = $partner->apiKeys()->findOrFail($apiKey);
        $this->apiKeys->revoke($key);

        return redirect()->route('partner.dashboard')->with('success', 'API key bekor qilindi.');
    }

    public function updateBranding(Request $request)
    {
        $this->assertPartnerAdmin($request);
        $partner = $this->partner($request);
        $data = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:180'],
            'brand_logo_url' => ['nullable', 'url', 'max:500'],
            'brand_primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'tracking_title' => ['nullable', 'string', 'max:180'],
        ]);
        $partner->forceFill($data)->save();

        return redirect()->route('partner.dashboard')->with('success', 'White-label tracking sozlamalari saqlandi.');
    }

    private function partner(Request $request): Partner
    {
        $partner = $request->user()?->partner;
        abort_unless($partner && $partner->status === 'active', 403);

        return $partner;
    }

    private function assertPartnerAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('partner_admin'), 403);
    }

    private function orderRules(): array
    {
        return [
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
        ];
    }
}
