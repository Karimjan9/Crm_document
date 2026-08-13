<?php

namespace App\Http\Controllers\Admin;

use App\Models\ClientsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminClientDocumentController extends Controller
{
    public function search(Request $request)
    {
        $this->authorize('viewAny', ClientsModel::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));

        if ($term === '') {
            return response()->json([]);
        }

        $term = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
        $clients = ClientsModel::query()
            ->visibleTo($request->user())
            ->where('phone_number', 'like', "%{$term}%")
            ->limit(10)
            ->get(['id', 'name', 'phone_number']);

        return response()->json($clients);
    }
    public function mapData()
    {
        $this->authorize('viewAny', ClientsModel::class);

        // Only expose client locations belonging to the current tenant.
        $clients = ClientsModel::query()
            ->visibleTo(request()->user())
            ->get(['id', 'name', 'phone_number']);

    // Mapga yuboriladigan markers array
    $markers = [];

        foreach ($clients as $client) {
            if (! is_null($client->lat) && ! is_null($client->lng)) {
                $markers[] = [
                    'name' => $client->name,
                    'latLng' => [(float) $client->lat, (float) $client->lng],
                ];
            }
        }

        return response()->json($markers);
    }
}
