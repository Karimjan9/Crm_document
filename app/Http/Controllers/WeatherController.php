<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $city = trim((string) $request->validate([
            'city' => ['required', 'string', 'min:2', 'max:100'],
        ])['city']);
        $apiKey = trim((string) config('services.openweather.key'));

        if ($apiKey === '') {
            return response()->json([
                'message' => 'Ob-havo integratsiyasi sozlanmagan.',
            ], 503);
        }

        $response = Http::acceptJson()
            ->timeout(5)
            ->get(config('services.openweather.url'), [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Ob-havo ma\'lumotini olish imkoni bo\'lmadi.',
            ], 502);
        }

        return response()->json($response->json())
            ->header('Cache-Control', 'private, max-age=300');
    }
}
