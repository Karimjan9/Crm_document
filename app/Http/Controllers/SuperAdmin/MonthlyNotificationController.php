<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDatabaseBackup;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MonthlyNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'message', 'type', 'notify_date', 'created_at'])
            ->map(function (Notification $notification): array {
                $payload = $notification->toArray();

                if ($notification->type === 'sql_backup') {
                    $payload['action_label'] = 'SQL nusxa olish';
                    $payload['action_type'] = 'sql_backup';
                }

                return $payload;
            });

        return response()->json($notifications);
    }

    public function queueSqlBackup(): JsonResponse
    {
        abort_unless(
            config('database.connections.'.config('database.default').'.driver') === 'mysql',
            422,
            'SQL backup faqat MySQL bazasi uchun ishlaydi.'
        );

        $token = Str::random(64);
        Cache::put('queued-database-backup:'.$token, [
            'status' => 'queued',
            'user_id' => (int) auth()->id(),
        ], now()->addHours(2));
        GenerateDatabaseBackup::dispatch($token, (int) auth()->id());

        return response()->json([
            'status' => 'queued',
            'token' => $token,
            'status_url' => route('superadmin.monthly_notifications.sql_backup.status', ['token' => $token]),
        ], 202);
    }

    public function sqlBackupStatus(string $token): JsonResponse
    {
        $backup = Cache::get('queued-database-backup:'.$token);
        abort_unless($this->ownedBackup($backup), 404);

        return response()->json([
            'status' => $backup['status'],
            'download_url' => $backup['status'] === 'ready'
                ? route('superadmin.monthly_notifications.sql_backup.file', ['token' => $token])
                : null,
        ]);
    }

    public function queuedSqlBackup(string $token)
    {
        $backup = Cache::get('queued-database-backup:'.$token);
        abort_unless($this->ownedBackup($backup) && $backup['status'] === 'ready', 404);

        $path = storage_path('app/backups/'.basename((string) $backup['path']));
        abort_unless(is_file($path), 404);

        return response()->download($path, basename($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function ownedBackup(mixed $backup): bool
    {
        return is_array($backup) && (int) ($backup['user_id'] ?? 0) === (int) auth()->id();
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
