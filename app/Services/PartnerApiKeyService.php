<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerApiKey;
use Illuminate\Support\Str;

class PartnerApiKeyService
{
    /**
     * The raw token is returned only once. The database stores a one-way hash.
     *
     * @return array{model: PartnerApiKey, token: string}
     */
    public function create(Partner $partner, string $name, ?int $expiresInDays = null): array
    {
        $token = 'b2b_' . Str::random(56);

        $model = $partner->apiKeys()->create([
            'name' => $name,
            'token_prefix' => substr($token, 0, 16),
            'token_hash' => hash('sha256', $token),
            'abilities' => ['orders:read', 'orders:write', 'invoices:read', 'stats:read'],
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);

        return ['model' => $model, 'token' => $token];
    }

    public function revoke(PartnerApiKey $key): void
    {
        $key->forceFill(['revoked_at' => now()])->save();
    }
}
