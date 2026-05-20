<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MonthlyNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'message', 'type', 'notify_date', 'created_at'])
            ->map(function (Notification $notification) {
                if ($notification->type === 'sql_backup') {
                    $notification->action_label = 'SQL nusxa olish';
                    $notification->action_url = route('superadmin.monthly_notifications.sql_backup');
                }

                return $notification;
            });

        return response()->json($notifications);
    }

    public function downloadSqlBackup()
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}");

        abort_unless(($database['driver'] ?? null) === 'mysql', 422, 'SQL backup faqat MySQL bazasi uchun ishlaydi.');

        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);

        $fileName = sprintf(
            '%s-sql-backup-%s.sql',
            $database['database'],
            now()->format('Y-m-d_H-i-s')
        );
        $path = $backupDirectory . DIRECTORY_SEPARATOR . $fileName;

        $command = [
            env('DB_DUMP_BINARY', 'mysqldump'),
            '--host=' . ($database['host'] ?? '127.0.0.1'),
            '--port=' . ($database['port'] ?? 3306),
            '--user=' . ($database['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            $database['database'],
        ];

        $environment = [];

        if (!empty($database['password'])) {
            $environment['MYSQL_PWD'] = $database['password'];
        }

        $process = new Process($command, base_path(), $environment, null, 300);
        $errorOutput = '';

        $file = fopen($path, 'w');

        try {
            $process->run(function ($type, $buffer) use ($file, &$errorOutput) {
                if ($type === Process::ERR) {
                    $errorOutput .= $buffer;
                    return;
                }

                fwrite($file, $buffer);
            });
        } finally {
            fclose($file);
        }

        if (!$process->isSuccessful()) {
            File::delete($path);

            abort(500, trim($errorOutput) ?: 'SQL backup olishda xatolik yuz berdi.');
        }

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
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
