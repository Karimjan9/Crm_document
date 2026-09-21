<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LoginRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TokenController extends Controller
{
    public const ABILITIES = [
        'profile:read',
        'token:revoke',
        'clients:read',
        'clients:write',
        'documents:read',
        'documents:write',
        'files:read',
    ];

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        app(LoginRateLimiter::class)->ensureNotLocked($request);

        $user = User::query()
            ->where('login', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], (string) $user->password)) {
            app(LoginRateLimiter::class)->recordFailure($request);
            throw ValidationException::withMessages([
                'login' => 'Login yoki parol noto‘g‘ri.',
            ]);
        }

        app(LoginRateLimiter::class)->clearAccount($request);

        $tokenName = trim((string) ($credentials['device_name'] ?? 'api')) ?: 'api';
        $expirationMinutes = max(5, (int) config('sanctum.expiration', 10080));
        $expiresAt = now()->addMinutes($expirationMinutes);

        return response()->json([
            'token' => $user->createToken($tokenName, self::ABILITIES, $expiresAt)->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'abilities' => self::ABILITIES,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function destroy(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token bekor qilindi.',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'login' => $user->login,
            'phone' => $user->phone,
            'filial_id' => $user->filial_id,
            'roles' => $user->getRoleNames()->values()->all(),
        ];
    }
}
