<?php

namespace Tests\Feature;

use App\Jobs\DeliverBotWebhook;
use App\Models\BotIntakeRequest;
use App\Models\FilialModel;
use App\Models\Lead;
use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BotRequestDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_intakes_appear_once_in_the_dashboard_including_existing_and_closed_requests(): void
    {
        $employee = $this->user('employee');
        $lead = $this->intake($employee);
        $closed = $this->intake($employee, 'Madina Karimova');
        $closed->update(['status' => 'won']);
        Lead::create(['name' => 'Oddiy telefon lead', 'filial_id' => $employee->filial_id, 'assigned_to_id' => $employee->id, 'status' => 'new']);
        $before = BotIntakeRequest::count();

        $response = $this->actingAs($this->user('super_admin'))->get('/bot-requests')->assertOk()
            ->assertSee('Botdan so‘rovlar')->assertSee('Ali Valiyev')->assertSee('Madina Karimova')
            ->assertSee('Kontakt tasdiqlangan')->assertSee('+998900000001')
            ->assertSee(route('bot-requests.show', $lead))->assertDontSee('Oddiy telefon lead');
        $this->assertSame(2, $response->viewData('requests')->total());
        $this->assertSame(2, $response->viewData('counts')->sum());
        $this->assertSame(1, $response->viewData('counts')['won']);
        $this->get('/bot-requests')->assertOk();
        $this->assertDatabaseCount('bot_intake_requests', $before);
        $this->assertDatabaseCount('leads', 3);
    }

    public function test_employee_and_filial_admin_scope_applies_to_rows_counts_search_and_details(): void
    {
        $employee = $this->user('employee');
        $mine = $this->intake($employee, 'Mening mijozim');
        $colleague = $this->user('employee', $employee->filial_id);
        $colleagueLead = $this->intake($colleague, 'Hamkasb mijozi');
        $elsewhere = $this->intake($this->user('employee'), 'Boshqa filial mijozi');
        $response = $this->actingAs($employee)->get('/bot-requests')->assertOk()
            ->assertSee('Mening mijozim')->assertDontSee('Hamkasb mijozi')->assertDontSee('Boshqa filial mijozi');
        $this->assertSame(1, $response->viewData('counts')->sum());
        $this->get('/bot-requests?q=Hamkasb')->assertOk()->assertDontSee('Hamkasb mijozi');
        $this->get('/bot-requests?q=%2B998900000003')->assertOk()->assertViewHas('requests', fn ($requests) => $requests->isEmpty());
        $this->get(route('bot-requests.show', $mine))->assertOk();
        $this->get(route('bot-requests.show', $colleagueLead))->assertNotFound();
        $this->get(route('bot-requests.show', $elsewhere))->assertNotFound();

        $admin = $this->user('admin_filial', $employee->filial_id);
        $response = $this->actingAs($admin)->get('/bot-requests')->assertOk()
            ->assertSee('Mening mijozim')->assertSee('Hamkasb mijozi')->assertDontSee('Boshqa filial mijozi');
        $this->assertSame(2, $response->viewData('counts')->sum());
        $this->get(route('bot-requests.show', $elsewhere))->assertNotFound();
        $response = $this->actingAs($this->user('admin_manager'))->get('/bot-requests')->assertOk();
        $this->assertSame(3, $response->viewData('counts')->sum());
    }

    public function test_filters_find_telegram_contact_text_and_id_and_paginate_without_losing_filters(): void
    {
        $employee = $this->user('employee');
        $first = $this->intake($employee);
        $second = $this->intake($employee, 'Madina Karimova');
        $second->update(['status' => 'quoted']);
        $this->actingAs($employee)->get('/bot-requests?status=quoted&q=Madina')->assertOk()
            ->assertViewHas('requests', fn ($requests) => $requests->total() === 1 && $requests->first()->is($second));
        foreach (['@ali_test', '+998900000001', 'Diplomni', (string) $first->id] as $search) {
            $this->get('/bot-requests?q='.urlencode($search))->assertOk()
                ->assertViewHas('requests', fn ($requests) => $requests->contains('id', $first->id));
        }
        for ($i = 0; $i < 20; $i++) {
            $this->intake($employee, 'Pagination '.$i);
        }
        $this->get('/bot-requests?status=new&q=Pagination')->assertOk()
            ->assertViewHas('requests', fn ($requests) => $requests->total() === 20);
        $this->get('/bot-requests?status=new')->assertOk()
            ->assertViewHas('requests', fn ($requests) => $requests->total() === 21 && str_contains($requests->nextPageUrl(), 'status=new'));
        $this->getJson('/bot-requests?status=invalid')->assertUnprocessable();
        $this->get('/bot-requests?q=topilmaydigan')->assertOk()->assertSee('So‘rov topilmadi');
    }

    public function test_details_preserve_full_escaped_text_captions_and_private_file_access(): void
    {
        Storage::fake('private');
        $employee = $this->user('employee');
        $text = str_repeat('Diplom tarjimasi kerak. ', 60)."\n<script>alert('test')</script>";
        $attachment = ['telegram_file_id' => 'test-file', 'telegram_message_id' => 10, 'file_name' => 'diplom.pdf', 'mime_type' => 'application/pdf', 'size' => 10240, 'kind' => 'document', 'caption' => 'Diplomning old tomoni'];
        $lead = $this->intake($employee, notes: $text, attachments: [$attachment]);
        $this->actingAs($employee)->get(route('bot-requests.show', $lead))->assertOk()
            ->assertSee($text)->assertDontSee("<script>alert('test')</script>", false)
            ->assertSee('Qabul qilingan fayllar: 0 / 1');
        $messageId = $this->withHeaders(['Authorization' => 'Bearer bot-dashboard-test-key'])->post('/api/v1/bot/leads/'.$lead->id.'/attachments', [
            ...$attachment, 'telegram_chat_id' => (string) $lead->client->telegram_chat_id,
            'file' => UploadedFile::fake()->create('diplom.pdf', 10, 'application/pdf'),
        ])->assertCreated()->json('data.id');
        $message = TelegramMessage::findOrFail($messageId);
        $this->get(route('bot-requests.show', $lead))->assertOk()->assertSee('diplom.pdf')
            ->assertSee('Diplomning old tomoni')->assertSee(route('telegram-messages.file', $message))
            ->assertDontSee($message->attachment_path);
        $this->get('/bot-requests')->assertOk()->assertViewHas('requests', fn ($requests) => $requests->first()->intake_files_count === 1);
        $this->get(route('telegram-messages.file', $message))->assertOk();
        $other = $this->user('employee', $employee->filial_id);
        $this->actingAs($other)->get(route('telegram-messages.file', $message))->assertForbidden();
    }

    public function test_superadmin_is_read_only_and_other_roles_cannot_open_the_dashboard(): void
    {
        $employee = $this->user('employee');
        $lead = $this->intake($employee);
        $this->actingAs($this->user('super_admin'))->get(route('bot-requests.show', $lead))->assertOk()
            ->assertSee('Kuzatuv rejimi')->assertDontSee(route('leads.update', $lead))
            ->assertDontSee(route('leads.telegram.reply', $lead))->assertDontSee(route('leads.convert', $lead));
        $this->put(route('leads.update', $lead), ['name' => $lead->name, 'status' => 'contacted'])->assertForbidden();
        foreach (['courier', 'partner', 'user'] as $role) {
            $this->actingAs($this->user($role))->get('/bot-requests')->assertNotFound();
            $this->get(route('bot-requests.show', $lead))->assertNotFound();
        }
        $ordinary = Lead::create(['name' => 'Botdan emas', 'filial_id' => $employee->filial_id, 'assigned_to_id' => $employee->id, 'status' => 'new']);
        $this->actingAs($employee)->get(route('bot-requests.show', $ordinary))->assertNotFound();
    }

    public function test_staff_can_handle_reply_and_convert_the_same_request_using_existing_crm_actions(): void
    {
        Queue::fake();
        $employee = $this->user('employee');
        $lead = $this->intake($employee);
        $original = $lead->botIntakeRequest->payload;
        $this->actingAs($employee)->get(route('bot-requests.show', $lead))->assertOk()
            ->assertSee(route('leads.update', $lead))->assertSee(route('leads.telegram.reply', $lead));
        $this->from(route('bot-requests.show', $lead))->put(route('leads.update', $lead), [
            'name' => $lead->name, 'phone' => $lead->phone, 'filial_id' => $lead->filial_id,
            'assigned_to_id' => $employee->id, 'status' => 'contacted', 'interested_service' => 'Diplom tarjimasi',
        ])->assertRedirect(route('bot-requests.show', $lead));
        $this->post(route('leads.telegram.reply', $lead), ['message' => 'Murojaatingiz qabul qilindi.'])->assertRedirect();
        Queue::assertPushed(DeliverBotWebhook::class);
        $this->assertSame($original, $lead->fresh()->botIntakeRequest->payload);
        $this->get(route('bot-requests.show', $lead))->assertOk()->assertSee('Diplom tarjimasi')->assertSee('Murojaatingiz qabul qilindi.');
        $this->post(route('leads.convert', $lead))->assertRedirect();
        $this->assertNotNull($lead->fresh()->converted_order_id);
        $this->get(route('bot-requests.show', $lead))->assertOk()->assertSee('Buyurtmaga aylandi');
        $this->assertDatabaseCount('bot_intake_requests', 1);
        $this->assertDatabaseCount('leads', 1);
    }

    private function user(string $role, ?int $filialId = null): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate($role, 'web');
        $filialId ??= FilialModel::create(['name' => 'Filial '.Str::random(5), 'code' => Str::upper(Str::random(5))])->id;
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);

        return $user;
    }

    private function intake(User $employee, string $name = 'Ali Valiyev', string $notes = 'Diplomni ingliz tiliga tarjima qilish kerak.', array $attachments = []): Lead
    {
        config(['bot.api_key' => 'bot-dashboard-test-key', 'bot.default_filial_id' => $employee->filial_id, 'bot.default_assignee_id' => $employee->id, 'security.files.scan_enabled' => false]);
        $number = BotIntakeRequest::count() + 1;
        $response = $this->withHeader('Authorization', 'Bearer bot-dashboard-test-key')->postJson('/api/v1/bot/leads', [
            'external_id' => (string) Str::uuid(),
            'customer' => ['telegram_chat_id' => (string) $number, 'telegram_user_id' => (string) $number, 'telegram_username' => $number === 1 ? 'ali_test' : 'client_'.$number, 'name' => $name, 'phone' => '+99890'.str_pad((string) $number, 7, '0', STR_PAD_LEFT), 'phone_verified' => true],
            'source' => ['channel' => 'telegram'], 'request' => ['mode' => 'compact', 'notes' => $notes],
            'attachments' => $attachments, 'transcript' => [],
        ])->assertCreated();

        return Lead::findOrFail($response->json('data.id'));
    }
}
