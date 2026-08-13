<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\DocumentsModel;
use App\Models\ClientsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\FilialModel;
use App\Models\DocumentFileModel as DocumentFile;
use App\Models\ServiceAddonModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\PackageTemplate;
use App\Services\OrderCaseService;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Models\Order;

trait StoresDocuments
{
    protected function resolveFilialId(?Request $request = null): int
    {
        $user = Auth::user();

        if (!$user) {
            throw ValidationException::withMessages([
                'filial_id' => 'Hujjat yaratish uchun autentifikatsiya talab qilinadi.',
            ]);
        }

        if ($user->filial_id !== null) {
            return (int) $user->filial_id;
        }

        $filialId = $request?->input('filial_id');
        if (!$filialId || !FilialModel::query()->whereKey($filialId)->exists()) {
            throw ValidationException::withMessages([
                'filial_id' => 'Filial tanlanishi shart.',
            ]);
        }

        return (int) $filialId;
    }

    protected function resolveFilialCode(int $filialId): string
    {
        $filial = FilialModel::query()->findOrFail($filialId);

        return trim((string) ($filial->code ?: $filial->id));
    }

    protected function storeDocumentFromRequest(Request $request): DocumentsModel
    {
        return DB::transaction(fn () => $this->persistDocumentFromRequest($request));
    }

    protected function storeDocumentFromPayload(array $payload, array $files = []): DocumentsModel
    {
        $request = Request::create('/', 'POST', $payload, [], ['files' => $files]);

        return DB::transaction(fn () => $this->persistDocumentFromRequest($request));
    }

