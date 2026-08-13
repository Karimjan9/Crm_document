<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\PackageTemplate;
use App\Models\FilialModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Support\PackageTemplateSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PackageTemplateController extends Controller
{
    public function index()
    {
        $templates = PackageTemplate::query()
            ->whereHas('items')
            ->with(array_merge($this->itemRelations(), [
                'packageFilials.filial:id,name',
                'packageAddons.serviceAddon:id,name,price,deadline',
            ]))
            ->ordered()
            ->get();

        $templatePayloads = PackageTemplateSupport::buildSelectionPayloads($templates);

        return view('super_admin.package_templates.index', compact('templates', 'templatePayloads'));
    }

    public function create()
    {
        return view('super_admin.package_templates.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        if ((float) $validated['template']['promo_price'] > (float) $validated['template']['standard_price']) {
            return redirect()
                ->back()
                ->withErrors(['promo_price' => "Aksiya narxi umumiy summadan katta bo'lishi mumkin emas."])
                ->withInput();
        }

        $template = PackageTemplate::create($validated['template']);
        $template->items()->createMany($validated['items']);
        $this->syncProductRelations($template, $validated);

        return redirect()
            ->route('superadmin.template_package.index')
            ->with('success', 'Shablon muvaffaqiyatli yaratildi.');
    }

    public function edit(PackageTemplate $templatePackage)
    {
        $templatePackage->load(array_merge($this->itemRelations(), [
            'packageFilials',
            'packageAddons.serviceAddon',
        ]));

        return view('super_admin.package_templates.edit', $this->formData([
            'templatePackage' => $templatePackage,
        ]));
    }

    public function update(Request $request, PackageTemplate $templatePackage)
    {
        $validated = $this->validatePayload($request);

        if ((float) $validated['template']['promo_price'] > (float) $validated['template']['standard_price']) {
            return redirect()
                ->back()
                ->withErrors(['promo_price' => "Aksiya narxi umumiy summadan katta bo'lishi mumkin emas."])
                ->withInput();
        }

        $templatePackage->update($validated['template']);
        $templatePackage->items()->delete();
        $templatePackage->items()->createMany($validated['items']);
        $this->syncProductRelations($templatePackage, $validated);

        return redirect()
            ->route('superadmin.template_package.index')
            ->with('success', 'Shablon yangilandi.');
    }

    public function destroy(PackageTemplate $templatePackage)
    {
        $templatePackage->delete();

        return redirect()
            ->route('superadmin.template_package.index')
            ->with('success', "Shablon o'chirildi.");
    }

    protected function formData(array $extra = []): array
    {
        $documentTypes = DocumentTypeModel::query()->orderBy('name')->get(['id', 'name']);
        $directions = DirectionTypeModel::query()->orderBy('name')->get(['id', 'name']);
        $services = ServicesModel::query()->orderBy('name')->get(['id', 'name', 'price', 'deadline']);
        $filials = FilialModel::query()->orderBy('name')->get(['id', 'name', 'code']);
        $serviceAddons = ServiceAddonModel::query()->orderBy('name')->get(['id', 'service_id', 'name', 'price', 'deadline', 'description']);
        $documentAddons = DocumentTypeAdditionModel::query()->orderBy('name')->get(['id', 'document_type_id', 'name', 'amount', 'day', 'description']);
        $directionAddons = DocumentDirectionAdditionModel::query()->orderBy('name')->get(['id', 'document_direction_id', 'name', 'amount', 'day', 'description']);
        $apostilStatics = ApostilStatikModel::query()->orderBy('group_id')->orderBy('name')->get(['id', 'group_id', 'name', 'price', 'days']);
        $consuls = ConsulModel::query()->orderBy('name')->get(['id', 'name', 'amount', 'day']);
        $consulateTypes = ConsulationTypeModel::query()->orderBy('name')->get(['id', 'name', 'amount', 'day']);

        return array_merge([
            'documentTypes' => $documentTypes,
            'directions' => $directions,
            'services' => $services,
            'filials' => $filials,
            'serviceAddons' => $serviceAddons,
            'documentAddons' => $documentAddons,
            'directionAddons' => $directionAddons,
            'apostilStatics' => $apostilStatics,
            'consuls' => $consuls,
            'consulateTypes' => $consulateTypes,
        ], $extra);
    }

    protected function validatePayload(Request $request): array
    {
        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_sellable'] = $request->has('is_sellable')
            ? $request->boolean('is_sellable')
            : true;
        $data['items_payload'] = $this->decodeItemsPayload($request->input('items_payload'));
        $data['filial_ids'] = array_values(array_filter(array_map('intval', (array) $request->input('filial_ids', []))));
        $data['additional_addon_ids'] = array_values(array_filter(array_map('intval', (array) $request->input('additional_addon_ids', []))));

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:64',
            'highlight' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'promo_price' => 'nullable|numeric|min:0',
            'standard_price' => 'nullable|numeric|min:0',
            'express_price' => 'nullable|numeric|min:0',
            'standard_deadline_days' => 'nullable|integer|min:0|max:3650',
            'express_deadline_days' => 'nullable|integer|min:0|max:3650',
            'margin_percent' => 'nullable|numeric|min:0|max:100',
            'delivery_type' => ['nullable', Rule::in(['pickup', 'courier', 'digital', 'branch'])],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'filial_ids' => 'array',
            'filial_ids.*' => 'integer|exists:filial,id',
            'additional_addon_ids' => 'array',
            'additional_addon_ids.*' => 'integer|exists:service_addons,id',
            'items_payload' => 'required|array|min:1',
        ]);

        $validator->after(function ($validator) use ($data) {
            $standardPrice = (float) ($data['standard_price'] ?? $data['promo_price'] ?? 0);
            $expressPrice = (float) ($data['express_price'] ?? $standardPrice);
            if ($expressPrice > 0 && $standardPrice > 0 && $expressPrice < $standardPrice) {
                $validator->errors()->add('express_price', 'Express narx standard narxdan past bo‘lishi mumkin emas.');
            }

            foreach (($data['items_payload'] ?? []) as $index => $item) {
                $itemValidator = Validator::make($item, [
                    'document_type_id' => 'required|exists:document_type,id',
                    'service_id' => 'required|exists:services,id',
                    'process_mode' => ['required', Rule::in(['service', 'apostil', 'consul'])],
                    'selection_mode' => ['nullable', Rule::in(['consul', 'legalization', 'mixed'])],
                    'direction_type_id' => [
                        'nullable',
                        'exists:direction_type,id',
                        Rule::requiredIf(fn () => ($item['process_mode'] ?? null) === 'apostil'),
                    ],
                    'apostil_group1_id' => [
                        'nullable',
                        'exists:apostil_static,id',
                        Rule::requiredIf(fn () => ($item['process_mode'] ?? null) === 'apostil'),
                    ],
                    'apostil_group2_id' => [
                        'nullable',
                        'exists:apostil_static,id',
                        Rule::requiredIf(fn () => ($item['process_mode'] ?? null) === 'apostil'),
                    ],
                    'consul_id' => [
                        'nullable',
                        'exists:consul,id',
                        Rule::requiredIf(fn () => ($item['process_mode'] ?? null) === 'consul'
                            && in_array($item['selection_mode'] ?? null, ['consul', 'mixed'], true)),
                    ],
                    'consulate_type_id' => [
                        'nullable',
                        'exists:consulates_type,id',
                        Rule::requiredIf(fn () => ($item['process_mode'] ?? null) === 'consul'
                            && in_array($item['selection_mode'] ?? null, ['legalization', 'mixed'], true)),
                    ],
                ]);

                if (($item['process_mode'] ?? null) === 'consul' && empty($item['selection_mode'])) {
                    $itemValidator->errors()->add('selection_mode', 'Legalizatsiya tanlov turi majburiy.');
                }

                if ($itemValidator->fails()) {
                    foreach ($itemValidator->errors()->all() as $message) {
                        $validator->errors()->add("items_payload.{$index}", "Element #".($index + 1).": {$message}");
                    }
                }
            }
        });

        $validator->after(function ($validator) use ($data) {
            if (! empty($data['product_code'])) {
                $current = request()->route('templatePackage');
                $query = PackageTemplate::query()->where('product_code', $data['product_code']);
                if ($current instanceof PackageTemplate) {
                    $query->where('id', '<>', $current->id);
                }
                if ($query->exists()) {
                    $validator->errors()->add('product_code', 'Bu mahsulot kodi allaqachon ishlatilgan.');
                }
            }

            $serviceIds = collect($data['items_payload'] ?? [])
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique();

            if ($serviceIds->isEmpty() || empty($data['additional_addon_ids'])) {
                return;
            }

            $validAddonIds = ServiceAddonModel::query()
                ->whereIn('id', $data['additional_addon_ids'])
                ->whereIn('service_id', $serviceIds)
                ->pluck('id');

            if ($validAddonIds->count() !== count($data['additional_addon_ids'])) {
                $validator->errors()->add(
                    'additional_addon_ids',
                    'Qo\'shimcha xizmatlar paket tarkibidagi xizmatlardan tanlanishi kerak.'
                );
            }
        });

        $validated = $validator->validate();
        $items = collect($validated['items_payload'])
            ->values()
            ->map(function (array $item, int $index) {
                $normalized = $this->normalizeItemPayload($item, $index);
                $pricing = PackageTemplateSupport::calculateItemPricing($normalized);

                return $normalized + [
                    'selected_addons' => $pricing['selected_addons'],
                    'base_price' => $pricing['total_price'],
                    'sort_order' => $index,
                ];
            });

        $basePrice = (float) $items->sum('base_price');
        $firstItem = $items->first();
        $calculatedDeadline = (int) $items->map(function (array $item): int {
            return (int) (PackageTemplateSupport::calculateItemPricing($item)['deadline'] ?? 0);
        })->max();
        $standardPrice = $validated['standard_price'] ?? null;
        $standardPrice = $standardPrice === null
            ? ((float) ($validated['promo_price'] ?? 0) ?: $basePrice)
            : (float) $standardPrice;
        $expressPrice = $validated['express_price'] ?? null;
        $expressPrice = $expressPrice === null ? $standardPrice : (float) $expressPrice;
        $promoPrice = (float) ($validated['promo_price'] ?? 0);
        $promoPrice = $promoPrice > 0 ? $promoPrice : $standardPrice;

        return [
            'template' => [
                'name' => $validated['name'],
                'product_code' => $validated['product_code'] ?? null,
                'highlight' => $validated['highlight'] ?? null,
                'description' => $validated['description'] ?? null,
                'process_mode' => $firstItem['process_mode'],
                'selection_mode' => $firstItem['selection_mode'],
                'document_type_id' => $firstItem['document_type_id'],
                'service_id' => $firstItem['service_id'],
                'direction_type_id' => $firstItem['direction_type_id'],
                'apostil_group1_id' => $firstItem['apostil_group1_id'],
                'apostil_group2_id' => $firstItem['apostil_group2_id'],
                'consul_id' => $firstItem['consul_id'],
                'consulate_type_id' => $firstItem['consulate_type_id'],
                'selected_addons' => $firstItem['selected_addons'],
                'base_price' => $basePrice,
                'promo_price' => $promoPrice,
                'standard_price' => $standardPrice,
                'express_price' => $expressPrice,
                'standard_deadline_days' => (int) ($validated['standard_deadline_days'] ?? $calculatedDeadline),
                'express_deadline_days' => (int) ($validated['express_deadline_days'] ?? max(1, (int) ceil($calculatedDeadline / 2))),
                'margin_percent' => (float) ($validated['margin_percent'] ?? 0),
                'delivery_type' => $validated['delivery_type'] ?? 'pickup',
                'is_active' => (bool) $validated['is_active'],
                'is_sellable' => (bool) $validated['is_sellable'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
            ],
            'items' => $items->map(fn (array $item) => collect($item)
                ->except('package_template_id')
                ->all())
                ->all(),
            'filial_ids' => $data['filial_ids'],
            'additional_addon_ids' => $data['additional_addon_ids'],
        ];
    }

    protected function syncProductRelations(PackageTemplate $template, array $validated): void
    {
        if (! $template->product_code) {
            $template->forceFill([
                'product_code' => 'PKG-' . str_pad((string) $template->id, 6, '0', STR_PAD_LEFT),
            ])->save();
        }

        $template->packageFilials()->delete();
        foreach ($validated['filial_ids'] ?? [] as $filialId) {
            $template->packageFilials()->create([
                'filial_id' => $filialId,
                'is_available' => true,
            ]);
        }

        $template->packageAddons()->delete();
        foreach ($validated['additional_addon_ids'] ?? [] as $index => $addonId) {
            $template->packageAddons()->create([
                'service_addon_id' => $addonId,
                'is_included' => false,
                'quantity' => 1,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    protected function decodeItemsPayload($payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($payload) ? $payload : [];
    }

    protected function normalizeItemPayload(array $item, int $index = 0): array
    {
        return [
            'document_type_id' => (int) ($item['document_type_id'] ?? 0),
            'service_id' => (int) ($item['service_id'] ?? 0),
            'process_mode' => PackageTemplateSupport::normalizeProcessMode($item['process_mode'] ?? 'service'),
            'selection_mode' => PackageTemplateSupport::normalizeSelectionMode($item['selection_mode'] ?? null),
            'direction_type_id' => $this->nullableInt($item['direction_type_id'] ?? null),
            'apostil_group1_id' => $this->nullableInt($item['apostil_group1_id'] ?? null),
            'apostil_group2_id' => $this->nullableInt($item['apostil_group2_id'] ?? null),
            'consul_id' => $this->nullableInt($item['consul_id'] ?? null),
            'consulate_type_id' => $this->nullableInt($item['consulate_type_id'] ?? null),
            'selected_addons' => PackageTemplateSupport::normalizeSelectedAddons($item['selected_addons'] ?? []),
            'sort_order' => $index,
        ];
    }

    protected function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function itemRelations(): array
    {
        return [
            'items.documentType:id,name',
            'items.service:id,name,price,deadline',
            'items.directionType:id,name',
            'items.apostilGroup1:id,name,price,days',
            'items.apostilGroup2:id,name,price,days',
            'items.consul:id,name,amount,day',
            'items.consulateType:id,name,amount,day',
        ];
    }
}
