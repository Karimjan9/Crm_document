<?php

namespace App\Http\Middleware;

use App\Models\PartnerApiKey;
use Closure;
use Illuminate\Http\Request;

class PartnerApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) $request->bearerToken();
        if ($token === '' || ! str_starts_with($token, 'b2b_')) {
            return response()->json(['message' => 'Partner API token talab qilinadi.'], 401);
        }

        $key = PartnerApiKey::query()
            ->active()
            ->with('partner')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $key || ! $key->partner || $key->partner->status !== 'active') {
            return response()->json(['message' => 'Partner API token yaroqsiz yoki bekor qilingan.'], 401);
        }

        $key->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('partner', $key->partner);
        $request->attributes->set('partnerApiKey', $key);

        return $next($request);
    }
}
