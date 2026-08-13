<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Services\PartnerBillingService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GeneratePartnerInvoicesCommand extends Command
{
    protected $signature = 'b2b:generate-invoices {--period= : Billing period in YYYY-MM format}';

    protected $description = 'Generate monthly invoices for active B2B partners.';

    public function handle(PartnerBillingService $billing): int
    {
        $period = (string) ($this->option('period') ?: now()->subMonth()->format('Y-m'));
        try {
            $start = CarbonImmutable::createFromFormat('!Y-m', $period)->startOfMonth();
        } catch (\Throwable) {
            $this->error('Period YYYY-MM formatida bo\'lishi kerak.');

            return self::FAILURE;
        }
        $end = $start->endOfMonth();
        $created = 0;

        Partner::query()->active()->orderBy('id')->each(function (Partner $partner) use ($billing, $start, $end, &$created): void {
            $invoice = $billing->generateForPeriod($partner, $start, $end);
            if ($invoice) {
                $created++;
                $this->line("{$partner->code}: {$invoice->invoice_number}");
            }
        });

        $this->info("B2B invoice yaratildi: {$created} ta.");

        return self::SUCCESS;
    }
}
