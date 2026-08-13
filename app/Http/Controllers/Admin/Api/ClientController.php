<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientsModel as Client;
use App\Services\ClientScopeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct(private readonly ClientScopeService $clients)
    {
    }

    /**
     * GET /api/clients
     */
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        return response()->json(
            $this->clients->query(auth()->user())
                ->orderByDesc('id')
                ->get()
        );
    }

    /**
     * POST /api/clients
     */
    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $request->merge([
            'phone_number' => $this->normalizePhone((string) $request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:clients,phone_number',
            'email' => ['nullable', 'email', 'max:180'],
            'telegram_chat_id' => ['nullable', 'string', 'max:80'],
            'whatsapp_phone' => ['nullable', 'string', 'max:40'],
            'description' => 'nullable|string|max:5000',
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);

        $user = $request->user();
        $validated['phone_number'] = $this->normalizePhone($validated['phone_number']);

        if (! $user->hasAnyRole(['super_admin', 'admin_manager'])) {
            $validated['filial_id'] = $user->filial_id;
        }

        if (! $validated['filial_id']) {
            return response()->json([
                'message' => 'Filial tanlanishi shart.',
                'errors' => ['filial_id' => ['Filial tanlanishi shart.']],
            ], 422);
        }

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    /**
     * GET /api/clients/{client}
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);
        $this->clients->assertVisible($client, auth()->user());

        return response()->json($client);
    }

    /**
     * PUT /api/clients/{client}
     */
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->merge([
            'phone_number' => $this->normalizePhone((string) $request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique('clients', 'phone_number')->ignore($client->id),
            ],
            'email' => ['nullable', 'email', 'max:180'],
            'telegram_chat_id' => ['nullable', 'string', 'max:80'],
            'whatsapp_phone' => ['nullable', 'string', 'max:40'],
            'description' => 'nullable|string|max:5000',
            'filial_id' => ['nullable', 'integer', 'exists:filial,id'],
        ]);

        $validated['phone_number'] = $this->normalizePhone($validated['phone_number']);

        if (! $request->user()->hasAnyRole(['super_admin', 'admin_manager'])) {
            unset($validated['filial_id']);
        }

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * DELETE /api/clients/{client}
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        try {
            $client->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'Client has related documents and cannot be deleted.'
            ], 409);
        }

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * GET /api/clients/search?q=?
     */

    public function search(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $q = trim((string) ($validated['q'] ?? ''));

        if ($q === '') {
            return response()->json([]);
        }

        $term = $this->escapeLikeTerm($q);

        $clients = Client::query()
            ->visibleTo($request->user())
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('phone_number', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone_number']);

        return response()->json($clients);
    }

    protected function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', $phone) ?: '';

        if (str_starts_with($normalized, '998') && strlen($normalized) === 12) {
            return substr($normalized, 3);
        }

        return $normalized;
    }

    protected function escapeLikeTerm(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
