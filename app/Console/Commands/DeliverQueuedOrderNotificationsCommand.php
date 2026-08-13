<?php

namespace App\Console\Commands;

use App\Jobs\DeliverOrderNotification;
use App\Models\OrderNotification;
use Illuminate\Console\Command;

class DeliverQueuedOrderNotificationsCommand extends Command
{
    protected $signature = 'notifications:deliver {--limit=100 : Maximum notifications per run}';

    protected $description = 'Dispatch queued customer notifications to their channel adapters';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $count = 0;

        OrderNotification::query()
            ->where('status', 'queued')
            ->where('attempts', '<', 3)
            ->where(function ($query): void {
                $query->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<', now()->subMinutes(5));
            })
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->each(function (int $notificationId) use (&$count): void {
                OrderNotification::query()->whereKey($notificationId)->update(['last_attempt_at' => now()]);
                DeliverOrderNotification::dispatch($notificationId);
                $count++;
            });

        $this->info("{$count} notification(s) dispatched.");

        return self::SUCCESS;
    }
}
