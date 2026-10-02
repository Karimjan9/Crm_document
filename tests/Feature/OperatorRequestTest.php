<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\FilialModel;
use App\Models\OperatorRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OperatorRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_inquiry_creates_a_client_request_and_task_once_without_needing_a_lead(): void
    {
        $this->configureBot();
        $payload = $this->payload();
        $id = $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->json('data.id');
        // A retry is deduplicated by its UUID even when Telegram message ID changes.
        $payload['telegram_message_id'] = 21;
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->assertJsonPath('data.id', $id);
        $this->assertDatabaseCount('operator_requests', 1);
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('telegram_messages', 1);
        $this->assertDatabaseCount('work_items', 1);
        $item = OperatorRequest::findOrFail($id);
        $this->assertSame($payload['text'], $item->message);
        $this->assertSame('+998900000001', $item->phone);
        $this->assertTrue($item->phone_verified);
        $this->assertSame('900000001', ClientsModel::findOrFail($item->client_id)->phone_number);
        $this->assertDatabaseHas('work_items', ['id' => $item->work_item_id, 'type' => 'operator_request', 'description' => $payload['text']]);
        $payload['telegram_chat_id'] = '999';
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertConflict();
    }

    public function test_an_operator_contact_is_reused_for_intake_without_duplicating_or_renaming_the_client(): void
    {
        $filial = $this->configureBot();
        $client = ClientsModel::create(['name' => 'CRMdagi ism', 'phone_number' => '900000001', 'filial_id' => $filial->id]);
        $payload = $this->payload();
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk();
        $this->postJson('/api/v1/bot/leads', [
            'external_id' => (string) Str::uuid(),
            'customer' => [...$payload['customer'], 'telegram_chat_id' => '123', 'telegram_user_id' => '123'],
            'source' => ['channel' => 'telegram'], 'request' => ['mode' => 'compact', 'notes' => 'Pasport tarjimasi kerak.'],
        ])->assertCreated()->assertJsonPath('data.client_id', $client->id);
        $this->assertDatabaseCount('clients', 1);
        $this->assertSame('CRMdagi ism', $client->fresh()->name);
        $this->assertSame($client->id, OperatorRequest::query()->sole()->client_id);
    }

    public function test_detailed_requests_require_nonempty_text_and_verified_valid_contact(): void
    {
        config(['bot.api_key' => 'bot-test-key']);
        $this->postJson('/api/v1/bot/operator-requests', $this->payload())->assertUnauthorized();
        $this->configureBot();
        $payload = $this->payload();
        $payload['customer']['phone_verified'] = false;
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable()->assertJsonValidationErrors('customer.phone_verified');
        $payload = $this->payload();
        unset($payload['customer']['phone']);
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable()->assertJsonValidationErrors('customer.phone');
        $payload = $this->payload();
        $payload['customer']['phone'] = 'not-a-phone';
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable();
        $payload = $this->payload();
        $payload['text'] = '   ';
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable()->assertJsonValidationErrors('text');
        $payload['text'] = str_repeat('x', 2001);
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable();
        $payload = $this->payload();
        unset($payload['external_id']);
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('operator_requests', 0);
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_old_bot_requests_remain_supported_and_are_listed_and_deduplicated(): void
    {
        $this->configureBot();
        $payload = ['telegram_chat_id' => '123', 'telegram_message_id' => 20, 'reason' => 'customer_requested_operator'];
        $id = $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->json('data.id');
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->assertJsonPath('data.id', $id);
        $this->assertDatabaseCount('operator_requests', 1);
        $this->assertDatabaseCount('work_items', 1);
        $this->assertFalse(OperatorRequest::findOrFail($id)->phone_verified);
    }

    public function test_only_superadmin_sees_operator_sidebar_and_table_with_search_and_safe_full_text(): void
    {
        $this->configureBot();
        $payload = $this->payload();
        $payload['text'] = str_repeat('Tarjima haqida savol. ', 12).'<script>alert("test")</script>';
        $id = $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->json('data.id');
        $admin = $this->userWithRole('super_admin');
        $this->actingAs($admin)->get('/operators')->assertOk()
            ->assertSee('Operatorlar')->assertSee(route('operators.index'))
            ->assertSee('Ali Valiyev')->assertSee('+998900000001')
            ->assertSee('Kontakt tasdiqlangan')->assertSee($payload['text'])
            ->assertDontSee('<script>alert("test")</script>', false);
        $this->get('/operators?q=900000001&status=new')->assertOk()->assertSee('Ali Valiyev');
        $this->get('/operators?q=not-found')->assertOk()->assertDontSee('Ali Valiyev')->assertSee('Mos murojaat topilmadi');
        $this->get('/operators?status=resolved')->assertOk()->assertDontSee('Ali Valiyev');
        foreach (['employee', 'admin_filial', 'admin_manager', 'courier'] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/operators')->assertNotFound();
            $this->patch('/operators/'.$id, ['status' => 'resolved'])->assertNotFound();
        }
        $employee = $this->userWithRole('employee');
        $this->actingAs($employee)->get('/leads')->assertOk()->assertDontSee(route('operators.index'));
    }

    public function test_superadmin_status_changes_are_audited_and_update_the_work_item_without_resetting_on_retry(): void
    {
        $this->configureBot();
        $payload = $this->payload();
        $id = $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->json('data.id');
        $admin = $this->userWithRole('super_admin');
        $this->actingAs($admin)->patch('/operators/'.$id, ['status' => 'contacted'])->assertRedirect();
        $this->assertDatabaseHas('operator_requests', ['id' => $id, 'status' => 'contacted', 'handled_by_id' => $admin->id]);
        $item = OperatorRequest::findOrFail($id);
        $this->assertNotNull($item->handled_at);
        $this->assertSame('in_progress', $item->workItem->status);
        $this->patch('/operators/'.$id, ['status' => 'resolved'])->assertRedirect();
        $this->assertSame('done', $item->fresh()->workItem->status);
        $this->assertNotNull($item->fresh()->workItem->completed_at);
        $this->postJson('/api/v1/bot/operator-requests', $payload)->assertOk()->assertJsonPath('data.id', $id);
        $this->assertSame('resolved', $item->fresh()->status);
        $this->patch('/operators/'.$id, ['status' => 'invalid'])->assertUnprocessable();
        $this->patch('/operators/'.$id, ['status' => 'new'])->assertRedirect();
        $this->assertNull($item->fresh()->handled_by_id);
        $this->assertSame('open', $item->fresh()->workItem->status);
    }

    public function test_contact_restore_returns_only_a_verified_contact_for_matching_telegram_identity(): void
    {
        $this->configureBot();
        $this->getJson('/api/v1/bot/contacts/123?telegram_user_id=123')->assertOk()->assertJsonPath('data', null);
        $this->postJson('/api/v1/bot/operator-requests', $this->payload())->assertOk();
        $this->getJson('/api/v1/bot/contacts/123?telegram_user_id=123')->assertOk()->assertJsonPath('data.phone', '+998900000001');
        $this->getJson('/api/v1/bot/contacts/123?telegram_user_id=999')->assertOk()->assertJsonPath('data', null);
        $this->getJson('/api/v1/bot/contacts/999?telegram_user_id=123')->assertOk()->assertJsonPath('data', null);
        $this->getJson('/api/v1/bot/contacts/123')->assertUnprocessable();
    }

    public function test_a_previous_verified_intake_contact_can_be_restored_before_any_operator_request(): void
    {
        $this->configureBot();
        $payload = $this->payload();
        $this->postJson('/api/v1/bot/leads', [
            'external_id' => (string) Str::uuid(),
            'customer' => [...$payload['customer'], 'telegram_chat_id' => '123', 'telegram_user_id' => '123'],
            'source' => ['channel' => 'telegram'], 'request' => ['mode' => 'compact', 'notes' => 'Pasport tarjimasi kerak.'],
        ])->assertCreated();
        $this->getJson('/api/v1/bot/contacts/123?telegram_user_id=123')->assertOk()->assertJsonPath('data.phone', '+998900000001');
        $this->getJson('/api/v1/bot/contacts/123?telegram_user_id=999')->assertOk()->assertJsonPath('data', null);
    }

    private function configureBot(): FilialModel
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $filial = FilialModel::create(['name' => 'Operator filial', 'code' => 'OP']);
        config(['bot.api_key' => 'bot-test-key', 'bot.default_filial_id' => $filial->id]);
        $this->withHeaders(['Authorization' => 'Bearer bot-test-key', 'Accept' => 'application/json']);

        return $filial;
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function payload(): array
    {
        return [
            'external_id' => (string) Str::uuid(), 'telegram_chat_id' => '123',
            'telegram_user_id' => '123', 'telegram_username' => 'ali_test', 'telegram_message_id' => 20,
            'reason' => 'customer_requested_operator', 'text' => 'Diplom tarjimasi narxi va muddatini bilmoqchiman.',
            'customer' => ['name' => 'Ali Valiyev', 'phone' => '+998900000001', 'phone_verified' => true],
        ];
    }
}
