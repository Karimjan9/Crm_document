<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClientsModel;
use App\Models\BotIntakeRequest;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OperatorRequest;
use App\Services\BotBranchDirectory;
use App\Services\TelegramBotIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TelegramBotController extends Controller
{
    public function __construct(
        private readonly TelegramBotIntegrationService $bot,
        private readonly BotBranchDirectory $branches,
    ) {}

    public function lead(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['required', 'uuid'], 'customer.telegram_chat_id' => ['required', 'string', 'max:80'],
            'customer.telegram_user_id' => ['nullable', 'string', 'max:80'], 'customer.telegram_username' => ['nullable', 'string', 'max:120'],
            'customer.name' => ['required', 'string', 'max:160'], 'customer.phone' => ['required', 'string', 'max:40'], 'customer.phone_verified' => ['required', 'boolean', 'accepted'],
            'source.channel' => ['required', 'in:telegram'], 'source.entry_payload' => ['nullable', 'string', 'max:120'],
            'request' => ['required', 'array:mode,purpose,document_type,urgency,notes'],
            'request.mode' => ['nullable', 'in:compact,guided'],
            'request.purpose' => ['nullable', 'string', 'max:500'], 'request.document_type' => ['nullable', 'string', 'max:180'],
            'request.urgency' => ['nullable', 'string', 'max:120'], 'request.notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:10'], 'transcript' => ['nullable', 'array', 'max:50'],
            'attachments.*' => ['required', 'array:telegram_file_id,telegram_message_id,file_name,mime_type,size,kind,caption'],
            'attachments.*.telegram_file_id' => ['required', 'string', 'max:255'],
            'attachments.*.telegram_message_id' => ['required', 'integer', 'min:1', 'distinct'],
            'attachments.*.file_name' => ['required', 'string', 'max:255'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:120'],
            'attachments.*.size' => ['required', 'integer', 'between:0,20971520'],
            'attachments.*.kind' => ['required', 'in:photo,document'],
            'attachments.*.caption' => ['nullable', 'string', 'max:2000'],
        ]);
        $purpose = trim((string) data_get($data, 'request.purpose'));
        if (data_get($data, 'request.mode') === 'compact' && $purpose === 'Hujjat bo‘yicha murojaat') {
            $purpose = '';
        }
        if ($purpose === '' && trim((string) data_get($data, 'request.notes')) === '' && empty($data['attachments'])) {
            throw ValidationException::withMessages(['request.notes' => 'Murojaat matni yoki kamida bitta hujjat kerak.']);
        }
        $lead = $this->bot->createIntake($data);

        return response()->json(['data' => ['id' => $lead->id, 'client_id' => $lead->client_id]], 201);
    }

    public function leadAttachment(Request $request, Lead $lead)
    {
        $data = $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'], 'telegram_file_id' => ['required', 'string', 'max:255'], 'telegram_message_id' => ['required', 'integer', 'min:1'], 'kind' => ['required', 'in:photo,document'], 'caption' => ['nullable', 'string', 'max:2000']]);
        $message = $this->bot->storeLeadAttachment($lead, $request->file('file'), ['telegram_file_id' => $data['telegram_file_id'], 'telegram_message_id' => $data['telegram_message_id'], 'kind' => $data['kind'], 'caption' => $data['caption'] ?? null, 'telegram_chat_id' => (string) $lead->client?->telegram_chat_id]);

        return response()->json(['data' => ['id' => $message->id]], 201);
    }

    public function message(Request $request)
    {
        $data = $request->validate(['telegram_chat_id' => ['required', 'string', 'max:80'], 'telegram_user_id' => ['nullable', 'string', 'max:80'], 'telegram_username' => ['nullable', 'string', 'max:120'], 'telegram_message_id' => ['required', 'integer'], 'text' => ['required', 'string', 'max:4000']]);
        $client = ClientsModel::query()->where('telegram_chat_id', $data['telegram_chat_id'])->first();
        $lead = $client ? Lead::query()->where('client_id', $client->id)->whereNotIn('status', ['won', 'lost'])->latest()->first() : null;
        $message = $this->bot->recordIncoming($client, $lead, $data['telegram_chat_id'], (int) $data['telegram_message_id'], $data['text']);

        return response()->json(['data' => ['id' => $message->id]], 201);
    }

    public function messageAttachment(Request $request)
    {
        $data = $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'], 'telegram_chat_id' => ['required', 'string', 'max:80'], 'telegram_user_id' => ['nullable', 'string', 'max:80'], 'telegram_message_id' => ['required', 'integer'], 'caption' => ['nullable', 'string', 'max:2000'], 'kind' => ['required', 'in:photo,document']]);
        $client = ClientsModel::query()->where('telegram_chat_id', $data['telegram_chat_id'])->first();
        $lead = $client ? Lead::query()->where('client_id', $client->id)->whereNotIn('status', ['won', 'lost'])->latest()->first() : null;
        $message = $this->bot->storeMessageAttachment($client, $lead, $request->file('file'), ['telegram_chat_id' => $data['telegram_chat_id'], 'telegram_user_id' => $data['telegram_user_id'] ?? null, 'telegram_message_id' => $data['telegram_message_id'], 'caption' => $data['caption'] ?? null, 'kind' => $data['kind']]);

        return response()->json(['data' => ['id' => $message->id]], 201);
    }

    public function operatorRequest(Request $request)
    {
        $detailed = $request->hasAny(['external_id', 'text', 'customer']);
        $data = $request->validate([
            'telegram_chat_id' => ['required', 'string', 'max:80'],
            'telegram_user_id' => ['nullable', 'string', 'max:80'],
            'telegram_username' => ['nullable', 'string', 'max:120'],
            'telegram_message_id' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:160'],
            'external_id' => [Rule::requiredIf($detailed), 'uuid'],
            'text' => [Rule::requiredIf($detailed), 'string', 'max:2000'],
            'customer' => [Rule::requiredIf($detailed), 'array'],
            'customer.name' => [Rule::requiredIf($detailed), 'string', 'max:160'],
            'customer.phone' => [Rule::requiredIf($detailed), 'string', 'max:40', 'regex:/^\+?[0-9]{7,15}$/'],
            'customer.phone_verified' => [Rule::requiredIf($detailed), 'boolean', $detailed ? 'accepted' : 'nullable'],
        ]);
        $operatorRequest = $this->bot->requestOperator($data);

        return response()->json(['ok' => true, 'data' => ['id' => $operatorRequest->id]]);
    }

    public function orders(Request $request)
    {
        $data = $request->validate(['telegram_chat_id' => ['required', 'string', 'max:80'], 'phone' => ['required', 'string', 'max:40']]);
        $phone = preg_replace('/\D+/', '', $data['phone']) ?: '';
        if (str_starts_with($phone, '998') && strlen($phone) === 12) {
            $phone = substr($phone, 3);
        }
        $client = ClientsModel::query()->where('telegram_chat_id', $data['telegram_chat_id'])->where('phone_number', $phone)->first();
        if (! $client) {
            return response()->json(['data' => []]);
        }
        $orders = Order::query()->where('client_id', $client->id)->whereNotIn('status', ['completed', 'cancelled'])->latest()->get()->map(fn (Order $order) => ['code' => $order->order_code, 'status' => $order->status, 'promised_at' => optional($order->promised_at)->timezone('Asia/Tashkent')->format('d.m.Y H:i'), 'paid_amount' => (float) $order->paid_amount, 'balance_amount' => $order->balance_amount, 'currency' => $order->currency ?: 'UZS', 'delivery_type' => $order->delivery_type]);

        return response()->json(['data' => $orders]);
    }

    public function verifiedContact(Request $request, string $chatId)
    {
        $data = $request->validate(['telegram_user_id' => ['required', 'string', 'max:80']]);
        $operatorRequest = OperatorRequest::query()->where('telegram_chat_id', $chatId)
            ->where('telegram_user_id', $data['telegram_user_id'])->where('phone_verified', true)->latest('id')->first();
        $intake = BotIntakeRequest::query()->where('payload->customer->telegram_chat_id', $chatId)
            ->where('payload->customer->telegram_user_id', $data['telegram_user_id'])
            ->where('payload->customer->phone_verified', true)->latest('id')->first();
        $phone = $operatorRequest?->phone;
        if ($intake && (! $operatorRequest || $intake->created_at->gt($operatorRequest->created_at))) {
            $phone = data_get($intake->payload, 'customer.phone');
        }

        return response()->json(['data' => $phone ? ['phone' => $phone] : null]);
    }

    public function marketingConsent(Request $request)
    {
        $data = $request->validate(['telegram_chat_id' => ['required', 'string', 'max:80'], 'consent' => ['required', 'boolean']]);
        $consent = $this->bot->setMarketingConsent($data['telegram_chat_id'], (bool) $data['consent']);

        return response()->json(['data' => ['consent' => $consent->consent]]);
    }

    public function deliveryReport(Request $request)
    {
        $data = $request->validate(['event_id' => ['required', 'uuid'], 'telegram_chat_id' => ['required', 'string', 'max:80'], 'telegram_message_id' => ['nullable', 'integer'], 'status' => ['required', 'in:sent,failed']]);
        $this->bot->reportDelivery($data);

        return response()->json(['ok' => true]);
    }

    public function feedback(Request $request)
    {
        $data = $request->validate(['telegram_chat_id' => ['required', 'string', 'max:80'], 'order_id' => ['required', 'integer', 'exists:orders,id'], 'rating' => ['required', 'integer', 'between:1,5']]);
        $feedback = $this->bot->saveFeedback($data['telegram_chat_id'], $data['order_id'], $data['rating']);

        return response()->json(['data' => ['rating' => $feedback->rating]]);
    }

    public function content(string $key)
    {
        abort_unless(in_array($key, ['branches', 'useful-information'], true), 404);
        if ($key === 'branches') {
            return response()->json($this->branches->content())->header('Cache-Control', 'no-store');
        }

        return response()->json(['text' => $this->bot->content($key, 'Ma’lumot hozir yangilanmoqda.')]);
    }
}