    protected function persistDocumentFromRequest(Request $request): DocumentsModel
    {
        $filialId = $this->resolveFilialId($request);
        $clientId = $this->resolveClientId($request, $filialId);

        $order = $request->filled('order_id')
            ? Order::query()->findOrFail((int) $request->input('order_id'))
            : null;
        if ($order) {
            Gate::forUser(auth()->user())->authorize('update', $order);
            if ((int) $order->filial_id !== $filialId || (int) $order->client_id !== $clientId) {
                throw ValidationException::withMessages([
                    'order_id' => 'Hujjat orderning mijoz va filial scope’iga mos kelishi kerak.',
                ]);
            }
            if ($order->partner_id) {
                $request->merge(['partner_id' => $order->partner_id]);
            }
        }

        $pricing = $this->calculateDocumentPricing($request, null, $filialId);
        $paidAmount = $this->resolveInitialPaidAmount($request, $pricing['final_price']);

        $document = app(\App\Services\DocumentCreationService::class)->create([
            'client_id'          => $clientId,
            'service_id'         => $pricing['service_id'],
            'service_price'      => $pricing['service_price'],
            'addons_total_price' => $pricing['addons_total_price'],
            'deadline_time'      => $pricing['deadline_time'],
            'final_price'        => $pricing['final_price'],
            'paid_amount'        => $paidAmount,
            'discount'           => $pricing['discount'],
            'pricing_snapshot'   => $pricing['pricing_snapshot'],
            'pricing_context'    => $pricing['pricing_context'],
            'pricing_locked_at'  => now(),
            'discount_approval_status' => $pricing['discount_approval_status'],
            'user_id'            => auth()->id(),
            'description'        => $request->input('description'),
            'filial_id'          => $filialId,
            'document_type_id'   => $pricing['document_type_id'],
            'direction_type_id'  => $pricing['direction_type_id'],
            'consulate_type_id'  => $pricing['consulate_type_id'],
            'process_mode'       => $pricing['process_mode'],
            'apostil_group1_id'  => $pricing['apostil_group1_id'],
            'apostil_group2_id'  => $pricing['apostil_group2_id'],
            'consul_id'          => $pricing['consul_id'],
        ]);

        $document->document_code = $this->generateDocumentCode($document, $filialId);
        $document->save();

        if ($request->filled('pricing_approval_id')) {
            \App\Models\PricingApproval::query()
                ->whereKey($request->integer('pricing_approval_id'))
                ->where('status', 'approved')
                ->whereNull('document_id')
                ->where(function ($query) use ($order): void {
                    $query->whereNull('order_id');
                    if ($order) {
                        $query->orWhere('order_id', $order->id);
                    }
                })
                ->update(['document_id' => $document->id]);
        }

        if ($request->boolean('original_received')) {
            app(\App\Services\DocumentCustodyService::class)->record(
                $document,
                'received',
                null,
                auth()->id(),
                $request->input('original_notes'),
                null,
                auth()->user(),
            );
        }

        if (!empty($pricing['extras']['charges'])) {
            $now = now();
            $rows = [];
            foreach ($pricing['extras']['charges'] as $charge) {
                $rows[] = [
                    'document_id' => $document->id,
                    'charge_type' => $charge['type'],
                    'source_id'   => $charge['source_id'],
                    'price'       => $charge['price'],
                    'days'        => $charge['days'],
                    'name'        => $charge['name'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            DB::table('document_process_charges')->insert($rows);
        }

        if (!empty($pricing['addons']['document'])) {
            $document->document_type_addons()->attach($pricing['addons']['document']);
        }

        if (!empty($pricing['addons']['direction'])) {
            $document->document_direction_addons()->attach($pricing['addons']['direction']);
        }

        if (!empty($pricing['addons']['service'])) {
            $document->addons()->attach($pricing['addons']['service']);
        }

        $paymentType = $request->input('payment_type');
        if ($paidAmount && $paymentType) {
            app(PaymentService::class)->recordForDocument(
                $document,
                $paidAmount,
                (string) $paymentType,
                auth()->user(),
            );
        }

        $files = $request->file('files', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        foreach ((array) $files as $file) {
            $this->assertValidDocumentFile($file);
            app(\App\Services\DocumentFileService::class)->store($document, $file);
        }

        $workflow = app(\App\Services\DocumentWorkflowService::class);
        $workflow->initialize($document, auth()->user());
        if ($document->assigned_to_id === null) {
            $workflow->autoAssign($document, auth()->user());
        }

        $orderService = app(OrderCaseService::class);

        if ($order) {
            $orderService->attachDocument($order, $document);
        } else {
            $orderService->createForDocument($document);
        }

        $workflow->syncDerivedStatus($document->refresh(), auth()->user());

        return $document;
    }

    /**
     * Resolve and enforce client ownership before a document is persisted.
     * This keeps direct HTML, legacy session API and Sanctum API flows aligned.
     */
    protected function resolveClientId(Request $request, int $filialId): int
    {
        $user = Auth::user();
        $clientId = $request->input('client_id');

        if (! $clientId) {
            $name = trim((string) $request->input('new_client_name'));
            $phone = preg_replace('/\D+/', '', (string) $request->input('new_client_phone'));
            $description = $request->input('new_client_desc', $request->input('description'));

            if ($name === '' || $phone === '') {
                throw ValidationException::withMessages([
                    'client_id' => 'Mijoz tanlanishi yoki yangi mijoz maʼlumotlari berilishi shart.',
                ]);
            }

            $client = ClientsModel::query()->where('phone_number', $phone)->first();

            if (! $client) {
                $client = ClientsModel::query()->create([
                    'name' => $name,
                    'phone_number' => $phone,
                    'description' => is_string($description) ? $description : null,
                    'filial_id' => $user?->filial_id ?? $filialId,
                ]);
            }

            $clientId = $client->getKey();
            $request->merge(['client_id' => $clientId]);
        }

        $client = ClientsModel::query()->find($clientId);

        if (! $client) {
            throw ValidationException::withMessages([
                'client_id' => 'Tanlangan mijoz topilmadi.',
            ]);
        }

        if ($user?->hasAnyRole(['super_admin', 'admin_manager'])) {
            return (int) $client->getKey();
        }

        if (! $user || $user->filial_id === null) {
            throw ValidationException::withMessages([
                'filial_id' => 'Filial biriktirilmagan foydalanuvchi mijoz tanlay olmaydi.',
            ]);
        }

        if ($client->filial_id === null) {
            // A legacy client with no documents can be safely claimed by the
            // first branch that uses it. Ambiguous legacy records are blocked
            // by the cross-branch existence check below.
            $hasOtherBranchDocument = $client->documents()
                ->whereNotNull('filial_id')
                ->where('filial_id', '<>', $filialId)
                ->exists();

            if ($hasOtherBranchDocument) {
                throw ValidationException::withMessages([
                    'client_id' => 'Bu mijoz boshqa filialga tegishli.',
                ]);
            }

            $client->forceFill(['filial_id' => $filialId])->save();
        }

        if ((int) $client->filial_id !== $filialId) {
            throw ValidationException::withMessages([
                'client_id' => 'Bu mijoz boshqa filialga tegishli.',
            ]);
        }

        return (int) $client->getKey();
    }

    protected function calculateDocumentPricing(
        Request $request,
        ?DocumentsModel $existing = null,
        ?int $filialId = null
    ): array
    {
        $value = function (string $key, mixed $fallback = null) use ($request): mixed {
            return $request->has($key) ? $request->input($key) : $fallback;
        };

        $serviceId = (int) ($value('service_id', $value('service', $existing?->service_id)) ?: 0);
        $documentTypeId = $value('document_type_id', $value('document_type', $existing?->document_type_id));
        $directionTypeId = $value('direction_type_id', $value('direction_type', $existing?->direction_type_id));
        $consulateTypeId = $value('consulate_type_id', $value('legalization_id', $existing?->consulate_type_id));
        $processMode = (string) ($value('process_mode', $existing?->process_mode ?: 'service') ?: 'service');
        $selectionMode = $value('selection_mode', $existing?->selection_mode);
        $apostilGroup1Id = $value('apostil_group1_id', $existing?->apostil_group1_id);
        $apostilGroup2Id = $value('apostil_group2_id', $existing?->apostil_group2_id);
        $consulId = $value('consul_id', $existing?->consul_id);
        $requestedVariant = $request->has('pricing_variant') && $request->input('pricing_variant') !== null && $request->input('pricing_variant') !== ''
            ? $request->input('pricing_variant')
            : ($request->has('variant') && $request->input('variant') !== null && $request->input('variant') !== ''
                ? $request->input('variant')
                : data_get($existing?->pricing_context, 'variant', 'standard'));
        $variant = (string) ($requestedVariant ?: 'standard');
        $seasonCode = $request->has('season_code') && $request->input('season_code') !== null && $request->input('season_code') !== ''
            ? $request->input('season_code')
            : data_get($existing?->pricing_context, 'season_code');
        $partnerId = $existing?->order?->partner_id;
        $requestedPartnerId = $value('partner_id');
        if ($requestedPartnerId && !auth()->user()?->hasAnyRole(['super_admin', 'admin_manager'])) {
            throw ValidationException::withMessages([
                'partner_id' => 'Corporate partner narxini faqat vakolatli admin tanlashi mumkin.',
            ]);
        }
        if (!$partnerId && $requestedPartnerId && auth()->user()?->hasAnyRole(['super_admin', 'admin_manager'])) {
            $partnerId = (int) $requestedPartnerId;
        }
        $pricingContext = [
            'variant' => $variant,
            'season_code' => $seasonCode,
            'filial_id' => $filialId,
            'partner_id' => $partnerId ? (int) $partnerId : null,
            'as_of' => ($request->has('pricing_as_of') && $request->input('pricing_as_of') !== null && $request->input('pricing_as_of') !== '')
                ? $request->input('pricing_as_of')
                : data_get($existing?->pricing_context, 'as_of', $existing?->created_at ?: now()),
        ];

        if (!in_array($processMode, ['service', 'apostil', 'consul'], true)) {
            throw ValidationException::withMessages([
                'process_mode' => 'Jarayon turi noto‘g‘ri.',
            ]);
        }

        if (strtolower($variant) === 'seasonal' && blank($seasonCode)) {
            throw ValidationException::withMessages([
                'season_code' => 'Seasonal narx uchun season code kiritilishi shart.',
            ]);
        }

        if ($processMode !== 'apostil') {
            $directionTypeId = null;
        }

        $service = ServicesModel::query()->findOrFail($serviceId);
        $pricingService = app(PricingService::class);
        $serviceQuote = $pricingService->resolveService($service, $filialId, $pricingContext);
        $servicePrice = (float) $serviceQuote['price'];
        $expressQuote = null;
        if (in_array($pricingService->variant($pricingContext), ['express', 'rush'], true)) {
            $requestedServiceQuote = $serviceQuote;
            $standardServiceQuote = $pricingService->resolveService(
                $service,
                $filialId,
                [...$pricingContext, 'variant' => 'standard'],
            );
            $expressQuote = $pricingService->resolveFixed(
                'express_fee',
                $service->id,
                'service:' . $service->id,
                'Express / rush fee',
                0,
                0,
                $filialId,
                $pricingContext,
            );

            if (!$expressQuote['tariff_id'] && (float) $expressQuote['price'] <= 0
                && (float) $requestedServiceQuote['price'] > (float) $standardServiceQuote['price']) {
                $expressQuote = [
                    ...$requestedServiceQuote,
                    'line_type' => 'express_fee',
                    'name' => 'Express / rush fee',
                    'price' => round((float) $requestedServiceQuote['price'] - (float) $standardServiceQuote['price'], 2),
                    'cost_amount' => round((float) $requestedServiceQuote['cost_amount'] - (float) $standardServiceQuote['cost_amount'], 2),
                    'source' => 'derived',
                ];
            }

            // Express/rush is represented as the normal base service plus a
            // separate fee whenever that fee is configured.
            if ($expressQuote['tariff_id'] || $expressQuote['price'] > 0) {
                $serviceQuote = $standardServiceQuote;
                $serviceQuote['context'] = $pricingService->quoteContext($pricingContext);
                $serviceQuote['requested_variant'] = $pricingService->variant($pricingContext);
                $servicePrice = (float) $serviceQuote['price'];
            }
        }

        [$addonsTotal, $addonsDeadline, $addonsAttach, $addonQuotes] = $this->buildAddonsFromRequest(
            $request,
            $serviceId,
            $documentTypeId,
            $directionTypeId,
            $existing,
            $filialId,
            $pricingContext,
        );

        $extras = $this->calculateProcessExtras(
            $processMode,
            $apostilGroup1Id,
            $apostilGroup2Id,
            $consulId,
            $consulateTypeId,
            $filialId,
            $pricingContext,
        );

        $addonsTotal += $extras['price'];
        if ($expressQuote && ($expressQuote['tariff_id'] || $expressQuote['price'] > 0)) {
            $addonsTotal += $expressQuote['price'];
        }
        $deadlineTime = (int) ($service->deadline ?? 0) + $addonsDeadline + $extras['deadline'];
        if (($serviceQuote['deadline'] ?? null) !== null) {
            $deadlineTime = (int) $serviceQuote['deadline'] + $addonsDeadline + $extras['deadline'];
        }
        $totalPrice = round($servicePrice + $addonsTotal, 2);
        $discountInput = round((float) ($request->input('discount') ?? 0), 2);

        $this->validateDiscountInput($request, $discountInput, $totalPrice);
        $discountAmount = min(
            $this->resolveAppliedDiscountAmount($request, $discountInput, $totalPrice),
            $totalPrice
        );
        $taxInput = $request->has('tax_percent') && $request->input('tax_percent') !== null && $request->input('tax_percent') !== ''
            ? $request->input('tax_percent')
            : data_get($existing?->pricing_snapshot, 'tax_percent', 0);
        $taxPercent = round((float) $taxInput, 2);
        $taxAmount = round(max($totalPrice - $discountAmount, 0) * ($taxPercent / 100), 2);
        $discountPercent = $totalPrice > 0 ? round(($discountAmount / $totalPrice) * 100, 2) : 0;
        $approvalStatus = $pricingService->assertDiscountApproval(
            $discountPercent,
            $discountAmount,
            auth()->user(),
            $existing,
            $request->integer('pricing_approval_id') ?: null,
            $request->integer('order_id') ?: null,
            $request->input('pricing_approval_token'),
        );

        $lineSnapshots = [
            [
                'line_type' => 'base_service',
                'source_id' => $serviceQuote['source_id'],
                'name' => $serviceQuote['name'],
                'quantity' => 1,
                'unit_price' => $serviceQuote['price'],
                'total_price' => $serviceQuote['price'],
                'cost_amount' => $serviceQuote['cost_amount'],
                'price_tariff_id' => $serviceQuote['tariff_id'],
                'effective_from' => $serviceQuote['effective_from'],
                'pricing_context' => $serviceQuote['context'],
                'price_source' => $serviceQuote['source'],
                'variant_applied' => $serviceQuote['requested_variant'] ?? $serviceQuote['variant_applied'],
                'price_key' => $serviceQuote['price_key'] ?? null,
            ],
            ...($expressQuote && ($expressQuote['tariff_id'] || $expressQuote['price'] > 0) ? [[
                'line_type' => 'express_fee',
                'source_id' => $expressQuote['source_id'],
                'name' => $expressQuote['name'],
                'quantity' => 1,
                'unit_price' => $expressQuote['price'],
                'total_price' => $expressQuote['price'],
                'cost_amount' => $expressQuote['cost_amount'],
                'price_tariff_id' => $expressQuote['tariff_id'],
                'effective_from' => $expressQuote['effective_from'],
                'pricing_context' => $expressQuote['context'],
                'price_source' => $expressQuote['source'],
                'variant_applied' => $expressQuote['variant_applied'],
                'price_key' => $expressQuote['price_key'] ?? null,
            ]] : []),
            ...array_map(fn (array $quote): array => [
                'line_type' => $quote['line_type'],
                'source_id' => $quote['source_id'],
                'name' => $quote['name'],
                'quantity' => 1,
                'unit_price' => $quote['price'],
                'total_price' => $quote['price'],
                'cost_amount' => $quote['cost_amount'],
                'price_tariff_id' => $quote['tariff_id'],
                'effective_from' => $quote['effective_from'],
                'pricing_context' => $quote['context'],
                'price_source' => $quote['source'],
                'variant_applied' => $quote['variant_applied'],
                'price_key' => $quote['price_key'] ?? null,
            ], $addonQuotes),
            ...array_map(fn (array $charge): array => [
                'line_type' => $charge['line_type'] ?? $this->pricingLineTypeForCharge($charge['type']),
                'source_id' => $charge['source_id'],
                'name' => $charge['name'] ?: 'Qo‘shimcha jarayon',
                'quantity' => 1,
                'unit_price' => $charge['price'],
                'total_price' => $charge['price'],
                'cost_amount' => 0,
                'price_tariff_id' => $charge['tariff_id'] ?? null,
                'effective_from' => $charge['effective_from'] ?? null,
                'pricing_context' => $charge['pricing_context'] ?? $pricingService->quoteContext($pricingContext),
                'price_source' => !empty($charge['tariff_id']) ? 'tariff' : 'legacy',
                'variant_applied' => data_get($charge, 'pricing_context.variant', 'standard'),
                'price_key' => null,
            ], $extras['charges']),
        ];

        if ($discountAmount > 0) {
            $lineSnapshots[] = [
                'line_type' => 'discount',
                'source_id' => null,
                'name' => 'Chegirma',
                'quantity' => 1,
                'unit_price' => -$discountAmount,
                'total_price' => -$discountAmount,
                'cost_amount' => 0,
                'price_tariff_id' => null,
                'effective_from' => null,
                'pricing_context' => [
                    ...$pricingService->quoteContext($pricingContext),
                    'discount_percent' => $discountPercent,
                    'approval_status' => $approvalStatus,
                ],
                'price_source' => 'calculated',
                'variant_applied' => $pricingService->variant($pricingContext),
                'price_key' => null,
            ];
        }

        if ($taxAmount > 0) {
            $taxQuote = $pricingService->resolveFixed(
                'tax',
                null,
                'tax:' . $taxPercent,
                'Tax (' . $taxPercent . '%)',
                $taxAmount,
                0,
                $filialId,
                $pricingContext,
            );
            $lineSnapshots[] = [
                'line_type' => 'tax',
                'source_id' => $taxQuote['source_id'],
                'name' => $taxQuote['name'],
                'quantity' => 1,
                'unit_price' => $taxAmount,
                'total_price' => $taxAmount,
                'cost_amount' => $taxQuote['cost_amount'],
                'price_tariff_id' => $taxQuote['tariff_id'],
                'effective_from' => $taxQuote['effective_from'],
                'pricing_context' => [
                    ...$taxQuote['context'],
                    'tax_percent' => $taxPercent,
                ],
                'price_source' => $taxQuote['source'],
                'variant_applied' => $taxQuote['variant_applied'],
                'price_key' => $taxQuote['price_key'] ?? null,
            ];
        }

        return [
            'service_id' => $serviceId,
            'service_price' => round($servicePrice, 2),
            'addons_total_price' => round($addonsTotal, 2),
            'deadline_time' => max($deadlineTime, 0),
            'total_price' => $totalPrice,
            'final_price' => round(max($totalPrice - $discountAmount, 0) + $taxAmount, 2),
            'discount' => $discountInput,
            'discount_amount' => round($discountAmount, 2),
            'discount_percent' => $discountPercent,
            'discount_approval_status' => $approvalStatus,
            'pricing_context' => $pricingService->quoteContext($pricingContext),
            'pricing_snapshot' => [
                'version' => 1,
                'calculated_at' => now()->toIso8601String(),
                'line_items' => $lineSnapshots,
                'subtotal' => $totalPrice,
                'discount_amount' => round($discountAmount, 2),
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'final_price' => round(max($totalPrice - $discountAmount, 0) + $taxAmount, 2),
                'currency' => config('pricing.default_currency', 'UZS'),
            ],
            'document_type_id' => $documentTypeId,
            'direction_type_id' => $processMode === 'apostil' ? $directionTypeId : null,
            'consulate_type_id' => $processMode === 'consul' ? $consulateTypeId : null,
            'process_mode' => $processMode,
            'selection_mode' => $processMode === 'consul' ? $selectionMode : null,
            'apostil_group1_id' => $processMode === 'apostil' ? $apostilGroup1Id : null,
            'apostil_group2_id' => $processMode === 'apostil' ? $apostilGroup2Id : null,
            'consul_id' => $processMode === 'consul' ? $consulId : null,
            'addons' => $addonsAttach,
            'extras' => $extras,
        ];
    }

    protected function updateDocumentFromRequest(DocumentsModel $document, Request $request): DocumentsModel
    {
        return DB::transaction(function () use ($document, $request) {
            $document = DocumentsModel::query()
                ->whereKey($document->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $pricing = $this->calculateDocumentPricing($request, $document, (int) $document->filial_id);
            $currentPaid = round((float) $document->paid_amount, 2);

            if ($currentPaid > $pricing['final_price']) {
                throw ValidationException::withMessages([
                    'final_price' => 'Yangi final narx mavjud to‘lovdan kam bo‘lishi mumkin emas.',
                ]);
            }

            $document->update([
                'service_id' => $pricing['service_id'],
                'service_price' => $pricing['service_price'],
                'addons_total_price' => $pricing['addons_total_price'],
                'deadline_time' => $pricing['deadline_time'],
                'final_price' => $pricing['final_price'],
                'discount' => $pricing['discount'],
                'pricing_snapshot' => $pricing['pricing_snapshot'],
                'pricing_context' => $pricing['pricing_context'],
                'pricing_locked_at' => $document->pricing_locked_at ?: now(),
                'discount_approval_status' => $pricing['discount_approval_status'],
                'description' => $request->input('description'),
                'document_type_id' => $pricing['document_type_id'],
                'direction_type_id' => $pricing['direction_type_id'],
                'consulate_type_id' => $pricing['consulate_type_id'],
                'process_mode' => $pricing['process_mode'],
                'apostil_group1_id' => $pricing['apostil_group1_id'],
                'apostil_group2_id' => $pricing['apostil_group2_id'],
                'consul_id' => $pricing['consul_id'],
            ]);

            $document->document_type_addons()->sync($pricing['addons']['document']);
            $document->document_direction_addons()->sync($pricing['addons']['direction']);
            $document->addons()->sync($pricing['addons']['service']);

            DB::table('document_process_charges')
                ->where('document_id', $document->id)
                ->delete();

            if (!empty($pricing['extras']['charges'])) {
                $now = now();
                $rows = [];
                foreach ($pricing['extras']['charges'] as $charge) {
                    $rows[] = [
                        'document_id' => $document->id,
                        'charge_type' => $charge['type'],
                        'source_id' => $charge['source_id'],
                        'price' => $charge['price'],
                        'days' => $charge['days'],
                        'name' => $charge['name'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('document_process_charges')->insert($rows);
            }

            $workflow = app(\App\Services\DocumentWorkflowService::class);
            $workflow->initialize($document, auth()->user());
            if ($document->assigned_to_id === null) {
                $workflow->autoAssign($document, auth()->user());
            }

            $this->applyRequestedPayment($document, $request);

            $orderService = app(\App\Services\OrderCaseService::class);
            $order = $document->order_id
                ? Order::query()->findOrFail((int) $document->order_id)
                : null;

            if ($order) {
                $orderService->attachDocument($order, $document);
            } else {
                $orderService->createForDocument($document);
            }

            $workflow->syncDerivedStatus($document->refresh(), auth()->user());

            return $document->refresh();
        });
    }

    protected function recordPayment(DocumentsModel $document, Request $request): void
    {
        $amount = round((float) $request->input('amount'), 2);
        $paymentType = (string) $request->input('payment_type');

        $this->assertPayment($amount, $paymentType);

        if ($document->order_id) {
            app(OrderCaseService::class)->recordPayment(
                Order::query()->findOrFail((int) $document->order_id),
                $amount,
                $paymentType,
                auth()->user(),
                (int) $document->id,
                [
                    'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->input('cash_session_id') : null,
                    'online_transaction_id' => $request->input('online_transaction_id'),
                    'payment_proof' => $request->file('payment_proof'),
                ]
            );

            return;
        }

        $balance = round((float) $document->final_price - (float) $document->paid_amount, 2);
        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' => 'To‘lov summasi qoldiqdan oshmasligi kerak.',
            ]);
        }

        app(PaymentService::class)->recordForDocument(
            $document,
            $amount,
            $paymentType,
            auth()->user(),
            [
                'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->input('cash_session_id') : null,
                'online_transaction_id' => $request->input('online_transaction_id'),
                'payment_proof' => $request->file('payment_proof'),
            ],
        );

        if ($document->order_id) {
            app(OrderCaseService::class)->recalculate(
                Order::query()->findOrFail((int) $document->order_id)
            );
        }
    }

    protected function applyRequestedPayment(DocumentsModel $document, Request $request): void
    {
        if (!$request->has('paid_amount') || $request->input('paid_amount') === null || $request->input('paid_amount') === '') {
            return;
        }

        $targetPaid = round((float) $request->input('paid_amount'), 2);
        $currentPaid = round((float) $document->paid_amount, 2);

        if ($targetPaid < $currentPaid) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Mavjud to‘lovni hujjat tahriridan kamaytirib bo‘lmaydi.',
            ]);
        }

        if ($targetPaid > (float) $document->final_price) {
            throw ValidationException::withMessages([
                'paid_amount' => 'To‘lov final narxdan oshmasligi kerak.',
            ]);
        }

        $difference = round($targetPaid - $currentPaid, 2);
        if ($difference <= 0) {
            return;
        }

        $paymentType = (string) $request->input('payment_type');
        $this->assertPayment($difference, $paymentType);

        if ($document->order_id) {
            app(OrderCaseService::class)->recordPayment(
                Order::query()->findOrFail((int) $document->order_id),
                $difference,
                $paymentType,
                auth()->user(),
                (int) $document->id,
                [
                    'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->input('cash_session_id') : null,
                    'online_transaction_id' => $request->input('online_transaction_id'),
                    'payment_proof' => $request->file('payment_proof'),
                ]
            );

            return;
        }

        app(PaymentService::class)->recordForDocument(
            $document,
            $difference,
            $paymentType,
            auth()->user(),
            [
                'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->input('cash_session_id') : null,
                'online_transaction_id' => $request->input('online_transaction_id'),
                'payment_proof' => $request->file('payment_proof'),
            ],
        );
    }

    protected function resolveInitialPaidAmount(Request $request, float $finalPrice): float
    {
        $paidAmount = round((float) $request->input('paid_amount', $request->input('payment_amount', 0)), 2);
        $paymentType = (string) $request->input('payment_type');

        if ($paidAmount < 0 || $paidAmount > $finalPrice) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Boshlang‘ich to‘lov final narxdan oshmasligi kerak.',
            ]);
        }

