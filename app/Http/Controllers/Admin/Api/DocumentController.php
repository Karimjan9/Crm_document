<?php

namespace App\Http\Controllers\Admin\Api;

use App\Models\ClientsModel;
use Illuminate\Http\Request;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\DocumentsModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DocumentFileModel as DocumentFile;
use App\Models\ServiceAddonModel as ServiceAddon;
use App\Models\ConsulationTypeModel as ConsulationType;
use App\Models\DocumentTypeAdditionModel as DocumentTypeAddition;
use App\Models\DocumentDirectionAdditionModel as DocumentDirectionAddition;
use App\Support\StoresDocuments;

class DocumentController extends Controller
{
    use StoresDocuments;

    public function store(Request $request)
    {
        $data = $request->all();
        $data['service_id'] = $data['service_id'] ?? $data['service'] ?? null;
        $data['document_type_id'] = $data['document_type_id'] ?? $data['document_type'] ?? null;
        $data['direction_type_id'] = $data['direction_type_id'] ?? $data['direction_type'] ?? null;
        $data['consulate_type_id'] = $data['consulate_type_id'] ?? $data['legalization_id'] ?? null;

        $normalizeNullable = function ($value) {
            if ($value === null) {
                return null;
            }
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '' || strtolower($value) === 'null' || strtolower($value) === 'undefined') {
                    return null;
                }
            }
            return $value;
        };

        foreach ([
            'service_id',
            'document_type_id',
            'direction_type_id',
            'consulate_type_id',
            'process_mode',
            'apostil_group1_id',
            'apostil_group2_id',
            'consul_id',
            'discount',
            'payment_amount',
            'payment_type',
            'description',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $normalizeNullable($data[$field]);
            }
        }

        $request->merge($data);

        $validator = Validator::make($data, [
            'client_id' => 'required|integer|exists:clients,id',
            'service_id' => 'required|integer|exists:services,id',
            'document_type_id' => 'required|integer|exists:document_type,id',
            'direction_type_id' => 'required_if:process_mode,apostil|nullable|integer|exists:direction_type,id',
            'consulate_type_id' => 'required_if:process_mode,consul|nullable|integer|exists:consulates_type,id',
            'process_mode' => 'nullable|string|in:apostil,consul',
            'apostil_group1_id' => 'required_if:process_mode,apostil|nullable|integer|exists:apostil_static,id',
            'apostil_group2_id' => 'required_if:process_mode,apostil|nullable|integer|exists:apostil_static,id',
            'consul_id' => 'required_if:process_mode,consul|nullable|integer|exists:consul,id',
            'discount' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_type' => ['nullable', 'string', Rule::in(['cash', 'card', 'transfer', 'online', 'admin_entry'])],
            'description' => 'nullable|string|max:5000',
            'selected_addons' => 'nullable|json',
            'addons' => 'nullable|array',
            'addons.*' => 'nullable|integer|exists:service_addons,id',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $document = $this->storeDocumentFromRequest($request);

        return response()->json([
            'success' => true,
            'message' => 'Документ успешно создан',
            'data' => $document
        ], 200);
    }

    public function storeAll(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',

            'items.*.client_id' => 'nullable|exists:clients,id',
            'items.*.new_client.name' => 'nullable|string|max:255',
            'items.*.new_client.phone' => 'nullable|string|max:20',
            'items.*.new_client.desc' => 'nullable|string',

            'items.*.service_id' => 'required|exists:services,id',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*' => 'exists:service_addons,id',

            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.paid_amount' => 'nullable|numeric|min:0',
            'items.*.payment_type' => 'nullable|string',

            'items.*.description' => 'nullable|string',
            'items.*.document_type_id' => 'required|integer',
            'items.*.direction_type_id' => 'required|integer',
            'items.*.consulate_type_id' => 'required|integer',
        ]);

        DB::transaction(function () use ($data) {

            foreach ($data['items'] as $item) {

                /* ================= CLIENT ================= */

                $clientId = $item['client_id'] ?? null;

                if (! $clientId && ! empty($item['new_client'])) {
                    $client = ClientsModel::create([
                        'name'         => $item['new_client']['name'],
                        'phone_number' => $item['new_client']['phone'],
                        'description'  => $item['new_client']['desc'] ?? null,
                    ]);
                    $clientId = $client->id;
                }

                /* ================= SERVICE ================= */

                $service      = ServicesModel::findOrFail($item['service_id']);
                $servicePrice = $service->price;
                $deadlineTime = $service->deadline;

                /* ================= ADDONS ================= */

                $addonsTotal = 0;
                $addonsData  = [];

                if (! empty($item['addons'])) {
                    $addons = DB::table('service_addons')
                        ->whereIn('id', $item['addons'])
                        ->get();

                    foreach ($addons as $addon) {
                        $addonsTotal += $addon->price;
                        $deadlineTime += $addon->deadline;

                        $addonsData[$addon->id] = [
                            'addon_price'    => $addon->price,
                            'addon_deadline' => $addon->deadline,
                        ];
                    }
                }

                /* ================= PRICE ================= */

                $discount   = $item['discount'] ?? 0;
                $totalPrice = $servicePrice + $addonsTotal;
                $finalPrice = $totalPrice - ($totalPrice * ($discount / 100));

                /* ================= DOCUMENT CODE ================= */

                $code   = Auth::user()->filial->code;
                $lastId = DocumentsModel::latest('id')->value('id') ?? 0;
                $number = $lastId + 1 + 1000000;

                $documentCode = $code . '-' . $number;

                /* ================= DOCUMENT ================= */

                $document = DocumentsModel::create([
                    'client_id'          => $clientId,
                    'service_id'         => $item['service_id'],
                    'service_price'      => $servicePrice,
                    'addons_total_price' => $addonsTotal,
                    'deadline_time'      => $deadlineTime,
                    'final_price'        => $finalPrice,
                    'paid_amount'        => $item['paid_amount'] ?? 0,
                    'discount'           => $discount,
                    'user_id'            => auth()->id(),
                    'description'        => $item['description'] ?? null,
                    'filial_id'          => auth()->user()->filial_id,
                    'document_code'      => $documentCode,
                    'document_type_id'   => $item['document_type_id'],
                    'direction_type_id'  => $item['direction_type_id'],
                    'consulate_type_id'  => $item['consulate_type_id'],
                ]);

                /* ================= ADDONS ATTACH ================= */

                if (! empty($addonsData)) {
                    $document->addons()->attach($addonsData);
                }

                /* ================= PAYMENT ================= */

                if (! empty($item['paid_amount']) && ! empty($item['payment_type'])) {
                    PaymentsModel::create([
                        'document_id'      => $document->id,
                        'amount'           => $item['paid_amount'],
                        'payment_type'     => $item['payment_type'],
                        'paid_by_admin_id' => auth()->id(),
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'ok',
            'message' => 'Все документы успешно созданы'
        ]);
    }

    public function getAddons($type, $id) {
        $models = [
            'document' => [DocumentTypeAddition::class, 'document_type_id', 'amount'],
            'direction' => [DocumentDirectionAddition::class, 'document_direction_id', 'amount'],
            'consulate' => [ConsulationType::class, 'id', 'price'],
            'service' => [ServiceAddon::class, 'service_id', 'price']
        ];

        if (!isset($models[$type])) {
            return response()->json([]);
        }

        [$model, $foreignKey, $priceField] = $models[$type];

        $addons = $model::where($foreignKey, $id)
            ->get(['id', 'name', $priceField, 'description'])
            ->map(function($addon) use ($priceField) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'amount' => $addon->$priceField, // Универсальное преобразование
                    'description' => $addon->description
                ];
            });

        return response()->json($addons);
    }
}

