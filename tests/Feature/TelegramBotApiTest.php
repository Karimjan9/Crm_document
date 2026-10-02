<?php

namespace Tests\Feature;

use App\Models\BotContent;
use App\Models\BotIntakeRequest;
use App\Models\ClientsModel;
use App\Models\FilialModel;
use App\Models\Lead;
use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TelegramBotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bot_api_requires_its_own_machine_key(): void
    {
        config(['bot.api_key' => 'bot-test-key']);
        $this->getJson('/api/v1/bot/content/branches')->assertUnauthorized();
    }

    public function test_bot_api_returns_only_configured_customer_content(): void
    {
        config(['bot.api_key' => 'bot-test-key']);
        BotContent::create(['key' => 'branches', 'text' => 'Toshkent, 09:00–18:00']);

        $this->withHeader('Authorization', 'Bearer bot-test-key')
            ->getJson('/api/v1/bot/content/branches')
            ->assertOk()
            ->assertJsonPath('text', 'Toshkent, 09:00–18:00');
    }

    public function test_courier_cannot_open_lead_customer_data(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('courier', 'web');
        $courier = User::factory()->create();
        $courier->assignRole('courier');

        $this->actingAs($courier)->get('/leads')->assertNotFound();
    }

    public function test_compact_text_creates_one_complete_intake_and_response_task_on_retry(): void
    {
        $operator = $this->configureBot();
        $payload = $this->intakePayload();
        // The bot still sends compatibility fields for servers with the older API.
        $payload['request'] += ['purpose' => $payload['request']['notes'], 'document_type' => 'Mutaxassis aniqlashtiradi', 'urgency' => 'Mutaxassis aniqlashtiradi'];
        $response = $this->postJson('/api/v1/bot/leads', $payload)->assertCreated();
        $leadId = $response->json('data.id');
        $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->assertJsonPath('data.id', $leadId);

        $lead = Lead::findOrFail($leadId);
        $this->assertSame('Ali Valiyev', $lead->name);
        $this->assertSame('900000001', $lead->phone);
        $this->assertSame($operator->id, $lead->assigned_to_id);
        $this->assertSame('instagram_korea', $lead->campaign);
        $this->assertNull($lead->interested_service);
        $this->assertSame($payload['request']['notes'], $lead->notes);
        $this->assertSame('123', $lead->client->telegram_chat_id);
        $this->assertSame($payload['request']['notes'], $lead->telegramMessages->sole()->body);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('bot_intake_requests', 1);
        $this->assertDatabaseCount('work_items', 1);
        $this->assertDatabaseCount('lead_activities', 1);
        $this->assertDatabaseHas('work_items', ['lead_id' => $leadId, 'type' => 'lead_response', 'assigned_to_id' => $operator->id, 'status' => 'open', 'description' => $payload['request']['notes']]);
        $saved = BotIntakeRequest::query()->sole()->payload;
        $this->assertNull($saved['request']['document_type']);
        $this->assertNull($saved['request']['urgency']);
        $this->assertTrue($saved['customer']['phone_verified']);
    }

    public function test_file_only_intake_keeps_captions_and_deduplicates_uploaded_files(): void
    {
        $operator = $this->configureBot();
        Storage::fake('private');
        $payload = $this->intakePayload();
        $payload['request'] = ['mode' => 'compact', 'notes' => null];
        $payload['attachments'] = [$this->attachment(10), $this->attachment(11)];
        $leadId = $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->json('data.id');

        $firstId = null;
        foreach ($payload['attachments'] as $attachment) {
            $upload = $this->post('/api/v1/bot/leads/'.$leadId.'/attachments', [
                ...$attachment, 'file' => UploadedFile::fake()->create($attachment['file_name'], 10, 'application/pdf'),
            ])->assertCreated();
            $firstId ??= $upload->json('data.id');
        }
        $attachment = $payload['attachments'][0];
        $this->post('/api/v1/bot/leads/'.$leadId.'/attachments', [
            ...$attachment, 'file' => UploadedFile::fake()->create($attachment['file_name'], 10, 'application/pdf'),
        ])->assertCreated()->assertJsonPath('data.id', $firstId);
        $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->assertJsonPath('data.id', $leadId);

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('telegram_messages', 3);
        $this->assertCount(2, Storage::disk('private')->allFiles());
        $message = TelegramMessage::findOrFail($firstId);
        $this->assertSame($attachment['caption'], $message->body);
        $this->assertSame($attachment['file_name'], $message->attachment_meta['file_name']);
        Storage::disk('private')->assertExists($message->attachment_path);

        $this->actingAs($operator)->get('/leads')->assertOk()
            ->assertSee('Telegram murojaati')
            ->assertSee('Mijoz o‘z kontaktini yuborgan')
            ->assertSee('diplom-10.pdf')->assertSee('diplom-11.pdf')
            ->assertSee($attachment['caption'])
            ->assertSee(route('telegram-messages.file', $message));
    }

    public function test_empty_unverified_or_invalid_attachment_intakes_are_rejected(): void
    {
        $this->configureBot();
        $payload = $this->intakePayload();
        $empty = [...$payload, 'request' => ['mode' => 'compact', 'notes' => '   ']];
        $this->postJson('/api/v1/bot/leads', $empty)->assertUnprocessable()->assertJsonValidationErrors('request.notes');
        $empty['request']['purpose'] = 'Hujjat bo‘yicha murojaat';
        $this->postJson('/api/v1/bot/leads', $empty)->assertUnprocessable();
        $this->postJson('/api/v1/bot/leads', [...$empty, 'attachments' => [[]]])->assertUnprocessable();
        $payload['customer']['phone_verified'] = false;
        $this->postJson('/api/v1/bot/leads', $payload)->assertUnprocessable()->assertJsonValidationErrors('customer.phone_verified');
        $payload['customer']['phone_verified'] = true;
        $payload['attachments'] = [$this->attachment(10), $this->attachment(10)];
        $this->postJson('/api/v1/bot/leads', $payload)->assertUnprocessable();
        $payload['attachments'] = [$this->attachment(10)];
        $payload['attachments'][0]['size'] = 20971521;
        $this->postJson('/api/v1/bot/leads', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_legacy_detailed_request_remains_supported(): void
    {
        $this->configureBot();
        $payload = $this->intakePayload();
        $payload['request'] = ['purpose' => 'O‘qish uchun', 'document_type' => 'Diplom', 'urgency' => '3 kun', 'notes' => 'Ingliz tiliga tarjima'];
        $this->postJson('/api/v1/bot/leads', $payload)->assertCreated();
        $lead = Lead::query()->sole();
        $this->assertSame('Diplom', $lead->interested_service);
        $this->assertStringContainsString('Shoshilinchlik: 3 kun', $lead->notes);
        $this->assertSame('guided', $lead->botIntakeRequest->payload['request']['mode']);
    }

    public function test_a_new_request_reuses_the_client_without_overwriting_the_crm_name(): void
    {
        $operator = $this->configureBot();
        $client = ClientsModel::create(['name' => 'CRMdagi ism', 'phone_number' => '900000001', 'filial_id' => $operator->filial_id]);
        $payload = $this->intakePayload();
        $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->assertJsonPath('data.client_id', $client->id);
        $payload['external_id'] = (string) Str::uuid();
        $payload['request']['notes'] = 'Yana bitta hujjat tarjimasi kerak';
        $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->assertJsonPath('data.client_id', $client->id);
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('leads', 2);
        $this->assertSame('CRMdagi ism', $client->fresh()->name);
    }

    public function test_intake_view_preserves_full_text_and_employee_access_boundaries(): void
    {
        $operator = $this->configureBot();
        $payload = $this->intakePayload();
        $text = str_repeat('Tarjima kerak. ', 60)."\n<script>alert('test')</script>";
        $payload['request']['notes'] = $text;
        $payload['attachments'] = [$this->attachment(10)];
        $leadId = $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->json('data.id');
        $this->actingAs($operator)->get('/leads')->assertOk()
            ->assertSee($text)->assertDontSee("<script>alert('test')</script>", false)
            ->assertSee('Qabul qilingan fayllar: 0 / 1');
        $lead = Lead::findOrFail($leadId);
        $this->put(route('leads.update', $lead), [
            'name' => $lead->name, 'status' => 'contacted', 'assigned_to_id' => $operator->id,
            'interested_service' => 'Diplom tarjimasi', 'next_follow_up_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $this->assertSame('Diplom tarjimasi', $lead->fresh()->interested_service);
        $this->assertSame($text, $lead->fresh()->botIntakeRequest->payload['request']['notes']);

        $other = User::factory()->create(['filial_id' => $operator->filial_id]);
        $other->assignRole('employee');
        $this->actingAs($other)->get('/leads')->assertOk()->assertDontSee('Ali Valiyev');
        $message = TelegramMessage::create([
            'lead_id' => $lead->id, 'client_id' => $lead->client_id, 'telegram_chat_id' => '123',
            'telegram_message_id' => 10, 'direction' => 'incoming', 'type' => 'attachment', 'attachment_path' => 'telegram/test.pdf',
        ]);
        $this->get(route('telegram-messages.file', $message))->assertForbidden();
        $this->put(route('leads.update', $lead), ['name' => 'Changed', 'status' => 'contacted'])->assertForbidden();
    }

    public function test_file_retry_keeps_its_original_lead_after_the_customer_opens_a_new_intake(): void
    {
        $this->configureBot();
        Storage::fake('private');
        $payload = $this->intakePayload();
        $originalLeadId = $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->json('data.id');
        $upload = ['telegram_chat_id' => '123', 'telegram_message_id' => 10, 'kind' => 'document', 'caption' => 'Qo‘shimcha hujjat'];
        $messageId = $this->post('/api/v1/bot/messages/attachments', [
            ...$upload, 'file' => UploadedFile::fake()->create('diplom.pdf', 10, 'application/pdf'),
        ])->assertCreated()->json('data.id');

        $payload['external_id'] = (string) Str::uuid();
        $newLeadId = $this->postJson('/api/v1/bot/leads', $payload)->assertCreated()->json('data.id');
        $this->post('/api/v1/bot/messages/attachments', [
            ...$upload, 'file' => UploadedFile::fake()->create('diplom.pdf', 10, 'application/pdf'),
        ])->assertCreated()->assertJsonPath('data.id', $messageId);
        $this->post('/api/v1/bot/leads/'.$newLeadId.'/attachments', [
            ...$this->attachment(10), 'file' => UploadedFile::fake()->create('diplom.pdf', 10, 'application/pdf'),
        ])->assertConflict();
        $this->assertSame($originalLeadId, TelegramMessage::findOrFail($messageId)->lead_id);
        $this->assertCount(1, Storage::disk('private')->allFiles());
        $this->assertDatabaseCount('telegram_messages', 3);
    }

    private function configureBot(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('employee', 'web');
        $filial = FilialModel::create(['name' => 'Bot filial', 'code' => 'BOT']);
        $operator = User::factory()->create(['filial_id' => $filial->id]);
        $operator->assignRole('employee');
        config(['bot.api_key' => 'bot-test-key', 'bot.default_filial_id' => $filial->id, 'bot.default_assignee_id' => $operator->id, 'security.files.scan_enabled' => false]);
        $this->withHeaders(['Authorization' => 'Bearer bot-test-key', 'Accept' => 'application/json']);

        return $operator;
    }

    private function intakePayload(): array
    {
        return [
            'external_id' => (string) Str::uuid(),
            'customer' => ['telegram_chat_id' => '123', 'telegram_user_id' => '123', 'telegram_username' => 'ali_test', 'name' => 'Ali Valiyev', 'phone' => '+998900000001', 'phone_verified' => true],
            'source' => ['channel' => 'telegram', 'entry_payload' => 'instagram_korea'],
            'request' => ['mode' => 'compact', 'notes' => "Diplomni ingliz tiliga tarjima qilish kerak.\nJuma kuniga kerak."],
            'attachments' => [], 'transcript' => [],
        ];
    }

    private function attachment(int $messageId): array
    {
        return ['telegram_file_id' => 'file-'.$messageId, 'telegram_message_id' => $messageId, 'file_name' => 'diplom-'.$messageId.'.pdf', 'mime_type' => 'application/pdf', 'size' => 10240, 'kind' => 'document', 'caption' => 'Hujjat '.$messageId.' izohi'];
    }
}
