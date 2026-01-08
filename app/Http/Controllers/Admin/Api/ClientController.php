<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientsModel as Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * GET /api/clients
     */
    public function index()
    {
        return response()->json(
            Client::orderBy('id', 'desc')->get()
        );
    }

    /**
     * POST /api/clients
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:clients,phone_number',
            'description' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    /**
     * GET /api/clients/{client}
     */
    public function show(Client $client)
    {
        return response()->json($client);
    }

    /**
     * PUT /api/clients/{client}
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique('clients', 'phone_number')->ignore($client->id),
            ],
            'description' => 'nullable|string',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * DELETE /api/clients/{client}
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * GET /api/clients/search?q=?
     */

    public function search(Request $request)
    {
        $q = $request->get('q');

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('phone_number', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'name', 'phone_number']);

        return response()->json($clients);
    }
}
