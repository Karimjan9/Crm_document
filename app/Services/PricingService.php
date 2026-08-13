<?php

namespace App\Services;

use App\Models\PriceTariff;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\DocumentsModel;
use App\Models\PricingApproval;
use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PricingService
{
    public function resolveService(ServicesModel $service, ?int $filialId, array $context = []): array
    {
        $variant = $this->variant($context);
        $tariff = $this->findTariff(
            PriceTariff::query()->where('line_type', 'base_service')->where('service_id', $service->id),
            $filialId,
            $context,
            $variant
        );

        if ($tariff) {
            return $this->tariffQuote($tariff, $context, 'base_service', $service->id, $service->name);
        }

        return $this->legacyQuote(
            'base_service',
            $service->id,
            $service->name,
            (float) $service->price,
            (int) ($service->deadline ?? 0),
            $context,
        );
    }

    public function resolveServiceAddon(ServiceAddonModel $addon, ?int $filialId, array $context = []): array
    {
        $tariff = $this->findTariff(
            PriceTariff::query()->where('line_type', 'addon')->where('service_addon_id', $addon->id),
            $filialId,
            $context,
            $this->variant($context)
        );

        if ($tariff) {
            return $this->tariffQuote($tariff, $context, 'addon', $addon->id, $addon->name);
        }

        return $this->legacyQuote(
            'addon',
            $addon->id,
            $addon->name,
            (float) $addon->price,
            (int) ($addon->deadline ?? 0),
            $context,
        );
    }

    public function resolveFixed(
        string $lineType,
        ?int $sourceId,
        ?string $priceKey,
        string $name,
        float $fallbackPrice,
        int $fallbackDeadline,
        ?int $filialId,
        array $context = []
    ): array {
        $query = PriceTariff::query()->where('line_type', $lineType);

        if ($sourceId !== null || $priceKey !== null) {
            $query->where(function (Builder $builder) use ($sourceId, $priceKey): void {
                if ($priceKey !== null) {
                    $builder->where('price_key', $priceKey);
                    if ($sourceId !== null) {
                        $builder->orWhere(function (Builder $fallback) use ($sourceId): void {
                            $fallback->where('source_id', $sourceId)->whereNull('price_key');
                        });
                    }
                } elseif ($sourceId !== null) {
                    $builder->where('source_id', $sourceId);
                }
            });
        }

        $tariff = $this->findTariff($query, $filialId, $context, $this->variant($context));

        if ($tariff) {
            return $this->tariffQuote($tariff, $context, $lineType, $sourceId, $name);
        }

        return $this->legacyQuote(
            $lineType,
            $sourceId,
            $name,
            $fallbackPrice,
            $fallbackDeadline,
            $context,
            $priceKey,
        );
    }

    public function createTariff(array $attributes): PriceTariff
    {
        $attributes['variant'] ??= 'standard';
        $attributes['currency'] ??= config('pricing.default_currency', 'UZS');
        $attributes['effective_from'] = CarbonImmutable::parse($attributes['effective_from']);
        $attributes['effective_to'] = !empty($attributes['effective_to'])
            ? CarbonImmutable::parse($attributes['effective_to'])
            : null;
        $attributes['is_active'] = (bool) ($attributes['is_active'] ?? true);
        $attributes['price'] = round((float) ($attributes['price'] ?? 0), 2);
        $attributes['cost_amount'] = round((float) ($attributes['cost_amount'] ?? 0), 2);

        if ($attributes['price'] < 0 || $attributes['cost_amount'] < 0) {
            throw ValidationException::withMessages([
                'price' => 'Tarif narxi va tannarx manfiy bo‘lishi mumkin emas.',
            ]);
        }

        if ($attributes['effective_to'] !== null && $attributes['effective_to']->lessThanOrEqualTo($attributes['effective_from'])) {
            throw ValidationException::withMessages([
                'effective_to' => 'Tarif tugash vaqti boshlanish vaqtidan keyin bo‘lishi kerak.',
            ]);
        }

        if ($attributes['variant'] === 'seasonal' && blank($attributes['season_code'] ?? null)) {
            throw ValidationException::withMessages([
                'season_code' => 'Mavsumiy tarif uchun season code kiritilishi shart.',
            ]);
        }

        if (($attributes['line_type'] ?? null) === 'base_service' && empty($attributes['service_id'])) {
            throw ValidationException::withMessages([
                'service_id' => 'Base service tarifi uchun xizmat tanlanishi shart.',
            ]);
        }

        if (($attributes['line_type'] ?? null) === 'addon'
            && empty($attributes['service_addon_id'])
            && empty($attributes['source_id'])
            && blank($attributes['price_key'] ?? null)) {
            throw ValidationException::withMessages([
                'service_addon_id' => 'Addon tarifi manbaga bog‘lanishi shart.',
            ]);
        }

        $this->closeOpenVersionAt($attributes);

        if ($this->overlaps($attributes)) {
            throw ValidationException::withMessages([
                'effective_from' => 'Xuddi shu shartlar uchun tarif davri mavjud tarif bilan kesishadi.',
            ]);
        }

        return PriceTariff::create($attributes);
    }

    public function updateTariff(PriceTariff $tariff, array $attributes): PriceTariff
    {
        $attributes['variant'] ??= $tariff->variant ?: 'standard';
        $attributes['currency'] ??= $tariff->currency ?: config('pricing.default_currency', 'UZS');
        $attributes['is_active'] = (bool) ($attributes['is_active'] ?? false);
        $attributes['priority'] = (int) ($attributes['priority'] ?? 0);
        $attributes['effective_from'] = CarbonImmutable::parse($attributes['effective_from']);
        $attributes['effective_to'] = !empty($attributes['effective_to'])
            ? CarbonImmutable::parse($attributes['effective_to'])
            : null;
        $attributes['price'] = round((float) ($attributes['price'] ?? 0), 2);
        $attributes['cost_amount'] = round((float) ($attributes['cost_amount'] ?? 0), 2);

        if ($attributes['effective_to'] !== null && $attributes['effective_to']->lessThanOrEqualTo($attributes['effective_from'])) {
            throw ValidationException::withMessages([
                'effective_to' => 'Tarif tugash vaqti boshlanish vaqtidan keyin bo‘lishi kerak.',
            ]);
        }

        if ($attributes['price'] < 0 || $attributes['cost_amount'] < 0) {
            throw ValidationException::withMessages([
                'price' => 'Tarif narxi va tannarx manfiy bo‘lishi mumkin emas.',
            ]);
        }

        if ($attributes['variant'] === 'seasonal' && blank($attributes['season_code'] ?? null)) {
            throw ValidationException::withMessages([
                'season_code' => 'Mavsumiy tarif uchun season code kiritilishi shart.',
            ]);
        }

        if (($attributes['line_type'] ?? null) === 'base_service' && empty($attributes['service_id'])) {
            throw ValidationException::withMessages([
                'service_id' => 'Base service tarifi uchun xizmat tanlanishi shart.',
            ]);
        }

        $overlap = PriceTariff::query()
            ->where('id', '<>', $tariff->id)
            ->where('line_type', $attributes['line_type'])
            ->where('variant', $attributes['variant'])
            ->where('filial_id', $attributes['filial_id'] ?? null)
            ->where('partner_id', $attributes['partner_id'] ?? null)
            ->where('season_code', $attributes['season_code'] ?? null)
            ->where('service_id', $attributes['service_id'] ?? null)
            ->where('service_addon_id', $attributes['service_addon_id'] ?? null)
            ->where('source_id', $attributes['source_id'] ?? null)
            ->where('price_key', $attributes['price_key'] ?? null)
            ->where(function (Builder $query) use ($attributes): void {
                $query->whereNull('effective_to')
                    ->orWhere('effective_from', '<', $attributes['effective_to'] ?? CarbonImmutable::create(9999, 12, 31));
            })
            ->where(function (Builder $query) use ($attributes): void {
                $query->whereNull('effective_to')->orWhere('effective_to', '>', $attributes['effective_from']);
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'Tarif davri boshqa versiya bilan kesishadi.',
            ]);
        }

        $tariff->update($attributes);

        return $tariff->refresh();
    }

    public function publishServicePrice(ServicesModel $service, float $price, int $deadline): PriceTariff
    {
        return DB::transaction(function () use ($service, $price, $deadline): PriceTariff {
            $now = CarbonImmutable::now();
            PriceTariff::query()
                ->where('line_type', 'base_service')
                ->where('service_id', $service->id)
                ->whereNull('filial_id')
                ->whereNull('partner_id')
                ->where('variant', 'standard')
                ->whereNull('season_code')
                ->where('is_active', true)
                ->where('effective_from', '<=', $now)
                ->where(function (Builder $query) use ($now): void {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>', $now);
                })
                ->update(['effective_to' => $now]);

            return PriceTariff::create([
                'line_type' => 'base_service',
                'service_id' => $service->id,
                'variant' => 'standard',
                'name' => $service->name,
                'price' => round($price, 2),
                'cost_amount' => 0,
                'deadline' => max($deadline, 0),
                'currency' => config('pricing.default_currency', 'UZS'),
                'effective_from' => $now,
                'is_active' => true,
            ]);
        });
    }

    public function publishAddonPrice(ServiceAddonModel $addon, float $price, int $deadline): PriceTariff
    {
        return DB::transaction(function () use ($addon, $price, $deadline): PriceTariff {
            $now = CarbonImmutable::now();
            PriceTariff::query()
                ->where('line_type', 'addon')
                ->where('service_addon_id', $addon->id)
                ->whereNull('filial_id')
                ->whereNull('partner_id')
                ->where('variant', 'standard')
                ->whereNull('season_code')
                ->where('is_active', true)
                ->where('effective_from', '<=', $now)
                ->where(function (Builder $query) use ($now): void {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>', $now);
                })
                ->update(['effective_to' => $now]);

            return PriceTariff::create([
                'line_type' => 'addon',
                'service_id' => $addon->service_id,
                'service_addon_id' => $addon->id,
                'variant' => 'standard',
                'name' => $addon->name,
                'price' => round($price, 2),
                'cost_amount' => 0,
                'deadline' => max($deadline, 0),
                'currency' => config('pricing.default_currency', 'UZS'),
                'effective_from' => $now,
                'is_active' => true,
            ]);
        });
    }

    public function publishFixedPrice(
        string $lineType,
        ?int $sourceId,
        string $priceKey,
        string $name,
        float $price,
        int $deadline = 0,
        ?int $filialId = null,
        ?int $partnerId = null,
    ): PriceTariff {
        if (!in_array($lineType, ['addon', 'apostille', 'consulate', 'courier', 'express_fee', 'tax'], true)) {
            throw ValidationException::withMessages([
                'line_type' => 'Fixed tarif turi noto‘g‘ri.',
            ]);
        }

        return DB::transaction(function () use ($lineType, $sourceId, $priceKey, $name, $price, $deadline, $filialId, $partnerId): PriceTariff {
            $now = CarbonImmutable::now();
            PriceTariff::query()
                ->where('line_type', $lineType)
                ->where('source_id', $sourceId)
                ->where('price_key', $priceKey)
                ->where('filial_id', $filialId)
                ->where('partner_id', $partnerId)
                ->where('variant', 'standard')
                ->whereNull('season_code')
                ->where('is_active', true)
                ->where('effective_from', '<=', $now)
                ->where(function (Builder $query) use ($now): void {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>', $now);
                })
                ->update(['effective_to' => $now]);

            return PriceTariff::create([
                'line_type' => $lineType,
                'source_id' => $sourceId,
                'price_key' => $priceKey,
                'variant' => 'standard',
                'name' => $name,
                'price' => round(max($price, 0), 2),
                'cost_amount' => 0,
                'deadline' => max($deadline, 0),
                'currency' => config('pricing.default_currency', 'UZS'),
                'filial_id' => $filialId,
                'partner_id' => $partnerId,
                'effective_from' => $now,
                'is_active' => true,
            ]);
        });
    }

    public function quoteContext(array $context = []): array
    {
        $variant = $this->variant($context);

        return [
            'variant' => $variant,
            'season_code' => $context['season_code'] ?? null,
            'filial_id' => isset($context['filial_id']) ? (int) $context['filial_id'] : null,
            'partner_id' => isset($context['partner_id']) ? (int) $context['partner_id'] : null,
            'as_of' => $this->asOf($context)->toIso8601String(),
        ];
    }

    public function discountRequiresApproval(float $percent, float $amount): bool
    {
        return $percent >= (float) config('pricing.discount_approval_percent', 15)
            || $amount >= (float) config('pricing.discount_approval_amount', 500000);
    }

    public function canApproveDiscount(?User $user): bool
    {
        return $user?->hasAnyRole(['super_admin', 'admin_manager']) ?? false;
    }

    public function assertDiscountApproval(
        float $percent,
        float $amount,
        ?User $actor,
        ?DocumentsModel $document = null,
        ?int $approvalId = null,
        ?int $orderId = null,
        ?string $approvalToken = null,
    ): string {
        if (!$this->discountRequiresApproval($percent, $amount)) {
            return 'not_required';
        }

        if ($this->canApproveDiscount($actor)) {
            return 'approved';
        }

        $approvalId ??= $document?->pricingApprovals()
            ->where('status', 'approved')
            ->latest('id')
            ->value('id');

        $approval = $approvalId
            ? PricingApproval::query()
                ->whereKey($approvalId)
                ->where('status', 'approved')
                ->when($document, fn (Builder $query) => $query->where('document_id', $document->id))
                ->when(!$document && $orderId, fn (Builder $query) => $query->where('order_id', $orderId))
                ->when(!$document && !$orderId, fn (Builder $query) => $query->whereNull('document_id')->whereNull('order_id')->where('requested_by_id', $actor?->id))
                ->lockForUpdate()
                ->first()
            : null;

        $matches = $approval
            && abs((float) $approval->discount_percent - $percent) < 0.01
            && abs((float) $approval->discount_amount - $amount) < 0.01
            && ($approvalToken === null
                || data_get($approval->metadata, 'approval_token') === $approvalToken);

        if (!$matches) {
            throw ValidationException::withMessages([
                'discount' => 'Bu chegirma uchun admin tasdig‘i kerak. Avval pricing approval yuboring.',
            ]);
        }

        return 'approved';
    }

    public function variant(array $context): string
    {
        $variant = strtolower(trim((string) ($context['variant'] ?? config('pricing.default_variant', 'standard'))));

        return in_array($variant, PriceTariff::VARIANTS, true) ? $variant : 'standard';
    }

    private function findTariff(Builder $query, ?int $filialId, array $context, string $variant): ?PriceTariff
    {
        $asOf = $this->asOf($context);
        $partnerId = isset($context['partner_id']) ? (int) $context['partner_id'] : null;
        $seasonCode = $context['season_code'] ?? null;

        $tariffs = $query
            ->active()
            ->where('effective_from', '<=', $asOf)
            ->where(function (Builder $builder) use ($asOf): void {
                $builder->whereNull('effective_to')->orWhere('effective_to', '>', $asOf);
            })
            ->where(function (Builder $builder) use ($filialId): void {
                $builder->whereNull('filial_id');
                if ($filialId !== null) {
                    $builder->orWhere('filial_id', $filialId);
                }
            })
            ->where(function (Builder $builder) use ($partnerId): void {
                $builder->whereNull('partner_id');
                if ($partnerId !== null) {
                    $builder->orWhere('partner_id', $partnerId);
                }
            })
            ->where(function (Builder $builder) use ($variant): void {
                if ($variant === 'corporate') {
                    $builder->whereIn('variant', ['corporate', 'standard']);
                } elseif ($variant === 'seasonal') {
                    $builder->whereIn('variant', ['seasonal', 'standard']);
                } else {
                    $builder->whereIn('variant', [$variant, 'standard']);
                }
            })
            ->where(function (Builder $builder) use ($seasonCode): void {
                $builder->whereNull('season_code');
                if ($seasonCode !== null) {
                    $builder->orWhere('season_code', $seasonCode);
                }
            })
            ->get();

        return $tariffs
            ->sortByDesc(function (PriceTariff $tariff) use ($filialId, $partnerId, $variant, $seasonCode): array {
                return [
                    $filialId !== null && (int) $tariff->filial_id === $filialId ? 1 : 0,
                    $partnerId !== null && (int) $tariff->partner_id === $partnerId ? 1 : 0,
                    $variant !== 'standard' && $tariff->variant === $variant ? 1 : 0,
                    $seasonCode !== null && $tariff->season_code === $seasonCode ? 1 : 0,
                    (int) $tariff->priority,
                    optional($tariff->effective_from)->getTimestamp() ?? 0,
                    (int) $tariff->id,
                ];
            })
            ->first();
    }

    private function tariffQuote(
        PriceTariff $tariff,
        array $context,
        string $lineType,
        ?int $sourceId,
        string $name,
    ): array {
        return [
            'line_type' => $lineType,
            'source_id' => $sourceId ?: $tariff->source_id,
            'name' => $tariff->name ?: $name,
            'price' => round((float) $tariff->price, 2),
            'cost_amount' => round((float) $tariff->cost_amount, 2),
            'deadline' => (int) ($tariff->deadline ?? 0),
            'tariff_id' => $tariff->id,
            'source' => 'tariff',
            'effective_from' => optional($tariff->effective_from)->toIso8601String(),
            'effective_to' => optional($tariff->effective_to)->toIso8601String(),
            'context' => $this->quoteContext($context),
            'currency' => $tariff->currency ?: config('pricing.default_currency', 'UZS'),
            'variant_applied' => $tariff->variant,
            'price_key' => $tariff->price_key,
        ];
    }

    private function legacyQuote(
        string $lineType,
        ?int $sourceId,
        string $name,
        float $price,
        int $deadline,
        array $context,
        ?string $priceKey = null,
    ): array {
        return [
            'line_type' => $lineType,
            'source_id' => $sourceId,
            'price_key' => $priceKey,
            'name' => $name,
            'price' => round(max($price, 0), 2),
            'cost_amount' => 0,
            'deadline' => max($deadline, 0),
            'tariff_id' => null,
            'source' => 'legacy',
            'effective_from' => null,
            'effective_to' => null,
            'context' => $this->quoteContext($context),
            'currency' => config('pricing.default_currency', 'UZS'),
            'variant_applied' => 'legacy',
        ];
    }

    private function asOf(array $context): CarbonImmutable
    {
        $value = $context['as_of'] ?? null;

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        return $value ? CarbonImmutable::parse($value) : CarbonImmutable::now();
    }

    private function overlaps(array $attributes): bool
    {
        $query = PriceTariff::query()
            ->where('line_type', $attributes['line_type'])
            ->where('variant', $attributes['variant'])
            ->where('filial_id', $attributes['filial_id'] ?? null)
            ->where('partner_id', $attributes['partner_id'] ?? null)
            ->where('season_code', $attributes['season_code'] ?? null)
            ->where(function (Builder $builder) use ($attributes): void {
                $builder->where('service_id', $attributes['service_id'] ?? null)
                    ->where('service_addon_id', $attributes['service_addon_id'] ?? null)
                    ->where('source_id', $attributes['source_id'] ?? null)
                    ->where('price_key', $attributes['price_key'] ?? null);
            })
            ->where('effective_from', '<', $attributes['effective_to'] ?? CarbonImmutable::create(9999, 12, 31))
            ->where(function (Builder $builder) use ($attributes): void {
                $builder->whereNull('effective_to')
                    ->orWhere('effective_to', '>', $attributes['effective_from']);
            });

        return $query->exists();
    }

    private function closeOpenVersionAt(array $attributes): void
    {
        if (empty($attributes['effective_from'])) {
            return;
        }

        PriceTariff::query()
            ->where('line_type', $attributes['line_type'])
            ->where('variant', $attributes['variant'])
            ->where('filial_id', $attributes['filial_id'] ?? null)
            ->where('partner_id', $attributes['partner_id'] ?? null)
            ->where('season_code', $attributes['season_code'] ?? null)
            ->when(array_key_exists('service_id', $attributes), fn (Builder $query) => $query->where('service_id', $attributes['service_id'] ?? null))
            ->when(array_key_exists('service_addon_id', $attributes), fn (Builder $query) => $query->where('service_addon_id', $attributes['service_addon_id'] ?? null))
            ->when(array_key_exists('source_id', $attributes), fn (Builder $query) => $query->where('source_id', $attributes['source_id'] ?? null))
            ->when(array_key_exists('price_key', $attributes), fn (Builder $query) => $query->where('price_key', $attributes['price_key'] ?? null))
            ->where('effective_from', '<', $attributes['effective_from'])
            ->where(function (Builder $query) use ($attributes): void {
                $query->whereNull('effective_to')->orWhere('effective_to', '>', $attributes['effective_from']);
            })
            ->update(['effective_to' => $attributes['effective_from']]);
    }
}
