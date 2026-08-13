<?php

namespace App\Http\Controllers;

use App\Models\DocumentCustodyEvent;
use App\Models\DocumentsModel;
use App\Models\User;
use App\Services\DocumentCustodyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentCustodyController extends Controller
{
    public function __construct(private readonly DocumentCustodyService $custody)
    {
    }

    public function index(DocumentsModel $document)
    {
        $this->authorize('view', $document);
        $document->load(['custodyEvents.fromUser', 'custodyEvents.toUser', 'custodyEvents.signedBy']);

        return response()->json([
            'data' => $document->custodyEvents->map(fn (DocumentCustodyEvent $event) => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'event_at' => $event->event_at?->toIso8601String(),
                'from' => $event->fromUser?->name,
                'to' => $event->toUser?->name,
                'signed_by' => $event->signedBy?->name,
                'signature' => $event->signature,
                'previous_signature' => $event->previous_signature,
                'notes' => $event->notes,
                'photo_url' => $event->photo_url,
            ]),
            'chain_valid' => $this->custody->verifyChain($document),
        ]);
    }

    public function store(Request $request, DocumentsModel $document)
    {
        $this->authorize('update', $document);
        $data = $request->validate([
            'event_type' => ['required', Rule::in(DocumentCustodyService::EVENTS)],
            'from_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $event = $this->custody->record(
            $document,
            $data['event_type'],
            $data['from_user_id'] ?? null,
            $data['to_user_id'] ?? null,
            $data['notes'] ?? null,
            $request->file('photo'),
            $request->user(),
        );

        return response()->json([
            'data' => $event,
            'chain_valid' => $this->custody->verifyChain($document->fresh()),
        ], 201);
    }

    public function photo(DocumentCustodyEvent $event)
    {
        $event->loadMissing('document');
        $this->authorize('view', $event->document);

        return $this->custody->photo($event);
    }
}
