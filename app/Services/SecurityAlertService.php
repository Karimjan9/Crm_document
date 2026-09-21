<?php

namespace App\Services;

use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SecurityAlertService
{
    public function raise(string $type, string $severity, array $context = []): void
    {
        if (! Schema::hasTable('security_alerts')) {
            return;
        }

        $request = app()->runningInConsole() ? null : request();
        $ip = $context['ip_address'] ?? $request?->ip();
        $userId = $context['user_id'] ?? $request?->user()?->id;
        $fingerprint = hash('sha256', $type.'|'.($ip ?: 'system').'|'.now()->format('Y-m-d-H'));

        $alert = SecurityAlert::query()->firstOrNew(['fingerprint' => $fingerprint]);
        $isNew = ! $alert->exists;
        $alert->fill([
            'type' => $type,
            'severity' => $severity,
            'user_id' => $userId,
            'ip_address' => $ip,
            'occurrences' => $alert->exists ? $alert->occurrences + 1 : 1,
            'context' => $context,
            'first_seen_at' => $alert->first_seen_at ?: now(),
            'last_seen_at' => now(),
        ]);
        $alert->save();

        Log::channel(config('security.alerts.log_channel', 'stack'))->warning('security_alert', [
            'type' => $type,
            'severity' => $severity,
            'ip_address' => $ip,
            'user_id' => $userId,
            'context' => $context,
        ]);
        app(AuditLogger::class)->event('security_alert.'.$type, $context);

        if ($isNew && $this->sendTelegram($type, $severity, $context)) {
            $alert->forceFill(['notified_at' => now()])->save();
        }
    }

    private function sendTelegram(string $type, string $severity, array $context): bool
    {
        $telegram = (array) config('security.alerts.telegram', []);
        if (empty($telegram['chat_id']) || empty($telegram['bot_token'])) {
            return false;
        }

        try {
            Http::timeout((int) ($telegram['timeout'] ?? 10))
                ->post(rtrim((string) ($telegram['endpoint'] ?? 'https://api.telegram.org'), '/').'/bot'.$telegram['bot_token'].'/sendMessage', [
                    'chat_id' => $telegram['chat_id'],
                    'text' => "CRM SECURITY [{$severity}] {$type}\n".json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ])->throw();

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
