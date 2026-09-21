<?php

namespace App\Services;

use App\Jobs\DeliverBotWebhook;
use App\Models\BotContent;
use App\Models\BotDeliveryReport;
use App\Models\BotIntakeRequest;
use App\Models\BotMarketingConsent;
use App\Models\BotWebhookEvent;
use App\Models\ClientsModel;
use App\Models\CustomerFeedback;
use App\Models\FilialModel;
use App\Models\Lead;
use App\Models\Order;
use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramBotIntegrationService
{
    public function createIntake(array $payload): Lead
    {
        return DB::transaction(function () use ($payload): Lead {
            $existing = BotIntakeRequest::query()->where('external_id', $payload['external_id'])->with('lead')->first();
            if ($existing?->lead) return $existing->lead;
            $customer = $payload['customer']; $phone = $this->normalizePhone((string) $customer['phone']);
            $client = ClientsModel::query()->where('phone_number', $phone)->first();
            $filialId = $client?->filial_id ?: $this->filialId();
            $client ??= ClientsModel::create(['name' => $customer['name'], 'phone_number' => $phone, 'filial_id' => $filialId, 'telegram_chat_id' => $customer['telegram_chat_id']]);
            $client->forceFill(['telegram_chat_id' => $customer['telegram_chat_id'], 'name' => $client->name ?: $customer['name'], 'filial_id' => $client->filial_id ?: $filialId])->save();
            $request = $payload['request'];
            $lead = Lead::create(['filial_id' => $filialId, 'client_id' => $client->id, 'assigned_to_id' => $this->assigneeId($filialId), 'name' => $customer['name'], 'phone' => $phone, 'source' => 'telegram', 'campaign' => data_get($payload, 'source.entry_payload'), 'interested_service' => $request['document_type'], 'status' => 'new', 'notes' => $this->intakeNotes($request)]);
            BotIntakeRequest::create(['external_id' => $payload['external_id'], 'client_id' => $client->id, 'lead_id' => $lead->id, 'payload' => $payload]);
            $this->recordIncoming($client, $lead, $customer['telegram_chat_id'], null, $this->summary($request), 'intake');
            $lead->workItems()->create(['filial_id' => $filialId, 'assigned_to_id' => $lead->assigned_to_id, 'type' => 'lead_response', 'title' => 'Telegram leadga javob berish: '.$lead->name, 'description' => $request['document_type'], 'status' => 'open', 'priority' => 'high', 'due_at' => now()->addMinutes((int) config('bot.response_minutes', 15)), 'metadata' => ['source' => 'telegram', 'telegram_chat_id' => $customer['telegram_chat_id']]]);
            return $lead;
        });
    }

    public function storeLeadAttachment(Lead $lead, UploadedFile $file, array $metadata): TelegramMessage
    {
        $path = app(FileSecurityService::class)->store($file, 'telegram/leads/'.$lead->id);
        $metadata['file_name'] = $file->getClientOriginalName();
        return $this->recordIncoming($lead->client, $lead, (string) $metadata['telegram_chat_id'], (int) $metadata['telegram_message_id'], null, 'attachment', $path, $metadata);
    }
    public function storeMessageAttachment(?ClientsModel $client, ?Lead $lead, UploadedFile $file, array $metadata): TelegramMessage
    {
        $path = app(FileSecurityService::class)->store($file, 'telegram/messages/'.now()->format('Y/m'));
        $metadata['file_name'] = $file->getClientOriginalName();
        return $this->recordIncoming($client, $lead, (string) $metadata['telegram_chat_id'], (int) $metadata['telegram_message_id'], $metadata['caption'] ?? null, 'attachment', $path, $metadata);
    }
    public function recordIncoming(?ClientsModel $client, ?Lead $lead, string $chatId, ?int $messageId, ?string $body, string $type = 'text', ?string $path = null, array $metadata = []): TelegramMessage
    {
        $attributes = ['client_id' => $client?->id, 'lead_id' => $lead?->id, 'type' => $type, 'body' => $body, 'attachment_path' => $path, 'attachment_meta' => $metadata, 'file_expires_at' => $path ? now()->addDays((int) config('bot.file_retention_days', 365)) : null, 'delivery_status' => 'received'];
        $message = $messageId === null ? TelegramMessage::create([...$attributes, 'telegram_chat_id' => $chatId, 'telegram_message_id' => null, 'direction' => 'incoming']) : TelegramMessage::query()->firstOrCreate(['telegram_chat_id' => $chatId, 'telegram_message_id' => $messageId, 'direction' => 'incoming'], $attributes);
        if ($lead && $body && $message->wasRecentlyCreated) $lead->activities()->create(['type' => 'telegram', 'body' => Str::limit($body, 2000), 'happened_at' => now()]);
        return $message;
    }
    public function requestOperator(string $chatId, ?int $messageId, string $reason): void
    {
        $client = ClientsModel::query()->where('telegram_chat_id', $chatId)->first();
        $lead = $client ? Lead::query()->where('client_id', $client->id)->whereNotIn('status', ['won', 'lost'])->latest()->first() : null;
        $this->recordIncoming($client, $lead, $chatId, $messageId, 'Operator so‘rovi: '.$reason, 'operator_request');
        if ($lead) $lead->workItems()->updateOrCreate(['type' => 'operator_request', 'status' => 'open'], ['filial_id' => $lead->filial_id, 'assigned_to_id' => $lead->assigned_to_id, 'title' => 'Telegram operator so‘rovi: '.$lead->name, 'priority' => 'urgent', 'due_at' => now()->addMinutes(5)]);
    }
    public function setMarketingConsent(string $chatId, bool $consent): BotMarketingConsent
    {
        $client = ClientsModel::query()->where('telegram_chat_id', $chatId)->first();
        return BotMarketingConsent::query()->updateOrCreate(['telegram_chat_id' => $chatId], ['client_id' => $client?->id, 'consent' => $consent, 'consented_at' => $consent ? now() : null, 'revoked_at' => $consent ? null : now()]);
    }
    public function sendOperatorReply(Lead $lead, User $user, string $text): TelegramMessage
    {
        $lead->loadMissing('client'); $chatId = (string) $lead->client?->telegram_chat_id; abort_if($chatId === '', 422, 'Mijozning Telegram chat IDsi yo‘q.');
        $message = $this->queueOutbound('operator.reply', $chatId, ['telegram_chat_id' => $chatId, 'message' => $text], $lead->client, null, $lead, $user);
        app(LeadService::class)->addActivity($lead, $user, 'message', $text);
        return $message;
    }
    public function queueOrderStatus(Order $order): void
    {
        $order->loadMissing('client'); if (! $order->client?->telegram_chat_id) return;
        $text = $this->content('notification.status.'.$order->status, 'Buyurtmangiz :code holati: :status.');
        $this->queueOutbound('order.status_changed', (string) $order->client->telegram_chat_id, ['telegram_chat_id' => (string) $order->client->telegram_chat_id, 'message' => $this->replace($text, $order)], $order->client, $order);
    }
    public function queuePaymentUpdate(Order $order): void
    {
        $order->loadMissing('client'); if (! $order->client?->telegram_chat_id) return;
        $text = $this->content('notification.payment_received', 'To‘lovingiz qabul qilindi. Qoldiq: :balance :currency.');
        $this->queueOutbound('payment.updated', (string) $order->client->telegram_chat_id, ['telegram_chat_id' => (string) $order->client->telegram_chat_id, 'message' => $this->replace($text, $order)], $order->client, $order);
    }
    public function queueLeadFollowUp(Lead $lead): void
    {
        $lead->loadMissing('client'); if (! $lead->client?->telegram_chat_id) return;
        $text = $this->content('lead.follow_up', 'Salom. Kecha hujjatingiz bo‘yicha narx yuborgandik. Savolingiz qolgan bo‘lsa, shu yerga yozing, yordam beramiz.');
        $message = $this->queueOutbound('lead.follow_up', (string) $lead->client->telegram_chat_id, ['telegram_chat_id' => (string) $lead->client->telegram_chat_id, 'message' => $text, 'follow_up_lead_id' => $lead->id], $lead->client, null, $lead);
        $lead->forceFill(['bot_follow_up_event_id' => $message->event_id])->save();
    }
    public function queueFeedbackRequest(Order $order): void
    {
        $order->loadMissing('client'); if (! $order->client?->telegram_chat_id) return;
        $this->queueOutbound('feedback.request', (string) $order->client->telegram_chat_id, ['telegram_chat_id' => (string) $order->client->telegram_chat_id, 'order_id' => $order->id, 'message' => 'Xizmatimiz sizga qanchalik ma’qul bo‘ldi? 1 dan 5 gacha baholang.'], $order->client, $order);
    }
    public function saveFeedback(string $chatId, int $orderId, int $rating): CustomerFeedback
    {
        $order = Order::query()->with('client')->whereKey($orderId)->firstOrFail();
        abort_unless((string) $order->client?->telegram_chat_id === $chatId, 403);
        $feedback = CustomerFeedback::query()->updateOrCreate(['order_id' => $order->id], ['client_id' => $order->client_id, 'rating' => $rating, 'submitted_at' => now()]);
        if ($rating <= 3) $order->workItems()->updateOrCreate(['type' => 'customer_feedback', 'status' => 'open'], ['filial_id' => $order->filial_id, 'assigned_to_id' => $order->responsible_user_id, 'title' => 'Mijoz fikrini tekshirish: '.$order->order_code, 'priority' => 'high', 'due_at' => now()->addHour(), 'metadata' => ['rating' => $rating]]);
        return $feedback;
    }
    public function reportDelivery(array $payload): void
    {
        BotDeliveryReport::query()->updateOrCreate(['event_id' => $payload['event_id']], ['telegram_chat_id' => $payload['telegram_chat_id'], 'telegram_message_id' => $payload['telegram_message_id'] ?? null, 'status' => $payload['status'], 'reported_at' => now()]);
        $event = BotWebhookEvent::query()->where('event_id', $payload['event_id'])->first();
        if (! $event) return;
        TelegramMessage::query()->where('event_id', $event->event_id)->update(['telegram_message_id' => $payload['telegram_message_id'] ?? null, 'delivery_status' => $payload['status'], 'delivered_at' => now(), 'sent_at' => $payload['status'] === 'sent' ? now() : null, 'delivery_error' => $payload['status'] === 'failed' ? 'Telegram delivery failed' : null]);
        if ($event->type === 'lead.follow_up' && ($leadId = data_get($event->payload, 'follow_up_lead_id'))) Lead::query()->whereKey($leadId)->update(['bot_follow_up_sent_at' => now()]);
        if ($event->type === 'feedback.request' && ($orderId = data_get($event->payload, 'order_id'))) { $order = Order::query()->find($orderId); if ($order) CustomerFeedback::query()->firstOrCreate(['order_id' => $order->id], ['client_id' => $order->client_id]); }
    }
    public function content(string $key, ?string $fallback = null): string { return (string) (BotContent::query()->where('key', $key)->value('text') ?: $fallback ?: ''); }
    private function queueOutbound(string $type, string $chatId, array $payload, ?ClientsModel $client = null, ?Order $order = null, ?Lead $lead = null, ?User $user = null): TelegramMessage
    {
        $event = BotWebhookEvent::create(['event_id' => (string) Str::uuid(), 'type' => $type, 'telegram_chat_id' => $chatId, 'payload' => $payload]);
        $message = TelegramMessage::create(['client_id' => $client?->id, 'lead_id' => $lead?->id, 'order_id' => $order?->id, 'telegram_chat_id' => $chatId, 'event_id' => $event->event_id, 'direction' => 'outgoing', 'type' => $type, 'body' => $payload['message'], 'sent_by_id' => $user?->id, 'delivery_status' => 'queued']);
        $event->forceFill(['telegram_message_id' => $message->id])->save();
        DeliverBotWebhook::dispatch($event->id);
        return $message;
    }
    private function filialId(): int { return (int) (config('bot.default_filial_id') ?: FilialModel::query()->value('id') ?: throw new \RuntimeException('Bot uchun filial topilmadi.')); }
    private function assigneeId(int $filialId): ?int { return config('bot.default_assignee_id') ?: User::query()->where('filial_id', $filialId)->whereHas('roles', fn ($q) => $q->whereIn('name', ['employee', 'admin_filial']))->value('id'); }
    private function normalizePhone(string $phone): string { $digits = preg_replace('/\D+/', '', $phone) ?: ''; return str_starts_with($digits, '998') && strlen($digits) === 12 ? substr($digits, 3) : $digits; }
    private function intakeNotes(array $request): string { return implode("\n", ['Telegram so‘rovi', 'Maqsad: '.$request['purpose'], 'Hujjat: '.$request['document_type'], 'Shoshilinchlik: '.$request['urgency'], 'Izoh: '.($request['notes'] ?: '—')]); }
    private function summary(array $request): string { return 'Telegram so‘rovi: '.$request['document_type'].' — '.$request['purpose']; }
    private function replace(string $text, Order $order): string { return strtr($text, [':code' => $order->order_code, ':status' => $order->status_label, ':balance' => number_format($order->balance_amount, 0, '.', ' '), ':currency' => $order->currency ?: 'UZS', ':promised_at' => optional($order->promised_at)->timezone('Asia/Tashkent')->format('d.m.Y H:i') ?: 'aniqlanmoqda']); }
}
