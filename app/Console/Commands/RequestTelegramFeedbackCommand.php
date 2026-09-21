<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TelegramBotIntegrationService;
use Illuminate\Console\Command;

class RequestTelegramFeedbackCommand extends Command
{
    protected $signature = 'bot:request-feedback';
    protected $description = 'Ask delivered Telegram customers for a one-time service rating.';
    public function handle(TelegramBotIntegrationService $bot): int
    {
        $count = 0;
        Order::query()->with('client')->whereIn('status', ['delivered', 'completed'])->whereNotNull('delivered_at')->where('delivered_at', '<=', now()->subDay())->doesntHave('feedback')->orderBy('id')->eachById(function (Order $order) use ($bot, &$count): void {
            if (! $order->client?->telegram_chat_id) return;
            $bot->queueFeedbackRequest($order); $count++;
        });
        $this->info("{$count} ta baholash so‘rovi navbatga qo‘shildi.");
        return self::SUCCESS;
    }
}
