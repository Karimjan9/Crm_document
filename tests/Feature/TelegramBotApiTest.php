<?php

namespace Tests\Feature;

use App\Models\BotContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
