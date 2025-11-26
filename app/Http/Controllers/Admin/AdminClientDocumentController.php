<?php

namespace App\Http\Controllers\Admin;

use App\Models\ClientsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminClientDocumentController extends Controller
{
   public function search(Request $request)
{
    $term = $request->get('q', '');
    $clients = ClientsModel::where('phone_number', 'like', "%$term%")
        ->limit(10)
        ->get(['id','name','phone_number']);

    return response()->json($clients);
}
    public function mapData()
{
    // Clients jadvalidan xaritada ko'rsatish uchun data
    $clients = ClientsModel::all(['id', 'name', 'phone_number']);

    // Mapga yuboriladigan markers array
    $markers = [];

    foreach($clients as $client){
        if(!is_null($client->lat) && !is_null($client->lng)){
            $markers[] = [
                'name' => $client->name,
                'latLng' => [(float)$client->lat, (float)$client->lng]
            ];
        }
    }

    return response()->json($markers); // bo'sh bo'lsa [] yuboradi
}
}
