<?php

namespace App\Console\Commands;

use App\Services\ExpenseManagementService;
use Illuminate\Console\Command;

class GenerateRecurringExpensesCommand extends Command
{
    protected $signature = 'expenses:generate-recurring {--date= : Generate due expenses as of YYYY-MM-DD}';

    protected $description = 'Create due instances of recurring expenses';

    public function handle(ExpenseManagementService $expenses): int
    {
        $date = $this->option('date') ? now()->parse($this->option('date')) : today();
        $created = $expenses->generateRecurring($date);

        $this->info("{$created} recurring expense(s) generated.");

        return self::SUCCESS;
    }
}
