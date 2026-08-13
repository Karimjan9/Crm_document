<?php

namespace App\Console\Commands;

use App\Services\MarginLeakDetectorService;
use Illuminate\Console\Command;

class ScanMarginLeaksCommand extends Command
{
    protected $signature = 'finance:scan-margin-leaks {--from=} {--to=} {--filial=}';

    protected $description = 'Detect suspicious discounts, unpaid orders and margin leaks';

    public function handle(MarginLeakDetectorService $detector): int
    {
        $from = $this->option('from') ? now()->parse($this->option('from'))->startOfDay() : today()->startOfMonth();
        $to = $this->option('to') ? now()->parse($this->option('to'))->endOfDay() : today()->endOfDay();
        $leaks = $detector->scan($from, $to, $this->option('filial') ? (int) $this->option('filial') : null);

        $this->info(count($leaks) . ' margin leak signal(s) detected.');

        return self::SUCCESS;
    }
}
