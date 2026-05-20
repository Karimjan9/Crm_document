<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class CreateMonthlyNotifications extends Command
{
    protected $signature = 'notifications:create-monthly';

    protected $description = 'Create monthly reminder notifications for super admins.';

    public function handle(): int
    {
        $today = now();

        if ($today->day !== 10) {
            return self::SUCCESS;
        }

        $items = [
            [
                'title' => 'SQL Backup',
                'message' => 'Bugun SQL backup qilish kerak.',
                'type' => 'sql_backup',
            ],
            [
                'title' => "Oylik to'lov",
                'message' => "Bugun oylik to'lov kuni.",
                'type' => 'monthly_payment',
            ],
        ];

        User::role('super_admin')
            ->select('id')
            ->each(function (User $user) use ($items, $today) {
                foreach ($items as $item) {
                    Notification::firstOrCreate([
                        'user_id' => $user->id,
                        'type' => $item['type'],
                        'notify_date' => $today->toDateString(),
                    ], [
                        'title' => $item['title'],
                        'message' => $item['message'],
                        'is_read' => false,
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
