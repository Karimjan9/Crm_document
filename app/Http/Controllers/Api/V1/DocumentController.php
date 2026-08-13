<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Admin\Api\DocumentController as LegacyDocumentController;
use App\Models\DocumentsModel;
use Illuminate\Http\Request;

class DocumentController extends LegacyDocumentController
{
    public function show(DocumentsModel $document)
    {
        $this->authorize('view', $document);

        $document->load([
            'client',
            'service',
            'filial',
            'documentType',
            'directionType',
            'consulateType',
            'files',
            'payments',
        ]);

        $documentData = $document->toArray();
        $documentData['files'] = $document->files->map(fn ($file) => [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'file_type' => $file->file_type,
            'file_size' => $file->file_size,
            'download_url' => route('api.v1.document-files.show', $file),
        ])->values()->all();

        return response()->json([
            'data' => $documentData,
        ]);
    }

    public function update(Request $request, DocumentsModel $document)
    {
        $this->authorize('update', $document);

        $raw = $request->all();
        $defaults = [
            'client_id' => $document->client_id,
            'filial_id' => $document->filial_id,
            'service_id' => $document->service_id,
            'document_type_id' => $document->document_type_id,
            'direction_type_id' => $document->direction_type_id,
            'consulate_type_id' => $document->consulate_type_id,
            'process_mode' => $document->process_mode ?: 'service',
            'selection_mode' => $document->selection_mode,
            'apostil_group1_id' => $document->apostil_group1_id,
            'apostil_group2_id' => $document->apostil_group2_id,
            'consul_id' => $document->consul_id,
            'discount' => $document->discount,
            'description' => $document->description,
        ];

        $input = $defaults;

        foreach ($raw as $field => $value) {
            $input[$field] = $value;
        }

        $payload = $this->normalizeDocumentPayload($input);

        if (!array_key_exists('selected_addons', $raw) && !array_key_exists('addons', $raw)) {
            $document->loadMissing([
                'document_type_addons',
                'document_direction_addons',
                'addons',
            ]);

            $payload['selected_addons'] = array_merge(
                $document->document_type_addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'document'])
                    ->all(),
                $document->document_direction_addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'direction'])
                    ->all(),
                $document->addons
                    ->map(fn ($addon) => ['id' => $addon->id, 'sourceType' => 'service'])
                    ->all(),
            );
        }

        $validator = $this->makeDocumentValidator($payload);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Hujjatni tekshirishda xatolar bor.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $request->merge($payload);
        $document = $this->updateDocumentFromRequest($document, $request);

        return response()->json([
            'success' => true,
            'message' => 'Hujjat muvaffaqiyatli yangilandi.',
            'data' => [
                'document' => [
                    'id' => $document->id,
                    'document_code' => $document->document_code,
                    'final_price' => (float) $document->final_price,
                    'paid_amount' => (float) $document->paid_amount,
                ],
            ],
        ]);
    }
}
