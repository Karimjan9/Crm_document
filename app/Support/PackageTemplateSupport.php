<?php

namespace App\Support;

use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateItem;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PackageTemplateSupport
{
    public static function calculatePricing(array $config): array
    {
        return static::calculateItemPricing($config);
    }

    public static function calculateItemPricing(array $config): array
    {
        $service = ServicesModel::findOrFail((int) $config['service_id']);
        $processMode = static::normalizeProcessMode($config['process_mode'] ?? '');
        $selectionMode = static::normalizeSelectionMode($config['selection_mode'] ?? '');
        $selectedAddons = static::normalizeSelectedAddons($config['selected_addons'] ?? []);

        $servicePrice = (float) ($service->price ?? 0);
        $deadline = (int) ($service->deadline ?? 0);
        $addonsTotal = 0.0;
        $includedItems = collect();

        foreach ($selectedAddons as $addonSelection) {
            $resolvedAddon = static::resolveAddonSelection($addonSelection);
            if (!$resolvedAddon) {
                continue;
            }

            $addonsTotal += (float) ($resolvedAddon['amount'] ?? 0);
            $deadline += (int) ($resolvedAddon['days'] ?? 0);
            $includedItems->push($resolvedAddon['item']);
        }

        $processTotal = 0.0;

        if ($processMode === 'apostil') {
            $group1 = !empty($config['apostil_group1_id'])
                ? ApostilStatikModel::find((int) $config['apostil_group1_id'])
                : null;
            $group2 = !empty($config['apostil_group2_id'])
                ? ApostilStatikModel::find((int) $config['apostil_group2_id'])
                : null;

            foreach ([$group1, $group2] as $group) {
                if (!$group) {
                    continue;
                }

                $processTotal += (float) ($group->price ?? 0);
                $deadline += (int) ($group->days ?? 0);
                $includedItems->push([
                    'label' => 'Apostil',
                    'name' => $group->name,
                    'price' => (float) ($group->price ?? 0),
                ]);
            }
        }

        if ($processMode === 'consul') {
            $includeConsul = in_array($selectionMode, ['consul', 'mixed'], true);
            $includeLegalization = in_array($selectionMode, ['legalization', 'mixed'], true);

            if ($includeConsul && !empty($config['consul_id'])) {
                $consul = ConsulModel::find((int) $config['consul_id']);
                if ($consul) {
                    $processTotal += (float) ($consul->amount ?? 0);
                    $deadline += (int) ($consul->day ?? 0);
                    $includedItems->push([
                        'label' => 'Konsullik',
                        'name' => $consul->name,
                        'price' => (float) ($consul->amount ?? 0),
                    ]);
                }
            }

            if ($includeLegalization && !empty($config['consulate_type_id'])) {
                $consulateType = ConsulationTypeModel::find((int) $config['consulate_type_id']);
                if ($consulateType) {
                    $processTotal += (float) ($consulateType->amount ?? 0);
                    $deadline += (int) ($consulateType->day ?? 0);
                    $includedItems->push([
                        'label' => 'Legalizatsiya',
                        'name' => $consulateType->name,
                        'price' => (float) ($consulateType->amount ?? 0),
                    ]);
                }
            }
        }

        return [
            'service_price' => $servicePrice,
            'addons_total' => $addonsTotal,
            'process_total' => $processTotal,
            'total_price' => $servicePrice + $addonsTotal + $processTotal,
            'deadline' => $deadline,
            'selected_addons' => $selectedAddons,
            'included_items' => $includedItems->values()->all(),
        ];
    }

    public static function buildSelectionPayloads(Collection $templates): array
    {
        return $templates->map(fn (PackageTemplate $template) => static::buildTemplatePayload($template))
            ->filter(fn (array $payload) => (int) ($payload['item_count'] ?? 0) > 0)
            ->values()
            ->all();
    }

    public static function buildTemplatePayload(PackageTemplate $template): array
    {
        $items = $template->relationLoaded('items')
            ? $template->items
            : $template->items()->get();

        $itemPayloads = $items->map(fn (PackageTemplateItem $item) => static::buildItemPayload($item))
            ->values();

        $computedBasePrice = (float) $itemPayloads->sum('base_price');
        $basePrice = (float) ($template->base_price ?: $computedBasePrice);

        return [
            'id' => $template->id,
            'name' => $template->name,
            'highlight' => $template->highlight,
            'description' => $template->description,
            'base_price' => $basePrice,
            'promo_price' => (float) $template->promo_price,
            'savings_amount' => max($basePrice - (float) $template->promo_price, 0),
            'item_count' => $itemPayloads->count(),
            'items' => $itemPayloads->all(),
            'included_items' => $itemPayloads
                ->flatMap(fn (array $item) => collect($item['included_items'])->map(function (array $included) use ($item) {
                    return [
                        'label' => $item['summary_name'],
                        'name' => $included['name'],
                        'price' => $included['price'] ?? null,
                    ];
                }))
                ->take(12)
                ->values()
                ->all(),
        ];
    }

    public static function buildItemPayload(PackageTemplateItem $item): array
    {
        $pricing = static::calculateItemPricing([
            'service_id' => $item->service_id,
            'process_mode' => $item->process_mode,
            'selection_mode' => $item->selection_mode,
            'document_type_id' => $item->document_type_id,
            'direction_type_id' => $item->direction_type_id,
            'apostil_group1_id' => $item->apostil_group1_id,
            'apostil_group2_id' => $item->apostil_group2_id,
            'consul_id' => $item->consul_id,
            'consulate_type_id' => $item->consulate_type_id,
            'selected_addons' => $item->selected_addons,
        ]);

        $basePrice = (float) ($item->base_price ?: $pricing['total_price']);

        return [
            'id' => $item->id,
            'document_type_id' => $item->document_type_id,
            'service_id' => $item->service_id,
            'process_mode' => static::normalizeProcessMode($item->process_mode),
            'selection_mode' => static::normalizeSelectionMode($item->selection_mode),
            'direction_type_id' => $item->direction_type_id,
            'apostil_group1_id' => $item->apostil_group1_id,
            'apostil_group2_id' => $item->apostil_group2_id,
            'consul_id' => $item->consul_id,
            'consulate_type_id' => $item->consulate_type_id,
            'selected_addons' => static::normalizeSelectedAddons($item->selected_addons),
            'base_price' => $basePrice,
            'deadline' => (int) ($pricing['deadline'] ?? 0),
            'summary_name' => static::buildItemSummary($item),
            'included_items' => static::buildItemDisplayItems($item, $pricing['included_items']),
        ];
    }

    public static function matchesRequest(?PackageTemplate $template, Request $request): bool
    {
        if (!$template) {
            return false;
        }

        $item = $template->relationLoaded('items')
            ? $template->items->first()
            : $template->items()->first();

        if (!$item) {
            return false;
        }

        return static::normalizeComparableConfig([
            'document_type_id' => static::nullableInt($request->input('document_type_id')),
            'service_id' => static::nullableInt($request->input('service_id')),
            'process_mode' => static::normalizeProcessMode($request->input('process_mode')),
            'selection_mode' => static::normalizeSelectionMode($request->input('selection_mode')),
            'direction_type_id' => static::nullableInt($request->input('direction_type_id')),
            'apostil_group1_id' => static::nullableInt($request->input('apostil_group1_id')),
            'apostil_group2_id' => static::nullableInt($request->input('apostil_group2_id')),
            'consul_id' => static::nullableInt($request->input('consul_id')),
            'consulate_type_id' => static::nullableInt($request->input('consulate_type_id')),
            'selected_addons' => static::normalizeSelectedAddons($request->input('selected_addons')),
        ]) === static::normalizeComparableConfig([
            'document_type_id' => static::nullableInt($item->document_type_id),
            'service_id' => static::nullableInt($item->service_id),
            'process_mode' => static::normalizeProcessMode($item->process_mode),
            'selection_mode' => static::normalizeSelectionMode($item->selection_mode),
            'direction_type_id' => static::nullableInt($item->direction_type_id),
            'apostil_group1_id' => static::nullableInt($item->apostil_group1_id),
            'apostil_group2_id' => static::nullableInt($item->apostil_group2_id),
            'consul_id' => static::nullableInt($item->consul_id),
            'consulate_type_id' => static::nullableInt($item->consulate_type_id),
            'selected_addons' => static::normalizeSelectedAddons($item->selected_addons),
        ]);
    }

    public static function normalizeSelectedAddons($selectedAddons): array
    {
        if (is_string($selectedAddons)) {
            $decoded = json_decode($selectedAddons, true);
            $selectedAddons = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($selectedAddons)) {
            return [];
        }

        return collect($selectedAddons)
            ->map(function ($item) {
                if (!is_array($item)) {
                    return null;
                }

                $sourceType = $item['sourceType'] ?? $item['type'] ?? null;
                $id = isset($item['id']) ? (int) $item['id'] : null;

                if (!$sourceType || !$id) {
                    return null;
                }

                return [
                    'id' => $id,
                    'sourceType' => $sourceType,
                ];
            })
            ->filter()
            ->unique(fn (array $item) => $item['sourceType'] . ':' . $item['id'])
            ->sortBy([
                ['sourceType', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
            ->all();
    }

    public static function normalizeProcessMode(?string $mode): string
    {
        $mode = trim((string) $mode);

        return $mode === '' ? 'service' : $mode;
    }

    public static function normalizeSelectionMode(?string $mode): ?string
    {
        $mode = trim((string) $mode);

        return $mode === '' ? null : $mode;
    }

    private static function resolveAddonSelection(array $selection): ?array
    {
        $sourceType = $selection['sourceType'] ?? null;
        $id = isset($selection['id']) ? (int) $selection['id'] : null;

        if (!$sourceType || !$id) {
            return null;
        }

        return match ($sourceType) {
            'document' => static::wrapAddon(
                DocumentTypeAdditionModel::find($id),
                "Hujjat qo'shimchasi",
                'amount',
                'day'
            ),
            'direction' => static::wrapAddon(
                DocumentDirectionAdditionModel::find($id),
                "Yo'nalish qo'shimchasi",
                'amount',
                'day'
            ),
            'service' => static::wrapAddon(
                ServiceAddonModel::find($id),
                "Xizmat qo'shimchasi",
                'price',
                'deadline'
            ),
            default => null,
        };
    }

    private static function wrapAddon($model, string $label, string $amountField, string $daysField): ?array
    {
        if (!$model) {
            return null;
        }

        return [
            'amount' => (float) ($model->{$amountField} ?? 0),
            'days' => (int) ($model->{$daysField} ?? 0),
            'item' => [
                'label' => $label,
                'name' => $model->name,
                'price' => (float) ($model->{$amountField} ?? 0),
            ],
        ];
    }

    private static function buildItemDisplayItems(PackageTemplateItem $item, array $items): array
    {
        $display = collect([
            $item->documentType ? [
                'label' => 'Hujjat turi',
                'name' => $item->documentType->name,
                'price' => null,
            ] : null,
            $item->service ? [
                'label' => 'Xizmat',
                'name' => $item->service->name,
                'price' => (float) ($item->service->price ?? 0),
            ] : null,
            $item->process_mode === 'apostil' && $item->directionType ? [
                'label' => "Yo'nalish",
                'name' => $item->directionType->name,
                'price' => null,
            ] : null,
            $item->process_mode === 'consul' && $item->selection_mode ? [
                'label' => 'Tanlov turi',
                'name' => match ($item->selection_mode) {
                    'consul' => 'Konsullik',
                    'legalization' => 'Legalizatsiya',
                    'mixed' => 'Mix',
                    default => ucfirst((string) $item->selection_mode),
                },
                'price' => null,
            ] : null,
        ])->filter();

        return $display
            ->concat($items)
            ->values()
            ->all();
    }

    private static function buildItemSummary(PackageTemplateItem $item): string
    {
        $serviceName = $item->service?->name ?: 'Xizmat';
        $documentName = $item->documentType?->name ?: 'Hujjat';

        return match (static::normalizeProcessMode($item->process_mode)) {
            'apostil' => "{$documentName} / Apostil / {$serviceName}",
            'consul' => "{$documentName} / Legalizatsiya / {$serviceName}",
            default => "{$documentName} / {$serviceName}",
        };
    }

    private static function normalizeComparableConfig(array $config): array
    {
        $processMode = static::normalizeProcessMode($config['process_mode'] ?? '');
        $selectionMode = static::normalizeSelectionMode($config['selection_mode'] ?? '');

        return [
            'document_type_id' => static::nullableInt($config['document_type_id'] ?? null),
            'service_id' => static::nullableInt($config['service_id'] ?? null),
            'process_mode' => $processMode,
            'selection_mode' => $processMode === 'consul' ? $selectionMode : null,
            'direction_type_id' => $processMode === 'apostil' ? static::nullableInt($config['direction_type_id'] ?? null) : null,
            'apostil_group1_id' => $processMode === 'apostil' ? static::nullableInt($config['apostil_group1_id'] ?? null) : null,
            'apostil_group2_id' => $processMode === 'apostil' ? static::nullableInt($config['apostil_group2_id'] ?? null) : null,
            'consul_id' => $processMode === 'consul' && in_array($selectionMode, ['consul', 'mixed'], true)
                ? static::nullableInt($config['consul_id'] ?? null)
                : null,
            'consulate_type_id' => $processMode === 'consul' && in_array($selectionMode, ['legalization', 'mixed'], true)
                ? static::nullableInt($config['consulate_type_id'] ?? null)
                : null,
            'selected_addons' => static::normalizeSelectedAddons($config['selected_addons'] ?? []),
        ];
    }

    private static function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
