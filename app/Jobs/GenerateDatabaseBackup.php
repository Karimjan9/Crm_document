<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class GenerateDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public string $token,
        public int $userId,
        public int $keep = 7,
    ) {}

    public function handle(): void
    {
        $exitCode = Artisan::call('backup:database', [
            '--path' => storage_path('app/backups'),
            '--keep' => $this->keep,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('Queued database backup command failed.');
        }

        preg_match('/^BACKUP_PATH=(.+)$/m', Artisan::output(), $matches);
        if (empty($matches[1])) {
            throw new RuntimeException('Queued database backup did not return a backup path.');
        }

        Cache::put($this->cacheKey(), [
            'status' => 'ready',
            'user_id' => $this->userId,
            'path' => basename(trim($matches[1])),
        ], now()->addHours(2));
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
        Cache::put($this->cacheKey(), [
            'status' => 'failed',
            'user_id' => $this->userId,
        ], now()->addHours(2));
    }

    private function cacheKey(): string
    {
        return 'queued-database-backup:'.$this->token;
    }
}
