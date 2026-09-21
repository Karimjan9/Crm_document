<?php

namespace App\Console\Commands;

use App\Services\SecurityAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityMonitorCommand extends Command
{
    protected $signature = 'security:monitor';

    protected $description = 'Checks operational security signals: storage, queue, backups, and TLS certificate expiry.';

    public function handle(SecurityAlertService $alerts): int
    {
        $this->checkDisk($alerts);
        $this->checkQueue($alerts);
        $this->checkBackup($alerts);
        $this->checkTls($alerts);

        return self::SUCCESS;
    }

    private function checkDisk(SecurityAlertService $alerts): void
    {
        $free = disk_free_space(storage_path());
        if ($free !== false && $free < (int) env('MONITOR_MIN_FREE_DISK_BYTES', 5 * 1024 * 1024 * 1024)) {
            $alerts->raise('low_disk_space', 'critical', ['free_bytes' => $free]);
        }
    }

    private function checkQueue(SecurityAlertService $alerts): void
    {
        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->where('failed_at', '>=', now()->subMinutes(15))->count();
            if ($failed > 0) {
                $alerts->raise('queue_failures', 'critical', ['failed_last_15_minutes' => $failed]);
            }
        }
        if (Schema::hasTable('jobs')) {
            $pending = DB::table('jobs')->count();
            if ($pending >= (int) env('MONITOR_QUEUE_BACKLOG_THRESHOLD', 100)) {
                $alerts->raise('queue_backlog', 'warning', ['pending_jobs' => $pending]);
            }
        }
    }

    private function checkBackup(SecurityAlertService $alerts): void
    {
        $files = glob(storage_path('app/backups/*.enc')) ?: [];
        $latest = $files ? max(array_map('filemtime', $files)) : false;
        if ($latest === false || $latest < now()->subHours(30)->getTimestamp()) {
            $alerts->raise('backup_stale', 'critical', ['latest_backup_at' => $latest ?: null]);
        }
    }

    private function checkTls(SecurityAlertService $alerts): void
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (! $host || app()->environment(['local', 'testing'])) {
            return;
        }

        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => true, 'verify_peer_name' => true]]);
        $socket = @stream_socket_client('ssl://'.$host.':443', $errno, $error, 10, STREAM_CLIENT_CONNECT, $context);
        if (! $socket) {
            $alerts->raise('tls_check_failed', 'critical', ['host' => $host]);

            return;
        }
        $parameters = stream_context_get_params($socket);
        fclose($socket);
        $certificate = openssl_x509_parse($parameters['options']['ssl']['peer_certificate'] ?? false);
        $expiresAt = (int) ($certificate['validTo_time_t'] ?? 0);
        $days = $expiresAt ? (int) floor(($expiresAt - now()->getTimestamp()) / 86400) : -1;
        if ($days < (int) env('MONITOR_TLS_MIN_DAYS', 21)) {
            $alerts->raise('tls_certificate_expiring', 'warning', ['host' => $host, 'days_remaining' => $days]);
        }
    }
}
