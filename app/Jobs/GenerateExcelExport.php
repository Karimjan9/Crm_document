<?php

namespace App\Jobs;

use App\Http\Controllers\SuperAdmin\ExcelExportController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateExcelExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public string $dataset,
        public string $token,
        public int $userId,
    ) {}

    public function handle(ExcelExportController $controller): void
    {
        $export = $controller->buildQueuedExport($this->dataset, User::query()->find($this->userId));
        $path = 'exports/'.$this->token.'.xls';

        Storage::disk('private')->put($path, $export['content']);
        Cache::put($this->cacheKey(), [
            'status' => 'ready',
            'user_id' => $this->userId,
            'path' => $path,
            'filename' => $export['filename'],
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
        return 'queued-excel-export:'.$this->token;
    }
}