        if ($paidAmount > 0) {
            $this->assertPayment($paidAmount, $paymentType);
        }

        return $paidAmount;
    }

    protected function assertPayment(float $amount, string $paymentType): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'To‘lov summasi noldan katta bo‘lishi kerak.',
            ]);
        }

        if (!in_array($paymentType, PaymentsModel::TYPES, true)) {
            throw ValidationException::withMessages([
                'payment_type' => 'To‘lov turi noto‘g‘ri.',
            ]);
        }
    }

    protected function validateDiscountInput(Request $request, float $discountInput, float $totalPrice): void
    {
        if ($discountInput < 0) {
            throw ValidationException::withMessages([
                'discount' => 'Chegirma manfiy bo‘lishi mumkin emas.',
            ]);
        }

        $isPercent = $this->discountIsPercent($request);
        if ($isPercent && $discountInput > 100) {
            throw ValidationException::withMessages([
                'discount' => 'Foiz chegirma 100 dan oshmasligi kerak.',
            ]);
        }

        if (!$isPercent && $discountInput > $totalPrice) {
            throw ValidationException::withMessages([
                'discount' => 'Chegirma jami narxdan oshmasligi kerak.',
            ]);
        }
    }

    protected function discountIsPercent(Request $request): bool
    {
        if ($request->input('discount_type') === 'percent') {
            return true;
        }

        if ($request->input('discount_type') === 'amount') {
            return false;
        }

        return $request->has('final_price');
    }

    protected function assertValidDocumentFile(mixed $file): void
    {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $extension = strtolower((string) ($file instanceof UploadedFile ? $file->getClientOriginalExtension() : ''));

        if (!$file instanceof UploadedFile || !$file->isValid() || !in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'files' => 'Faqat PDF, DOC, DOCX, JPG yoki PNG fayllarga ruxsat beriladi.',
            ]);
        }

        if ((int) $file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'files' => 'Fayl hajmi 10 MB dan oshmasligi kerak.',
            ]);
        }
    }

    protected function buildAddonsFromRequest(
        Request $request,
        ?int $serviceId = null,
        ?int $documentTypeId = null,
        ?int $directionTypeId = null,
        ?DocumentsModel $existing = null,
        ?int $filialId = null,
        array $pricingContext = [],
    ): array
    {
        $hasAddonInput = $request->has('selected_addons') || $request->has('addons');
        $selectedAddons = [];

        if ($request->has('selected_addons')) {
            $rawSelectedAddons = $request->input('selected_addons');

            if (is_array($rawSelectedAddons)) {
                $selectedAddons = $rawSelectedAddons;
            } elseif (is_string($rawSelectedAddons) && trim($rawSelectedAddons) !== '') {
                $decoded = json_decode($rawSelectedAddons, true);
                if (is_array($decoded)) {
                    $selectedAddons = $decoded;
                }
            }
        }

        if (empty($selectedAddons) && $request->filled('addons')) {
            $selectedAddons = array_map(function ($id) {
                return ['id' => $id, 'sourceType' => 'service'];
            }, (array) $request->input('addons'));
        }

        if (!$hasAddonInput && $existing) {
            $existing->loadMissing([
                'document_type_addons',
                'document_direction_addons',
                'addons',
            ]);

            $selectedAddons = array_merge(
                $existing->document_type_addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'document'])
                    ->all(),
                $existing->document_direction_addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'direction'])
                    ->all(),
                $existing->addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'service'])
                    ->all(),
            );
        }

        $byType = [
            'document' => [],
            'direction' => [],
            'service' => [],
        ];

        foreach ($selectedAddons as $addon) {
            $type = $addon['sourceType'] ?? $addon['type'] ?? null;
            $id = $addon['id'] ?? null;
            if ($type && isset($byType[$type]) && $id) {
                $byType[$type][] = (int) $id;
            }
        }

        foreach ($byType as $type => $ids) {
            $byType[$type] = array_values(array_unique($ids));
        }

        $total = 0;
        $deadline = 0;
        $quotes = [];
        $attach = [
            'document' => [],
            'direction' => [],
            'service' => [],
        ];

        if (!empty($byType['document'])) {
            $addons = DocumentTypeAdditionModel::query()
                ->where('document_type_id', $documentTypeId)
                ->whereIn('id', $byType['document'])
                ->get();

            $this->assertAllAddonsResolved($byType['document'], $addons->modelKeys(), 'document');
            foreach ($addons as $addon) {
                $quote = app(PricingService::class)->resolveFixed(
                    'addon',
                    $addon->id,
                    'document_addon:' . $addon->id,
                    $addon->name,
                    (float) ($addon->amount ?? 0),
                    (int) ($addon->day ?? 0),
                    $filialId,
                    $pricingContext,
                );
                $price = $quote['price'];
                $addonDeadline = $addon->day ?? 0;
                $total += $price;
                $deadline += $addonDeadline;
                $attach['document'][$addon->id] = [
                    'addon_price' => $price,
                ];
                $quotes[] = $quote;
            }
        }

        if (!empty($byType['direction'])) {
            $addons = DocumentDirectionAdditionModel::query()
                ->where('document_direction_id', $directionTypeId)
                ->whereIn('id', $byType['direction'])
                ->get();

            $this->assertAllAddonsResolved($byType['direction'], $addons->modelKeys(), 'direction');
            foreach ($addons as $addon) {
                $quote = app(PricingService::class)->resolveFixed(
                    'addon',
                    $addon->id,
                    'direction_addon:' . $addon->id,
                    $addon->name,
                    (float) ($addon->amount ?? 0),
                    (int) ($addon->day ?? 0),
                    $filialId,
                    $pricingContext,
                );
                $price = $quote['price'];
                $addonDeadline = $addon->day ?? 0;
                $total += $price;
                $deadline += $addonDeadline;
                $attach['direction'][$addon->id] = [
                    'addon_price' => $price,
                ];
                $quotes[] = $quote;
            }
        }

        if (!empty($byType['service'])) {
            $addons = ServiceAddonModel::query()
                ->where('service_id', $serviceId)
                ->whereIn('id', $byType['service'])
                ->get();

            $this->assertAllAddonsResolved($byType['service'], $addons->modelKeys(), 'service');
            foreach ($addons as $addon) {
                $quote = app(PricingService::class)->resolveServiceAddon($addon, $filialId, $pricingContext);
                $price = $quote['price'];
                $addonDeadline = $quote['deadline'];
                $total += $price;
                $deadline += $addonDeadline;
                $attach['service'][$addon->id] = [
                    'addon_price' => $price,
                    'addon_deadline' => $addonDeadline,
                ];
                $quotes[] = $quote;
            }
        }

        return [$total, $deadline, $attach, $quotes];
    }

    protected function assertAllAddonsResolved(array $requestedIds, array $resolvedIds, string $type): void
    {
        $missing = array_values(array_diff($requestedIds, array_map('intval', $resolvedIds)));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'selected_addons' => "Tanlangan {$type} addon ushbu konfiguratsiyaga tegishli emas.",
            ]);
        }
    }

    protected function calculateProcessExtras(
        $processMode,
        $apostilGroup1Id,
        $apostilGroup2Id,
        $consulId,
        $consulateTypeId,
        ?int $filialId = null,
        array $pricingContext = [],
    ): array
    {
        $price = 0;
        $deadline = 0;
        $charges = [];

        if ($processMode === 'apostil') {
            if ($apostilGroup1Id) {
                $apostil = ApostilStatikModel::query()
                    ->whereKey($apostilGroup1Id)
                    ->where('group_id', 1)
                    ->first();
                if (!$apostil) {
                    throw ValidationException::withMessages([
                        'apostil_group1_id' => 'Birinchi apostil guruhi noto‘g‘ri tanlangan.',
                    ]);
                }
                if ($apostil) {
                    $quote = app(PricingService::class)->resolveFixed(
                        'apostille',
                        $apostil->id,
                        'apostille:' . $apostil->id,
                        $apostil->name ?? 'Apostil',
                        (float) ($apostil->price ?? 0),
                        (int) ($apostil->days ?? 0),
                        $filialId,
                        $pricingContext,
                    );
                    $itemPrice = $quote['price'];
                    $itemDays = $quote['deadline'];
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'apostil_group1',
                        'source_id' => $apostil->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $apostil->name ?? null,
                        'line_type' => 'apostille',
                        'tariff_id' => $quote['tariff_id'],
                        'effective_from' => $quote['effective_from'],
                        'pricing_context' => $quote['context'],
                    ];
                }
            }

            if ($apostilGroup2Id) {
                $apostil = ApostilStatikModel::query()
                    ->whereKey($apostilGroup2Id)
                    ->where('group_id', 2)
                    ->first();
                if (!$apostil) {
                    throw ValidationException::withMessages([
                        'apostil_group2_id' => 'Ikkinchi apostil guruhi noto‘g‘ri tanlangan.',
                    ]);
                }
                if ($apostil) {
                    $quote = app(PricingService::class)->resolveFixed(
                        'apostille',
                        $apostil->id,
                        'apostille:' . $apostil->id,
                        $apostil->name ?? 'Apostil',
                        (float) ($apostil->price ?? 0),
                        (int) ($apostil->days ?? 0),
                        $filialId,
                        $pricingContext,
                    );
                    $itemPrice = $quote['price'];
                    $itemDays = $quote['deadline'];
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'apostil_group2',
                        'source_id' => $apostil->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $apostil->name ?? null,
                        'line_type' => 'apostille',
                        'tariff_id' => $quote['tariff_id'],
                        'effective_from' => $quote['effective_from'],
                        'pricing_context' => $quote['context'],
                    ];
                }
            }
        }

        if ($processMode === 'consul') {
            if ($consulId) {
                $consul = ConsulModel::find($consulId);
                if (!$consul) {
                    throw ValidationException::withMessages([
                        'consul_id' => 'Konsul xizmati topilmadi.',
                    ]);
                }
                if ($consul) {
                    $quote = app(PricingService::class)->resolveFixed(
                        'consulate',
                        $consul->id,
                        'consul:' . $consul->id,
                        $consul->name ?? 'Konsul',
                        (float) ($consul->amount ?? 0),
                        (int) ($consul->day ?? 0),
                        $filialId,
                        $pricingContext,
                    );
                    $itemPrice = $quote['price'];
                    $itemDays = $quote['deadline'];
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'consul',
                        'source_id' => $consul->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $consul->name ?? null,
                        'line_type' => 'consulate',
                        'tariff_id' => $quote['tariff_id'],
                        'effective_from' => $quote['effective_from'],
                        'pricing_context' => $quote['context'],
                    ];
                }
            }

            if ($consulateTypeId) {
                $consulate = ConsulationTypeModel::find($consulateTypeId);
                if (!$consulate) {
                    throw ValidationException::withMessages([
                        'consulate_type_id' => 'Konsullik xizmati topilmadi.',
                    ]);
                }
                if ($consulate) {
                    $quote = app(PricingService::class)->resolveFixed(
                        'consulate',
                        $consulate->id,
                        'consulate:' . $consulate->id,
                        $consulate->name ?? 'Konsullik',
                        (float) ($consulate->amount ?? 0),
                        (int) ($consulate->day ?? 0),
                        $filialId,
                        $pricingContext,
                    );
                    $itemPrice = $quote['price'];
                    $itemDays = $quote['deadline'];
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'consulate',
                        'source_id' => $consulate->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $consulate->name ?? null,
                        'line_type' => 'consulate',
                        'tariff_id' => $quote['tariff_id'],
                        'effective_from' => $quote['effective_from'],
                        'pricing_context' => $quote['context'],
                    ];
                }
            }
        }

        return [
            'price' => $price,
            'deadline' => $deadline,
            'charges' => $charges,
        ];
    }

    protected function calculateDiscountAmount(Request $request, float $discountInput, float $totalPrice): float
    {
        if ($discountInput <= 0) {
            return 0;
        }

        $isPercent = $this->discountIsPercent($request);

        return $isPercent ? ($totalPrice * ($discountInput / 100)) : $discountInput;
    }

    protected function pricingLineTypeForCharge(string $type): string
    {
        return str_starts_with($type, 'apostil') ? 'apostille' : 'consulate';
    }

    protected function resolveAppliedDiscountAmount(Request $request, float $discountInput, float $totalPrice): float
    {
        $templateId = $request->input('package_template_id');

        if ($templateId) {
            $template = PackageTemplate::query()
                ->active()
                ->find($templateId);

            if ($template && PackageTemplateSupport::matchesRequest($template, $request)) {
                return max($totalPrice - (float) $template->promo_price, 0);
            }
        }

        return $this->calculateDiscountAmount($request, $discountInput, $totalPrice);
    }

    protected function generateDocumentCode(DocumentsModel $document, int $filialId): string
    {
        $prefix = $this->resolveFilialCode($filialId);
        $documentCode = $prefix . '-' . (1000000 + (int) $document->getKey());

        if (DocumentsModel::query()
            ->where('document_code', $documentCode)
            ->where('id', '<>', $document->getKey())
            ->exists()) {
            $documentCode .= '-' . $document->getKey();
        }

        return $documentCode;
    }
}
