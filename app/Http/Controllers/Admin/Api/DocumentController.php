<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsulationTypeModel as ConsulationType;
use App\Models\DocumentDirectionAdditionModel as DocumentDirectionAddition;
use App\Models\DocumentTypeAdditionModel as DocumentTypeAddition;
use App\Models\ServiceAddonModel as ServiceAddon;
use App\Support\StoresDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use StoresDocuments;

    public function store(Request $request)
    {
        $payload = $this->normalizeDocumentPayload($request->all());
        $validator = $this->makeDocumentValidator(
            $payload + ['files' => $request->file('files', [])],
            includeFiles: true
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Hujjatni tekshirishda xatolar bor.",
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->merge($payload);
        $document = $this->storeDocumentFromRequest($request);

        return response()->json([
            'success' => true,
            'message' => 'Hujjat muvaffaqiyatli saqlandi.',
            'data' => [
                'document' => [
                    'id' => $document->id,
                    'document_code' => $document->document_code,
                    'final_price' => (float) $document->final_price,
                ],
            ],
        ], 200);
    }

    public function storeAll(Request $request)
    {
        $clientId = $this->normalizeNullable($request->input('client_id'));
        $items = $this->decodeItemsPayload($request->input('items_payload', $request->input('items', [])));

        $topLevelValidator = Validator::make([
            'client_id' => $clientId,
            'items' => $items,
            'files' => $request->file('files', []),
        ], [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'items' => ['required', 'array', 'min:1'],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', 'array'],
            'files.*.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($topLevelValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Saqlash uchun umumiy ma'lumotlarda xato bor.",
                'errors' => $topLevelValidator->errors(),
            ], 422);
        }

        $validatedItems = [];
        $errors = [];

        foreach (array_values($items) as $index => $item) {
            $payload = $this->normalizeDocumentPayload(is_array($item) ? $item : []);

            if (empty($payload['client_id'])) {
                $payload['client_id'] = $clientId;
            }

            $itemValidator = $this->makeDocumentValidator($payload);

            if ($itemValidator->fails()) {
                foreach ($itemValidator->errors()->getMessages() as $field => $messages) {
                    $errors["items.{$index}.{$field}"] = $messages;
                }

                continue;
            }

            $validatedItems[$index] = $payload;
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => "Ba'zi hujjatlarda xatolar bor. Hech biri saqlanmadi.",
                'errors' => $errors,
            ], 422);
        }

        $documents = DB::transaction(function () use ($validatedItems, $request) {
            $created = [];

            foreach (array_values($validatedItems) as $index => $payload) {
                $files = $request->file("files.{$index}", []);
                $files = is_array($files) ? $files : array_filter([$files]);

                $document = $this->storeDocumentFromPayload($payload, $files);

                $created[] = [
                    'id' => $document->id,
                    'document_code' => $document->document_code,
                    'final_price' => (float) $document->final_price,
                ];
            }

            return $created;
        });

        return response()->json([
            'success' => true,
            'message' => count($documents) . " ta hujjat muvaffaqiyatli saqlandi.",
            'data' => [
                'documents' => $documents,
                'count' => count($documents),
            ],
        ], 200);
    }

    public function getAddons($type, $id)
    {
        $models = [
            'document' => [DocumentTypeAddition::class, 'document_type_id', 'amount'],
            'direction' => [DocumentDirectionAddition::class, 'document_direction_id', 'amount'],
            'consulate' => [ConsulationType::class, 'id', 'price'],
            'service' => [ServiceAddon::class, 'service_id', 'price'],
        ];

        if (!isset($models[$type])) {
            return response()->json([]);
        }

        [$model, $foreignKey, $priceField] = $models[$type];

        $addons = $model::where($foreignKey, $id)
            ->get(['id', 'name', $priceField, 'description'])
            ->map(function ($addon) use ($priceField) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'amount' => $addon->{$priceField},
                    'description' => $addon->description,
                ];
            });

        return response()->json($addons);
    }

    protected function makeDocumentValidator(array $payload, bool $includeFiles = false)
    {
        $rules = [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'document_type_id' => ['required', 'integer', 'exists:document_type,id'],
            'package_template_id' => ['nullable', 'integer', 'exists:package_templates,id'],
            'process_mode' => ['required', 'string', Rule::in(['apostil', 'consul', 'service'])],
            'selection_mode' => [
                'nullable',
                'string',
                Rule::in(['consul', 'legalization', 'mixed']),
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'consul'),
            ],
            'direction_type_id' => [
                'nullable',
                'integer',
                'exists:direction_type,id',
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'apostil'),
            ],
            'apostil_group1_id' => [
                'nullable',
                'integer',
                'exists:apostil_static,id',
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'apostil'),
            ],
            'apostil_group2_id' => [
                'nullable',
                'integer',
                'exists:apostil_static,id',
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'apostil'),
            ],
            'consul_id' => [
                'nullable',
                'integer',
                'exists:consul,id',
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'consul'
                    && in_array($payload['selection_mode'] ?? null, ['consul', 'mixed'], true)),
            ],
            'consulate_type_id' => [
                'nullable',
                'integer',
                'exists:consulates_type,id',
                Rule::requiredIf(fn () => ($payload['process_mode'] ?? null) === 'consul'
                    && in_array($payload['selection_mode'] ?? null, ['legalization', 'mixed'], true)),
            ],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*.id' => ['required_with:selected_addons', 'integer'],
            'selected_addons.*.sourceType' => ['required_with:selected_addons', 'string', Rule::in(['document', 'direction', 'service'])],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_type' => [
                'nullable',
                'string',
                Rule::in(['cash', 'card', 'transfer', 'online', 'admin_entry']),
                Rule::requiredIf(fn () => (float) ($payload['paid_amount'] ?? 0) > 0),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ];

        if ($includeFiles) {
            $rules['files'] = ['nullable', 'array'];
            $rules['files.*'] = ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'];
        }

        return Validator::make($payload, $rules, $this->documentValidationMessages());
    }

    protected function normalizeDocumentPayload(array $data): array
    {
        $selectedAddons = $data['selected_addons'] ?? $data['addons'] ?? [];

        if (is_string($selectedAddons)) {
            $decoded = json_decode($selectedAddons, true);
            $selectedAddons = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($selectedAddons)) {
            $selectedAddons = [];
        }

        if (!empty($data['addons']) && is_array($data['addons']) && empty($selectedAddons)) {
            $selectedAddons = array_map(fn ($id) => [
                'id' => (int) $id,
                'sourceType' => 'service',
            ], $data['addons']);
        }

        $selectedAddons = collect($selectedAddons)
            ->map(function ($addon) {
                if (!is_array($addon)) {
                    return null;
                }

                $id = (int) ($addon['id'] ?? 0);
                $sourceType = $addon['sourceType'] ?? $addon['type'] ?? null;

                if (!$id || !$sourceType) {
                    return null;
                }

                return [
                    'id' => $id,
                    'sourceType' => (string) $sourceType,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $payload = [
            'client_id' => $this->normalizeNullable($data['client_id'] ?? null),
            'service_id' => $this->normalizeNullable($data['service_id'] ?? ($data['service'] ?? null)),
            'document_type_id' => $this->normalizeNullable($data['document_type_id'] ?? ($data['document_type'] ?? null)),
            'direction_type_id' => $this->normalizeNullable($data['direction_type_id'] ?? ($data['direction_type'] ?? null)),
            'consulate_type_id' => $this->normalizeNullable($data['consulate_type_id'] ?? ($data['legalization_id'] ?? null)),
            'package_template_id' => $this->normalizeNullable($data['package_template_id'] ?? null),
            'process_mode' => $this->normalizeProcessMode($data['process_mode'] ?? null),
            'selection_mode' => $this->normalizeSelectionMode($data['selection_mode'] ?? ($data['process_selection_mode'] ?? null)),
            'apostil_group1_id' => $this->normalizeNullable($data['apostil_group1_id'] ?? null),
            'apostil_group2_id' => $this->normalizeNullable($data['apostil_group2_id'] ?? null),
            'consul_id' => $this->normalizeNullable($data['consul_id'] ?? null),
            'discount' => $this->normalizeNullable($data['discount'] ?? null),
            'paid_amount' => $this->normalizeNullable($data['paid_amount'] ?? ($data['payment_amount'] ?? null)),
            'payment_type' => $this->normalizeNullable($data['payment_type'] ?? null),
            'description' => $this->normalizeNullable($data['description'] ?? null),
            'selected_addons' => $selectedAddons,
        ];

        if ($payload['process_mode'] !== 'apostil') {
            $payload['direction_type_id'] = null;
            $payload['apostil_group1_id'] = null;
            $payload['apostil_group2_id'] = null;
        }

        if ($payload['process_mode'] !== 'consul') {
            $payload['selection_mode'] = null;
            $payload['consul_id'] = null;
            $payload['consulate_type_id'] = null;
        }

        if ($payload['process_mode'] === 'consul' && !in_array($payload['selection_mode'], ['consul', 'mixed'], true)) {
            $payload['consul_id'] = null;
        }

        if ($payload['process_mode'] === 'consul' && !in_array($payload['selection_mode'], ['legalization', 'mixed'], true)) {
            $payload['consulate_type_id'] = null;
        }

        return $payload;
    }

    protected function decodeItemsPayload($payload): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($payload) ? $payload : [];
    }

    protected function normalizeNullable($value)
    {
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
    }

    protected function normalizeProcessMode($value): string
    {
        $value = strtolower((string) $this->normalizeNullable($value));

        return in_array($value, ['apostil', 'consul', 'service'], true) ? $value : 'service';
    }

    protected function normalizeSelectionMode($value): ?string
    {
        $value = strtolower((string) $this->normalizeNullable($value));

        return in_array($value, ['consul', 'legalization', 'mixed'], true) ? $value : null;
    }

    protected function documentValidationMessages(): array
    {
        return [
            'client_id.required' => 'Mijoz tanlanishi shart.',
            'client_id.exists' => 'Tanlangan mijoz topilmadi.',
            'service_id.required' => 'Xizmat tanlanishi shart.',
            'service_id.exists' => 'Tanlangan xizmat mavjud emas.',
            'document_type_id.required' => 'Hujjat turi tanlanishi shart.',
            'document_type_id.exists' => 'Tanlangan hujjat turi topilmadi.',
            'direction_type_id.required' => "Apostil uchun yo'nalish tanlanishi shart.",
            'consulate_type_id.required' => 'Legalizatsiya turi tanlanishi shart.',
            'consul_id.required' => 'Konsullik tanlanishi shart.',
            'selection_mode.required' => 'Legalizatsiya tanlov turi tanlanishi shart.',
            'payment_type.required' => "To'lov summasi kiritilgan bo'lsa, to'lov turi ham tanlanishi shart.",
        ];
    }
}
