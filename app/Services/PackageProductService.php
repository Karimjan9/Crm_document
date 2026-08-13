<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderChecklist;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateAddon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackageProductService
{
    public const VARIANTS = ['standard', 'express'];

    public function catalog(?int $filialId = null): Collection
    {
        $products = PackageTemplate::query()
            ->active()
            ->sellable()
            ->whereHas('items')
            ->with([
                'items.service:id,name,deadline,price',
                'items.documentType:id,name',
                'packageFilials',
                'packageAddons.serviceAddon:id,name,price,deadline,service_id',
            ])
            ->ordered()
            ->get();

        return $products
            ->filter(fn (PackageTemplate $product): bool => $this->isAvailable($product, $filialId))
            ->map(fn (PackageTemplate $product): array => $this->payload($product, $filialId))
            ->values();
    }

    /**
     * Public partner catalog. Internal cost and margin economics must never be
     * embedded in a partner cabinet page or returned from the partner API.
     */
    public function partnerCatalog(?int $filialId = null): Collection
    {
        return $this->catalog($filialId)->map(function (array $product): array {
            foreach (['estimated_cost', 'expected_profit', 'margin_percent'] as $field) {
                unset($product[$field], $product['standard'][$field], $product['express'][$field]);
            }

            $product['included_services'] = collect($product['included_services'] ?? [])
                ->map(fn (array $service): array => [
                    'name' => $service['name'] ?? 'Xizmat',
                    'document' => $service['document'] ?? null,
                ])->values()->all();

            return $product;
        })->values();
    }

    public function quote(PackageTemplate $product, ?int $filialId, string $variant = 'standard'): array
    {
        $variant = $this->normalizeVariant($variant);
        $product->loadMissing([
            'items.service:id,name,deadline,price',
            'items.documentType:id,name',
            'packageFilials',
            'packageAddons.serviceAddon:id,name,price,deadline,service_id',
        ]);

        if (! $product->is_active || ! $product->is_sellable) {
            throw ValidationException::withMessages(['package_template_id' => 'Bu paket hozir sotuvda emas.']);
        }

        $branch = $this->branchRule($product, $filialId);
        $branchRules = $product->relationLoaded('packageFilials')
            ? $product->packageFilials
            : $product->packageFilials()->get();

        if ($filialId !== null && $branchRules->isNotEmpty() && ! $branch) {
            throw ValidationException::withMessages(['package_template_id' => 'Bu paket tanlangan filialda mavjud emas.']);
        }

        if ($branch && ! $branch->is_available) {
            throw ValidationException::withMessages(['package_template_id' => 'Bu paket tanlangan filialda mavjud emas.']);
        }

        $standardPrice = $this->positiveOrNull($branch?->standard_price)
            ?? $this->positiveOrNull($product->standard_price)
            ?? $this->positiveOrNull($product->promo_price)
            ?? (float) $product->base_price;
        $expressPrice = $this->positiveOrNull($branch?->express_price)
            ?? $this->positiveOrNull($product->express_price)
            ?? $standardPrice;

        $standardDeadline = $branch?->standard_deadline_days
            ?? ($product->standard_deadline_days ?: $this->calculatedDeadline($product));
        $expressDeadline = $branch?->express_deadline_days
            ?? ($product->express_deadline_days ?: max(1, (int) ceil(((int) $standardDeadline) / 2)));

        $price = $variant === 'express' ? $expressPrice : $standardPrice;
        $deadlineDays = $variant === 'express' ? $expressDeadline : $standardDeadline;
        $marginPercent = min(max((float) $product->margin_percent, 0), 100);
        $cost = round($price * (1 - ($marginPercent / 100)), 2);

        return [
            'id' => $product->id,
            'code' => $product->product_code ?: 'PKG-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
            'name' => $product->name,
            'description' => $product->description,
            'highlight' => $product->highlight,
            'variant' => $variant,
            'price' => round((float) $price, 2),
            'standard_price' => round((float) $standardPrice, 2),
            'express_price' => round((float) $expressPrice, 2),
            'deadline_days' => (int) $deadlineDays,
            'standard_deadline_days' => (int) $standardDeadline,
            'express_deadline_days' => (int) $expressDeadline,
            'margin_percent' => round($marginPercent, 2),
            'estimated_cost' => $cost,
            'expected_profit' => round($price - $cost, 2),
            'delivery_type' => $product->delivery_type ?: 'pickup',
            'included_services' => $this->includedServices($product),
            'additional_services' => $this->additionalServices($product),
            'filial_id' => $filialId,
        ];
    }

    public function attachToOrder(Order $order, int $productId, string $variant = 'standard', array $addonIds = []): Order
    {
        return DB::transaction(function () use ($order, $productId, $variant, $addonIds): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $product = PackageTemplate::query()->findOrFail($productId);
            $quote = $this->quote($product, (int) $order->filial_id, $variant);
            $addonIds = array_values(array_unique(array_map('intval', $addonIds)));
            $selectedAddons = $product->packageAddons
                ->where('is_active', true)
                ->where('is_included', false)
                ->whereIn('service_addon_id', $addonIds);

            if (count($addonIds) !== $selectedAddons->pluck('service_addon_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'package_addon_ids' => 'Tanlangan qo\'shimcha xizmat bu paketga tegishli emas.',
                ]);
            }

            $order->priceLines()->whereIn('line_type', ['package', 'package_addon'])->delete();
            $order->priceLines()->create([
                'line_type' => 'package',
                'source_id' => $product->id,
                'name' => $product->name . ' (' . ($variant === 'express' ? 'Express' : 'Standard') . ')',
                'quantity' => 1,
                'unit_price' => $quote['price'],
                'total_price' => $quote['price'],
                'cost_amount' => $quote['estimated_cost'],
                'metadata' => [
                    'product_code' => $quote['code'],
                    'variant' => $variant,
                    'price' => $quote['price'],
                    'deadline_days' => $quote['deadline_days'],
                    'margin_percent' => $quote['margin_percent'],
                    'included_services' => $quote['included_services'],
                    'additional_services' => $quote['additional_services'],
                ],
            ]);

            foreach ($selectedAddons as $packageAddon) {
                $addon = $packageAddon->serviceAddon;
                $unitPrice = (float) ($packageAddon->price_override ?? $addon?->price ?? 0);
                $quantity = max(1, (int) $packageAddon->quantity);
                $total = round($unitPrice * $quantity, 2);
                $cost = round($total * (1 - ($quote['margin_percent'] / 100)), 2);

                $order->priceLines()->create([
                    'line_type' => 'package_addon',
                    'source_id' => $addon?->id,
                    'name' => ($addon?->name ?: 'Qo\'shimcha xizmat') . ' (paket qo\'shimchasi)',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $total,
                    'cost_amount' => $cost,
                    'metadata' => [
                        'package_template_id' => $product->id,
                        'package_code' => $quote['code'],
                    ],
                ]);
            }

            $order->forceFill([
                'package_template_id' => $product->id,
                'package_variant' => $variant,
                'package_price' => $quote['price'],
                'package_deadline_days' => $quote['deadline_days'],
                'package_margin_percent' => $quote['margin_percent'],
                'package_name_snapshot' => $product->name,
                'delivery_type' => $order->delivery_type ?: $quote['delivery_type'],
                'promised_at' => $order->promised_at ?: now()->addDays($quote['deadline_days']),
            ])->save();

            OrderChecklist::query()->firstOrCreate(
                [
                    'order_id' => $order->id,
                    'document_id' => null,
                    'title' => 'Paket tarkibini tekshirish: ' . $product->name,
                ],
                [
                    'is_required' => true,
                    'is_completed' => false,
                    'sort_order' => 0,
                ]
            );

            app(OrderCaseService::class)->recalculate($order);

            return $order->fresh(['packageTemplate', 'priceLines']);
        });
    }

    public function isAvailable(PackageTemplate $product, ?int $filialId): bool
    {
        if ($filialId === null) {
            return true;
        }

        $rules = $product->relationLoaded('packageFilials')
            ? $product->packageFilials
            : $product->packageFilials()->get();

        if ($rules->isEmpty()) {
            return true;
        }

        return (bool) $rules->firstWhere('filial_id', $filialId)?->is_available;
    }

    private function payload(PackageTemplate $product, ?int $filialId): array
    {
        $standard = $this->quote($product, $filialId, 'standard');
        $express = $this->quote($product, $filialId, 'express');

        return array_merge($standard, [
            'standard' => $standard,
            'express' => $express,
        ]);
    }

    private function branchRule(PackageTemplate $product, ?int $filialId)
    {
        if ($filialId === null) {
            return null;
        }

        $rules = $product->relationLoaded('packageFilials')
            ? $product->packageFilials
            : $product->packageFilials()->get();

        return $rules->firstWhere('filial_id', $filialId);
    }

    private function includedServices(PackageTemplate $product): array
    {
        return $product->items->map(function ($item): array {
            return [
                'service_id' => $item->service_id,
                'document_type_id' => $item->document_type_id,
                'name' => $item->service?->name ?: ($item->documentType?->name ?: 'Xizmat'),
                'document' => $item->documentType?->name,
                'price' => (float) $item->base_price,
            ];
        })->values()->all();
    }

    private function additionalServices(PackageTemplate $product): array
    {
        return $product->packageAddons
            ->where('is_active', true)
            ->map(function (PackageTemplateAddon $item): array {
                $addon = $item->serviceAddon;
                return [
                    'id' => $addon?->id,
                    'name' => $addon?->name ?: 'Qo\'shimcha xizmat',
                    'price' => (float) ($item->price_override ?? $addon?->price ?? 0),
                    'deadline_days' => (int) ($item->deadline_days_override ?? $addon?->deadline ?? 0),
                    'is_included' => (bool) $item->is_included,
                    'quantity' => (int) $item->quantity,
                ];
            })->values()->all();
    }

    private function calculatedDeadline(PackageTemplate $product): int
    {
        $deadlines = $product->items->map(fn ($item): int => (int) ($item->service?->deadline ?? 0));
        $addonDeadlines = $product->packageAddons->where('is_active', true)
            ->map(fn (PackageTemplateAddon $item): int => (int) ($item->deadline_days_override ?? $item->serviceAddon?->deadline ?? 0));

        return (int) max($deadlines->merge($addonDeadlines)->max() ?: 0, 0);
    }

    private function positiveOrNull($value): ?float
    {
        return $value !== null && (float) $value > 0 ? (float) $value : null;
    }

    private function normalizeVariant(string $variant): string
    {
        if (! in_array($variant, self::VARIANTS, true)) {
            throw ValidationException::withMessages(['package_variant' => 'Paket varianti noto\'g\'ri.']);
        }

        return $variant;
    }
}
