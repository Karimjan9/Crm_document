<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentTypeAdditionModel as DocumentTypeAddition;
use App\Models\DocumentDirectionAdditionModel as DocumentDirectionAddition;
use App\Models\ServiceAddonModel as ServiceAddon;
use App\Models\ConsulationTypeModel as ConsulationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ServicesModel;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentsModel;
use App\Models\DocumentFileModel as DocumentFile;

class DocumentController extends Controller
{

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            // Основные поля
            'client_id' => 'required|integer|exists:clients,id',
            'document_type' => 'required|string|max:255',
            'direction_type' => 'required|boolean',

            // Консульство
            'consulate_enabled' => 'required|boolean',
            'consulate_price' => 'nullable|numeric|min:0',

            // Легализация
            'legalization_id' => 'required|integer|exists:legalizations,id',
            'legalization_price' => 'required|numeric|min:0',

            // Сервис и оплата
            'service' => 'required|string|max:255',
            'discount' => 'nullable|numeric',
            'payment_amount' => 'required|numeric|min:0',
            'payment_type' => ['required', 'string', Rule::in(['cash', 'card', 'transfer', 'online'])],

            // Описание
            'description' => 'nullable|string|max:5000',

            // Аддоны (JSON строка)
            'selected_addons' => 'nullable|json',

            // Totals (JSON строка)
            'totals' => 'required|json',

            // Файлы
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // максимум 10MB
        ], [
            // Кастомные сообщения об ошибках
            'client_id.required' => 'Не указан ID клиента',
            'client_id.exists' => 'Клиент не найден',
            'document_type.required' => 'Не указан тип документа',
            'legalization_id.exists' => 'Указанная легализация не существует',
            'payment_type.in' => 'Недопустимый тип оплаты',
            'files.*.mimes' => 'Допустимые форматы файлов: PDF, DOC, DOCX, JPG, JPEG, PNG',
            'files.*.max' => 'Размер файла не должен превышать 10MB',
        ]);


        $service      = ServicesModel::findOrFail($request->service);
        $servicePrice = $service->price;
        $deadlineTime = $service->deadline;

        if ($request->selected_addons) {
            $selectedAddons = json_decode($request->selected_addons, true);

            if (is_array($selectedAddons) && count($selectedAddons) > 0) {
                // Конфигурация таблиц для каждого типа
                $tableConfig = [
                    'document' => 'document_type_addition',
                    'direction' => 'document_direction_addition',
                    'service' => 'service_addons'
                ];

                // Группируем по типу
                $addonsByType = [];
                foreach ($selectedAddons as $addon) {
                    $sourceType = $addon['sourceType'] ?? null;
                    if ($sourceType && isset($tableConfig[$sourceType])) {
                        $addonsByType[$sourceType][] = $addon['id'];
                    }
                }

                $addons_total = 0;
                $addonsData = [];

                // Обрабатываем каждый тип
                foreach ($addonsByType as $type => $ids) {
                    if (empty($ids)) continue;

                    $tableName = $tableConfig[$type];
                    $addons = DB::table($tableName)->whereIn('id', $ids)->get();

                    foreach ($addons as $addon) {
                        $price = $addon->amount ?? $addon->price ?? 0;
                        $deadline = $addon->deadline ?? 0;

                        $addons_total += $price;
                        $deadlineTime += $deadline;

                        $addonsData[] = [
                            'addon_id' => $addon->id,
                            'addon_type' => $type,
                            'addon_price' => $price,
                            'addon_deadline' => $deadline,
                            'addon_name' => $addon->name ?? '',
                        ];
                    }
                }

            }
        }

        // dd($addonsData);

        $discount   = intval($request->discount);
        $totalPrice = $servicePrice + $addons_total;
        $finalPrice = $totalPrice - $discount;

        $code = Auth::user()->filial->code;

        // Используем автоинкремент ID с добавлением базового числа
        $baseNumber = 1000000;
        $nextId = DocumentsModel::max('id') + 1;
        $number = $baseNumber + $nextId;

        $documentCode = $code . '-' . $number;

        // Проверка уникальности
        while (DocumentsModel::where('document_code', $documentCode)->exists()) {
            $number++;
            $documentCode = $code . '-' . $number;
        }

        $document = DocumentsModel::create([
            'client_id'          => $request->client_id,
            'service_id'         => $request->service,
            'service_price'      => $servicePrice,
            'addons_total_price' => $addons_total,
            'deadline_time'      => $deadlineTime,
            'final_price'        => $finalPrice,
            'paid_amount'        => $request->payment_amount ?? 0,
            'discount'           => $discount,
            'user_id'            => auth()->id(),
            'description'        => $request->description,
            'filial_id'          => auth()->user()->filial_id,
            'document_code'      => $documentCode,
            'document_type_id'   => $request->document_type,
            'direction_type_id'  => $request->direction_type,
            'consulate_type_id'  => $request->legalization_id,
        ]);

        if (!empty($addonsData)) {
            foreach ($addonsData as $addon) {
                switch ($addon['addon_type']) {
                    case 'document':
                        $document->document_type_addons()->attach($addon['addon_id'], [
                            'addon_price' => $addon['addon_price'],
                        ]);
                        break;

                    case 'direction':
                        $document->document_direction_addons()->attach($addon['addon_id'], [
                            'addon_price' => $addon['addon_price'],
                        ]);
                        break;

                    case 'consulate':
                        $document->consulate_addons()->attach($addon['addon_id'], [
                            'addon_price' => $addon['addon_price'],
                        ]);
                        break;

                    case 'service':
                        $document->addons()->attach($addon['addon_id'], [
                            'addon_price' => $addon['addon_price'],
                        ]);
                        break;
                }
            }
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('documents/' . $document->document_code, 'public');

                DocumentFile::create([
                    'document_id' => $document->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

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
