<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            DB::select('select 1');

            $storageHealthy = is_dir(storage_path())
                && is_writable(storage_path())
                && is_dir(storage_path('app'))
                && is_writable(storage_path('app'))
                && is_dir(base_path('bootstrap/cache'))
                && is_writable(base_path('bootstrap/cache'));

            if (!$storageHealthy) {
                return response()->json([
                    'status' => 'error',
                    'checks' => [
                        'database' => 'ok',
                        'storage' => 'failed',
                    ],
                ], 503)->header('Cache-Control', 'no-store');
            }

            return response()->json([
                'status' => 'ok',
                'checks' => [
                    'database' => 'ok',
                    'storage' => 'ok',
                ],
                'timestamp' => now()->toIso8601String(),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'checks' => [
                    'database' => 'failed',
                ],
            ], 503)->header('Cache-Control', 'no-store');
        }
    }
}
