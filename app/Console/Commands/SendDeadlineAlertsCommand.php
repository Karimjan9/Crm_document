<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\CustomerNotificationService;
use App\Services\DeadlineRadarService;
use Illuminate\Console\Command;

class SendDeadlineAlertsCommand extends Command
{
    protected $signature = 'orders:send-deadline-alerts';

    protected $description = 'Queue customer notifications for orders at deadline risk';

    public function handle(DeadlineRadarService $radar, CustomerNotificationService $notifications): int
    {
        $count = 0;
        Order::query()
            ->whereNotIn('status', ['completed', 'cancelled', 'delivered'])
            ->whereNotNull('promised_at')
            ->where('promised_at', '<=', now()->addDays(3))
            ->whereDoesntHave('notifications', fn ($query) => $query
                ->where('event', 'deadline_approaching')
                ->where('created_at', '>=', now()->subDay()))
            ->cursor()
            ->each(function (Order $order) use ($radar, $notifications, &$count): void {
                $risk = $radar->assess($order);
                if (in_array($risk['level'], ['yellow', 'red'], true)) {
                    $notifications->queueEvent(
                        $order,
                        'deadline_approaching',
                        "Buyurtma {$order->order_code}: {$risk['summary']} " . implode('; ', $risk['reasons'])
                    );
                    $count++;
                }
            });

        $this->info("{$count} deadline alert(s) queued.");

        return self::SUCCESS;
    }
}
