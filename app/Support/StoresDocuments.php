<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentsModel;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\DocumentFileModel as DocumentFile;
use App\Models\ServiceAddonModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;

trait StoresDocuments
{
    protected function storeDocumentFromRequest(Request $request): DocumentsModel
    {
        return DB::transaction(function () use ($request) {
            $serviceId = $request->input('service_id') ?? $request->input('service');
            $documentTypeId = $request->input('document_type_id') ?? $request->input('document_type');
            $directionTypeId = $request->input('direction_type_id') ?? $request->input('direction_type');
            $consulateTypeId = $request->input('consulate_type_id') ?? $request->input('legalization_id');

            $processMode = $request->input('process_mode');
            $apostilGroup1Id = $request->input('apostil_group1_id');
            $apostilGroup2Id = $request->input('apostil_group2_id');
            $consulId = $request->input('consul_id');

            $service = ServicesModel::findOrFail($serviceId);
            $servicePrice = $service->price;
            $deadlineTime = $service->deadline ?? 0;

            [$addonsTotal, $addonsDeadline, $addonsAttach] = $this->buildAddonsFromRequest($request);
            $deadlineTime += $addonsDeadline;

            $extras = $this->calculateProcessExtras(
                $processMode,
                $apostilGroup1Id,
                $apostilGroup2Id,
                $consulId,
                $consulateTypeId
            );

            $addonsTotal += $extras['price'];
            $deadlineTime += $extras['deadline'];

            $discountInput = (float) ($request->input('discount') ?? 0);
            $totalPrice = $servicePrice + $addonsTotal;
            $discountAmount = $this->calculateDiscountAmount($request, $discountInput, $totalPrice);
            $finalPrice = $totalPrice - $discountAmount;

            $document = DocumentsModel::create([
                'client_id'          => $request->input('client_id'),
                'service_id'         => $serviceId,
                'service_price'      => $servicePrice,
                'addons_total_price' => $addonsTotal,
                'deadline_time'      => $deadlineTime,
                'final_price'        => $finalPrice,
                'paid_amount'        => $request->input('paid_amount', $request->input('payment_amount', 0)),
                'discount'           => $discountInput,
                'user_id'            => auth()->id(),
                'description'        => $request->input('description'),
                'filial_id'          => auth()->user()->filial_id,
                'document_code'      => $this->generateDocumentCode(),
                'document_type_id'   => $documentTypeId,
                'direction_type_id'  => $directionTypeId,
                'consulate_type_id'  => $consulateTypeId,
                'process_mode'       => $processMode,
                'apostil_group1_id'  => $apostilGroup1Id,
                'apostil_group2_id'  => $apostilGroup2Id,
                'consul_id'          => $consulId,
            ]);

            if (!empty($extras['charges'])) {
                $now = now();
                $rows = [];
                foreach ($extras['charges'] as $charge) {
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

            if (!empty($addonsAttach['document'])) {
                $document->document_type_addons()->attach($addonsAttach['document']);
            }

            if (!empty($addonsAttach['direction'])) {
                $document->document_direction_addons()->attach($addonsAttach['direction']);
            }

            if (!empty($addonsAttach['service'])) {
                $document->addons()->attach($addonsAttach['service']);
            }

            $paidAmount = $request->input('paid_amount', $request->input('payment_amount', 0));
            $paymentType = $request->input('payment_type');
            if ($paidAmount && $paymentType) {
                PaymentsModel::create([
                    'document_id'      => $document->id,
                    'amount'           => $paidAmount,
                    'payment_type'     => $paymentType,
                    'paid_by_admin_id' => auth()->id(),
                ]);
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('documents/' . $document->document_code, 'public');

                    DocumentFile::create([
                        'document_id'   => $document->id,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path'     => $path,
                        'file_type'     => $file->getClientMimeType(),
                        'file_size'     => $file->getSize(),
                    ]);
                }
            }

            return $document;
        });
    }

    protected function buildAddonsFromRequest(Request $request): array
    {
        $selectedAddons = [];

        if ($request->filled('selected_addons')) {
            $decoded = json_decode($request->input('selected_addons'), true);
            if (is_array($decoded)) {
                $selectedAddons = $decoded;
            }
        }

        if (empty($selectedAddons) && $request->filled('addons')) {
            $selectedAddons = array_map(function ($id) {
                return ['id' => $id, 'sourceType' => 'service'];
            }, (array) $request->input('addons'));
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
        $attach = [
            'document' => [],
            'direction' => [],
            'service' => [],
        ];

        if (!empty($byType['document'])) {
            $addons = DocumentTypeAdditionModel::whereIn('id', $byType['document'])->get();
            foreach ($addons as $addon) {
                $price = $addon->amount ?? 0;
                $total += $price;
                $attach['document'][$addon->id] = ['addon_price' => $price];
            }
        }

        if (!empty($byType['direction'])) {
            $addons = DocumentDirectionAdditionModel::whereIn('id', $byType['direction'])->get();
            foreach ($addons as $addon) {
                $price = $addon->amount ?? 0;
                $total += $price;
                $attach['direction'][$addon->id] = ['addon_price' => $price];
            }
        }

        if (!empty($byType['service'])) {
            $addons = ServiceAddonModel::whereIn('id', $byType['service'])->get();
            foreach ($addons as $addon) {
                $price = $addon->price ?? 0;
                $addonDeadline = $addon->deadline ?? 0;
                $total += $price;
                $deadline += $addonDeadline;
                $attach['service'][$addon->id] = [
                    'addon_price' => $price,
                    'addon_deadline' => $addonDeadline,
                ];
            }
        }

        return [$total, $deadline, $attach];
    }

    protected function calculateProcessExtras($processMode, $apostilGroup1Id, $apostilGroup2Id, $consulId, $consulateTypeId): array
    {
        $price = 0;
        $deadline = 0;
        $charges = [];

        if ($processMode === 'apostil') {
            if ($apostilGroup1Id) {
                $apostil = ApostilStatikModel::find($apostilGroup1Id);
                if ($apostil) {
                    $itemPrice = $apostil->price ?? 0;
                    $itemDays = $apostil->days ?? 0;
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'apostil_group1',
                        'source_id' => $apostil->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $apostil->name ?? null,
                    ];
                }
            }

            if ($apostilGroup2Id) {
                $apostil = ApostilStatikModel::find($apostilGroup2Id);
                if ($apostil) {
                    $itemPrice = $apostil->price ?? 0;
                    $itemDays = $apostil->days ?? 0;
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'apostil_group2',
                        'source_id' => $apostil->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $apostil->name ?? null,
                    ];
                }
            }
        }

        if ($processMode === 'consul') {
            if ($consulId) {
                $consul = ConsulModel::find($consulId);
                if ($consul) {
                    $itemPrice = $consul->amount ?? 0;
                    $itemDays = $consul->day ?? 0;
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'consul',
                        'source_id' => $consul->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $consul->name ?? null,
                    ];
                }
            }

            if ($consulateTypeId) {
                $consulate = ConsulationTypeModel::find($consulateTypeId);
                if ($consulate) {
                    $itemPrice = $consulate->amount ?? 0;
                    $itemDays = $consulate->day ?? 0;
                    $price += $itemPrice;
                    $deadline += $itemDays;
                    $charges[] = [
                        'type' => 'consulate',
                        'source_id' => $consulate->id,
                        'price' => $itemPrice,
                        'days' => $itemDays,
                        'name' => $consulate->name ?? null,
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

        $discountType = $request->input('discount_type');
        $isPercent = $discountType === 'percent';

        if ($discountType === null && $request->has('final_price')) {
            $isPercent = true;
        }

        return $isPercent ? ($totalPrice * ($discountInput / 100)) : $discountInput;
    }

    protected function generateDocumentCode(): string
    {
        $code = Auth::user()->filial->code;
        $baseNumber = 1000000;
        $nextId = DocumentsModel::max('id') + 1;
        $number = $baseNumber + $nextId;
        $documentCode = $code . '-' . $number;

        while (DocumentsModel::where('document_code', $documentCode)->exists()) {
            $number++;
            $documentCode = $code . '-' . $number;
        }

        return $documentCode;
    }
}
