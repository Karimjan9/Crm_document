<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilialModel;
use App\Models\Partner;
use App\Models\PriceTariff;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingTariffController extends Controller
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $tariffs = PriceTariff::query()
            ->with(['service:id,name', 'serviceAddon:id,name', 'filial:id,name', 'partner:id,company_name'])
            ->when($request->filled('line_type'), fn ($query) => $query->where('line_type', $request->input('line_type')))
            ->when($request->filled('variant'), fn ($query) => $query->where('variant', $request->input('variant')))
            ->when($request->filled('filial_id'), fn ($query) => $query->where('filial_id', (int) $request->input('filial_id')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = trim((string) $request->input('q'));
                $query->where(function ($builder) use ($q): void {
                    $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('price_key', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pricing.index', [
            'tariffs' => $tariffs,
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'lineTypes' => PriceTariff::LINE_TYPES,
            'variants' => PriceTariff::VARIANTS,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAccess($request);

        $filialId = $request->integer('filial_id');
        $filialId = $filialId > 0 && FilialModel::query()->whereKey($filialId)->exists() ? $filialId : null;

        return view('admin.pricing.form', $this->formData(new PriceTariff([
            'line_type' => 'base_service',
            'variant' => 'standard',
            'filial_id' => $filialId,
            'effective_from' => now()->startOfDay(),
            'currency' => config('pricing.default_currency', 'UZS'),
            'is_active' => true,
        ]), false));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->rules());
        $this->pricing->createTariff($this->tariffData($data));

        return redirect()->route('admin.pricing.index')->with('success', 'Tarif versiyasi yaratildi. Eski tariflar o‘zgartirilmadi.');
    }

    public function edit(Request $request, PriceTariff $pricing)
    {
        $this->authorizeAccess($request);

        return view('admin.pricing.form', $this->formData($pricing, true));
    }

    public function update(Request $request, PriceTariff $pricing)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->rules());
        $attributes = $this->tariffData($data);

        // A tariff that has already been used is immutable. Deactivating it
        // is safe; changing its price would corrupt historical explanations.
        if ($pricing->orderPriceLines()->exists()) {
            $pricing->update(['is_active' => (bool) ($attributes['is_active'] ?? false)]);
        } else {
            $this->pricing->updateTariff($pricing, $attributes);
        }

        return redirect()->route('admin.pricing.index')->with('success', 'Tarif yangilandi. Ishlatilgan tarif narxi o‘zgartirilmaydi.');
    }

    private function formData(PriceTariff $tariff, bool $isEdit): array
    {
        return [
            'tariff' => $tariff,
            'isEdit' => $isEdit,
            'services' => ServicesModel::query()->orderBy('name')->get(['id', 'name']),
            'addons' => ServiceAddonModel::query()->with('service:id,name')->orderBy('name')->get(['id', 'service_id', 'name']),
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'partners' => Partner::query()->active()->orderBy('company_name')->get(['id', 'company_name']),
            'lineTypes' => PriceTariff::LINE_TYPES,
            'variants' => PriceTariff::VARIANTS,
        ];
    }

    private function rules(): array
    {
        return [
            'line_type' => ['required', Rule::in(PriceTariff::LINE_TYPES)],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'service_addon_id' => ['nullable', 'integer', 'exists:service_addons,id'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'price_key' => ['nullable', 'string', 'max:100'],
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'variant' => ['required', Rule::in(PriceTariff::VARIANTS)],
            'season_code' => ['nullable', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_amount' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function tariffData(array $data): array
    {
        return [
            ...$data,
            'currency' => strtoupper($data['currency']),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'priority' => (int) ($data['priority'] ?? 0),
        ];
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['super_admin', 'admin_manager']), 403);
    }
}
