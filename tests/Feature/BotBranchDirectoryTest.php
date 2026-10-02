<?php

namespace Tests\Feature;

use App\Models\BotContent;
use App\Models\FilialModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BotBranchDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['bot.api_key' => 'bot-test-key']);
        $this->withHeaders(['Authorization' => 'Bearer bot-test-key', 'Accept' => 'application/json']);
    }

    public function test_branch_content_uses_public_crm_fields_instead_of_a_static_bot_text(): void
    {
        BotContent::create(['key' => 'branches', 'text' => 'Eski statik manzil']);
        $filial = FilialModel::create([
            'name' => 'Buxoro filiali', 'code' => 'BX', 'address' => 'Mustaqillik ko‘chasi, 10',
            'phone' => '+998900000001', 'work_start_time' => '10:30:00', 'work_end_time' => '19:15:00',
            'working_days' => [5, 1, 3, 1], 'monthly_expense' => 555222,
            'commission_percent' => 12, 'description' => 'Ichki filial izohi',
        ]);
        $response = $this->getJson('/api/v1/bot/content/branches')->assertOk()
            ->assertJsonPath('data.0.id', $filial->id)
            ->assertJsonPath('data.0.address', 'Mustaqillik ko‘chasi, 10')
            ->assertJsonPath('data.0.phone', '+998900000001')
            ->assertJsonPath('data.0.work_start_time', '10:30')
            ->assertJsonPath('data.0.work_end_time', '19:15')
            ->assertJsonPath('data.0.working_days', [1, 3, 5]);
        $text = $response->json('text');
        $this->assertStringContainsString('Buxoro filiali', $text);
        $this->assertStringContainsString('10:30–19:15', $text);
        $this->assertStringContainsString('Dushanba, Chorshanba, Juma', $text);
        $this->assertStringNotContainsString('Eski statik manzil', $text);
        foreach (['monthly_expense', 'target_amount', 'commission_percent', 'description', 'manager_id'] as $field) {
            $this->assertArrayNotHasKey($field, $response->json('data.0'));
        }
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_each_api_call_reflects_changed_added_and_deleted_filials(): void
    {
        $filial = FilialModel::create(['name' => 'Avvalgi filial', 'code' => 'A', 'address' => 'Eski manzil']);
        $this->getJson('/api/v1/bot/content/branches')->assertOk()->assertJsonPath('data.0.address', 'Eski manzil');
        $filial->update(['name' => 'Buxoro filial', 'address' => 'Yangi manzil', 'work_start_time' => '08:30', 'work_end_time' => '20:00']);
        $this->getJson('/api/v1/bot/content/branches')->assertOk()
            ->assertJsonPath('data.0.name', 'Buxoro filial')->assertJsonPath('data.0.address', 'Yangi manzil')
            ->assertJsonPath('data.0.work_start_time', '08:30')->assertJsonPath('data.0.work_end_time', '20:00');
        FilialModel::create(['name' => 'Toshkent filial', 'code' => 'T', 'address' => 'Toshkent manzili']);
        $this->getJson('/api/v1/bot/content/branches')->assertOk()->assertJsonCount(2, 'data');
        $filial->delete();
        $this->getJson('/api/v1/bot/content/branches')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Toshkent filial');
    }

    public function test_missing_hours_default_to_nine_to_eighteen_without_inventing_an_address(): void
    {
        FilialModel::create(['name' => 'A filial', 'code' => 'A']);
        FilialModel::create(['name' => 'B filial', 'code' => 'B', 'work_start_time' => '09:45']);
        FilialModel::create(['name' => 'C filial', 'code' => 'C', 'work_end_time' => '17:30']);
        $response = $this->getJson('/api/v1/bot/content/branches')->assertOk()
            ->assertJsonPath('data.0.address', null)
            ->assertJsonPath('data.0.work_start_time', '09:00')->assertJsonPath('data.0.work_end_time', '18:00')
            ->assertJsonPath('data.1.work_start_time', '09:45')->assertJsonPath('data.1.work_end_time', '18:00')
            ->assertJsonPath('data.2.work_start_time', '09:00')->assertJsonPath('data.2.work_end_time', '17:30');
        $this->assertStringContainsString('Ish vaqti: 09:00–18:00', $response->json('text'));
        $this->assertStringContainsString('Manzil: Operator orqali aniqlashtiring.', $response->json('text'));
    }

    public function test_an_empty_directory_still_returns_default_hours(): void
    {
        BotContent::create(['key' => 'branches', 'text' => 'Eski statik manzil']);
        $response = $this->getJson('/api/v1/bot/content/branches')->assertOk()->assertJsonPath('data', []);
        $this->assertStringContainsString('09:00–18:00', $response->json('text'));
        $this->assertStringNotContainsString('Eski statik manzil', $response->json('text'));
    }

    public function test_holidays_are_current_for_tashkent_and_exclude_past_invalid_and_duplicate_dates(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 20:00:00', 'UTC'));
        FilialModel::create([
            'name' => 'Buxoro filial', 'code' => 'B',
            'holiday_dates' => ['2026-10-03', '2026-10-01', '2026-10-02', '2026-10-03', '2026-99-01'],
        ]);
        $response = $this->getJson('/api/v1/bot/content/branches')->assertOk()
            ->assertJsonPath('data.0.holiday_dates', ['2026-10-02', '2026-10-03']);
        $this->assertStringContainsString('Dam olish sanalari: 02.10.2026, 03.10.2026', $response->json('text'));
    }

    public function test_directory_still_requires_the_bot_api_key(): void
    {
        $this->withHeader('Authorization', 'Bearer invalid-key')->getJson('/api/v1/bot/content/branches')->assertUnauthorized();
    }

    public function test_admin_edits_branch_information_in_filials_and_other_bot_text_remains_editable(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        BotContent::create(['key' => 'branches', 'text' => 'Eski statik manzil']);
        BotContent::create(['key' => 'useful-information', 'text' => 'Kerakli hujjatlarni tayyorlang.']);
        $this->actingAs($admin)->get('/bot-content')->assertOk()
            ->assertSee('Filiallar')->assertSee('09:00–18:00')->assertDontSee('Eski statik manzil')
            ->assertSee('Kerakli hujjatlarni tayyorlang.');
        $this->postJson('/bot-content', ['key' => 'branches', 'text' => 'Noto‘g‘ri manzil'])
            ->assertUnprocessable()->assertJsonValidationErrors('key');
        $this->post('/bot-content', ['key' => 'useful-information', 'text' => 'Yangilangan foydali ma’lumot'])
            ->assertRedirect();
        $this->getJson('/api/v1/bot/content/useful-information')->assertOk()->assertJsonPath('text', 'Yangilangan foydali ma’lumot');
    }
}
