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
            ->with($this->itemRelations())
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

        if ((float) $validated['template']['promo_price'] > (float) $validated['template']['base_price']) {
            return redirect()
                ->back()
                ->withErrors(['promo_price' => "Aksiya narxi umumiy summadan katta bo'lishi mumkin emas."])
                ->withInput();
        }

        $template = PackageTemplate::create($validated['template']);
        $template->items()->createMany($validated['items']);

        return redirect()
            ->route('superadmin.template_package.index')
            ->with('success', 'Shablon muvaffaqiyatli yaratildi.');
    }

    public function edit(PackageTemplate $templatePackage)
    {
        $templatePackage->load($this->itemRelations());

        return view('super_admin.package_templates.edit', $this->formData([
            'templatePackage' => $templatePackage,
        ]));
    }

    public function update(Request $request, PackageTemplate $templatePackage)
    {
        $validated = $this->validatePayload($request);

        if ((float) $validated['template']['promo_price'] > (float) $validated['template']['base_price']) {
            return redirect()
                ->back()
                ->withErrors(['promo_price' => "Aksiya narxi umumiy summadan katta bo'lishi mumkin emas."])
                ->withInput();
        }

        $templatePackage->update($validated['template']);
        $templatePackage->items()->delete();
        $templatePackage->items()->createMany($validated['items']);

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
        $data['items_payload'] = $this->decodeItemsPayload($request->input('items_payload'));

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'highlight' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'promo_price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'items_payload' => 'required|array|min:1',
        ]);

        $validator->after(function ($validator) use ($data) {
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

        return [
            'template' => [
                'name' => $validated['name'],
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
                'promo_price' => (float) $validated['promo_price'],
                'is_active' => (bool) $validated['is_active'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
            ],
            'items' => $items->map(fn (array $item) => collect($item)
                ->except('package_template_id')
                ->all())
                ->all(),
        ];
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
