<?php

namespace App\Services;

use App\Models\DocumentCustodyEvent;
use App\Models\DocumentsModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentCustodyService
{
    public const EVENTS = ['received', 'handoff', 'qa', 'courier', 'client', 'returned'];

    public function record(
        DocumentsModel $document,
        string $eventType,
        ?int $fromUserId,
        ?int $toUserId,
        ?string $notes = null,
        ?UploadedFile $photo = null,
        ?User $actor = null
    ): DocumentCustodyEvent {
        if (! in_array($eventType, self::EVENTS, true)) {
            throw new \InvalidArgumentException('Chain-of-custody eventi noto‘g‘ri.');
        }

        $path = $photo ? app(FileSecurityService::class)->store($photo, 'custody/'.$document->id) : null;

        try {
            return DB::transaction(function () use ($document, $eventType, $fromUserId, $toUserId, $notes, $path, $actor): DocumentCustodyEvent {
                $lockedDocument = DocumentsModel::query()->lockForUpdate()->findOrFail($document->id);
                $previousEvent = $lockedDocument->custodyEvents()->latest('event_at')->first();
                if ($previousEvent && $previousEvent->to_user_id && $fromUserId && (int) $previousEvent->to_user_id !== (int) $fromUserId) {
                    throw new \InvalidArgumentException('Chain-of-custody ketma-ketligi buzilgan: from_user oldingi to_user bilan mos emas.');
                }
                $previousSignature = $previousEvent?->signature;
                $eventAt = now();
                $event = $lockedDocument->custodyEvents()->create([
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $toUserId,
                    'signed_by_id' => $actor?->id,
                    'event_type' => $eventType,
                    'event_at' => $eventAt,
                    'signature' => hash('sha256', implode('|', [
                        $lockedDocument->id,
                        $eventType,
                        $fromUserId ?: 0,
                        $toUserId ?: 0,
                        $eventAt->format('c'),
                        $previousSignature ?: 'GENESIS',
                        Str::random(16),
                    ])),
                    'previous_signature' => $previousSignature,
                    'photo_path' => $path,
                    'notes' => $notes,
                    'metadata' => ['recorded_by_id' => $actor?->id],
                ]);

                return $event->fresh(['fromUser', 'toUser', 'signedBy']);
            });
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }

            throw $exception;
        }
    }

    public function photo(DocumentCustodyEvent $event)
    {
        abort_unless($event->photo_path, 404);
        $disk = Storage::disk('private');
        abort_unless($disk->exists($event->photo_path), 404);

        return $disk->download($event->photo_path, basename($event->photo_path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function verifyChain(DocumentsModel $document): bool
    {
        $previous = null;
        foreach ($document->custodyEvents()->oldest('event_at')->get() as $event) {
            if ($event->previous_signature !== $previous) {
                return false;
            }
            $previous = $event->signature;
        }

        return true;
    }
}
