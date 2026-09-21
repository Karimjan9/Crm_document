<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\TelegramBotIntegrationService;
use Illuminate\Console\Command;

class SendTelegramLeadFollowUpsCommand extends Command
{
    protected $signature = 'bot:send-lead-follow-ups';
    protected $description = 'Send one polite Telegram reminder for unanswered quoted leads.';

    public function handle(TelegramBotIntegrationService $bot): int
    {
        $count = 0;
        Lead::query()->with('client')->where('status', 'quoted')->whereNull('bot_follow_up_sent_at')->whereNull('bot_follow_up_event_id')->where('quoted_at', '<=', now()->subDay())->orderBy('id')->eachById(function (Lead $lead) use ($bot, &$count): void {
            if (! $lead->client?->telegram_chat_id) return;
            $bot->queueLeadFollowUp($lead);
            $count++;
        });
        $this->info("{$count} ta follow-up navbatga qo‘shildi.");
        return self::SUCCESS;
    }
}
