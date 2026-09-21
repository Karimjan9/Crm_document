<?php

namespace App\Services;

use App\Models\IntakeOcrDocument;
use App\Models\IntakeSession;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DocumentOcrService
{
    public function __construct(private readonly FileSecurityService $security) {}

    public function upload(IntakeSession $session, UploadedFile $file, ?User $actor = null): IntakeOcrDocument
    {
        $path = $this->security->store($file, 'intake-ocr/'.$session->token);

        try {
            $record = $session->ocrDocuments()->create([
                'uploaded_by_id' => $actor?->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'pending_review',
                'provider' => 'manual',
            ]);

            $config = (array) config('services.ocr', []);
            if (empty($config['endpoint'])) {
                return $record;
            }

            $response = Http::timeout((int) ($config['timeout'] ?? 60))
                ->withToken($config['token'] ?? '')
                ->attach('file', $file->getContent(), $file->getClientOriginalName())
                ->post($config['endpoint']);
            $response->throw();

            $payload = $response->json();
            $data = data_get($payload, 'data', $payload);
            $extracted = is_array($data)
                ? (data_get($data, 'extracted_data') ?: data_get($data, 'fields') ?: $data)
                : ['text' => (string) $data];

            return $record->forceFill([
                'status' => 'extracted',
                'provider' => $config['provider'] ?? 'external',
                'confidence' => is_numeric(data_get($data, 'confidence')) ? data_get($data, 'confidence') : null,
                'extracted_data' => is_array($extracted) ? $extracted : ['value' => $extracted],
            ])->save() ? $record->fresh() : $record;
        } catch (\Throwable $exception) {
            report($exception);

            if (isset($record)) {
                $record->forceFill([
                    'status' => 'failed',
                    // Provider responses can contain implementation detail.
                    'error_message' => 'OCR xizmati faylni qayta ishlay olmadi.',
                ])->save();
            } else {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }
    }

    public function approve(IntakeOcrDocument $record, User $actor, ?array $overrides = null, ?string $note = null): IntakeOcrDocument
    {
        if (! in_array($record->status, ['pending_review', 'extracted'], true)) {
            throw new \InvalidArgumentException('OCR yozuvi tasdiqlash uchun tayyor emas.');
        }

        $extracted = $record->extracted_data ?: [];
        if ($overrides !== null) {
            $extracted = array_merge($extracted, $overrides);
        }

        return $record->forceFill([
            'status' => 'approved',
            'extracted_data' => $extracted,
            'approved_by_id' => $actor->id,
            'approved_at' => now(),
            'approval_note' => $note,
            'error_message' => null,
        ])->save() ? $record->fresh(['approvedBy']) : $record;
    }
}
