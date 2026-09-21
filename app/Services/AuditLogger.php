<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    private const SENSITIVE_FIELDS = [
        'password', 'password_confirmation', 'remember_token', 'token', 'token_hash',
        'secret', 'api_key', 'access_token', 'refresh_token', 'webhook_secret',
    ];

    public function model(string $event, Model $model, array $old = [], array $new = []): void
    {
        $this->write($event, $model, $old, $new);
    }

    public function event(string $event, array $context = [], ?Model $subject = null): void
    {
        $this->write($event, $subject, [], [], $context);
    }

    private function write(string $event, ?Model $subject, array $old, array $new, array $context = []): void
    {
        if (! config('security.audit.enabled', true) || ! Schema::hasTable('audit_logs')) {
            return;
        }

        $request = app()->runningInConsole() ? null : request();
        AuditLog::query()->create([
            'event' => $event,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'user_id' => $request?->user()?->id ?? auth()->id(),
            'ip_address' => $request?->ip(),
            'method' => $request?->method(),
            'url' => $request?->fullUrl(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 1000) : null,
            'old_values' => $this->sanitize($old),
            'new_values' => $this->sanitize($new),
            'context' => $this->sanitize($context),
            'created_at' => now(),
        ]);
    }

    private function sanitize(array $values): array
    {
        $values = Arr::except($values, self::SENSITIVE_FIELDS);

        array_walk_recursive($values, function (&$value, $key): void {
            if (in_array((string) $key, self::SENSITIVE_FIELDS, true)) {
                $value = '[REDACTED]';

                return;
            }

            if (is_string($value)) {
                $value = mb_substr($value, 0, 1000);
            }
        });

        return $values;
    }
}
