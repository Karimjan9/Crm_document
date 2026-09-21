<?php

namespace App\Jobs;

use App\Models\BotWebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverBotWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 4;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $eventId) { $this->afterCommit(); }

    public function handle(): void
    {
        $event = BotWebhookEvent::query()->find($this->eventId);
        if (! $event || $event->status === 'dispatched') return;
        $url = (string) config('bot.webhook_url');
        $secret = (string) config('bot.webhook_secret');
        if ($url === '' || $secret === '') {
            $event->forceFill(['status' => 'skipped', 'last_error' => 'Bot webhook sozlanmagan.'])->save();
            return;
        }
        $body = json_encode(['event_id' => $event->event_id, 'type' => $event->type, 'payload' => $event->payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);
        $event->increment('attempts');
        try {
            Http::timeout((int) config('bot.timeout', 15))->withHeaders(['X-CRM-Timestamp' => $timestamp, 'X-CRM-Signature' => $signature])->withBody($body, 'application/json')->post($url)->throw();
            $event->forceFill(['status' => 'dispatched', 'dispatched_at' => now(), 'last_error' => null])->save();
        } catch (\Throwable $exception) {
            $event->forceFill(['status' => 'queued', 'last_error' => class_basename($exception)])->save();
            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $event = BotWebhookEvent::query()->find($this->eventId);
        if (! $event) return;
        $event->forceFill(['status' => 'failed', 'last_error' => class_basename($exception)])->save();
        if ($event->telegram_message_id) {
            \App\Models\TelegramMessage::query()->whereKey($event->telegram_message_id)->update(['delivery_status' => 'failed', 'delivery_error' => class_basename($exception)]);
        }
        if ($event->type === 'lead.follow_up' && ($leadId = data_get($event->payload, 'follow_up_lead_id'))) {
            \App\Models\Lead::query()->whereKey($leadId)->update(['bot_follow_up_event_id' => null]);
        }
    }
}
