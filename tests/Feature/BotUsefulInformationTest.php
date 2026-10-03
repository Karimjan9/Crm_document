<?php

namespace Tests\Feature;

use App\Models\BotContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BotUsefulInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_an_editable_default_library_without_writing_on_read(): void
    {
        $response = $this->api()->getJson('/api/v1/bot/content/useful-information')->assertOk()
            ->assertJsonPath('title', 'Foydali ma’lumotlar')->assertJsonCount(3, 'topics')
            ->assertJsonPath('topics.0.id', 'clear-photo');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseCount('bot_contents', 0);
        $this->withHeader('Authorization', 'Bearer wrong')->getJson('/api/v1/bot/content/useful-information')->assertUnauthorized();
    }

    public function test_admin_can_edit_order_publish_and_hide_topics_without_exposing_internal_metadata(): void
    {
        $admin = $this->user('super_admin');
        BotContent::create(['key' => 'useful-information', 'text' => 'Avvalgi kirish', 'metadata' => ['internal_note' => 'Yopiq ichki eslatma']]);
        $data = $this->catalog();
        $data['topics'][] = [...$data['topics'][0], 'id' => 'draft', 'title' => 'Qoralama mavzu', 'published' => 0];
        $data['topics'][] = [...$data['topics'][0], 'id' => 'second', 'title' => 'Ikkinchi mavzu'];
        $this->actingAs($admin)->put('/bot-useful-information', $data)->assertRedirect(route('bot-useful-information.edit'));
        $item = BotContent::query()->sole();
        $this->assertSame($admin->id, $item->updated_by_id);
        $this->assertSame(['Rasm aniq bo‘lsin.', 'Barcha sahifalarni qo‘shing.'], $item->metadata['topics'][0]['checklist']);
        $this->assertSame('Yopiq ichki eslatma', $item->metadata['internal_note']);
        $response = $this->api()->getJson('/api/v1/bot/content/useful-information')->assertOk()
            ->assertJsonPath('title', 'Mijoz uchun qo‘llanma')->assertJsonPath('intro', 'Kerakli mavzuni tanlang.')
            ->assertJsonCount(2, 'topics')->assertJsonPath('topics.1.id', 'second');
        $this->assertStringNotContainsString('Qoralama mavzu', $response->getContent());
        foreach (['published', 'updated_by_id', 'internal_note', 'legacy_text', 'metadata'] as $field) {
            $this->assertArrayNotHasKey($field, $response->json());
            $this->assertArrayNotHasKey($field, $response->json('topics.0'));
        }
        $data['topics'] = [$data['topics'][2], $data['topics'][0]];
        $data['topics'][0]['body'] = 'Eng yangi ma’lumot.';
        $this->put('/bot-useful-information', $data)->assertRedirect();
        $this->getJson('/api/v1/bot/content/useful-information')->assertOk()
            ->assertJsonPath('topics.0.id', 'second')->assertJsonPath('topics.0.body', 'Eng yangi ma’lumot.');
    }

    public function test_old_plain_text_is_preserved_and_the_general_text_editor_keeps_topic_metadata(): void
    {
        BotContent::create(['key' => 'useful-information', 'text' => 'Oldingi mijoz uchun ma’lumot.']);
        $this->api()->getJson('/api/v1/bot/content/useful-information')->assertOk()
            ->assertJsonPath('text', 'Oldingi mijoz uchun ma’lumot.')->assertJsonPath('intro', 'Oldingi mijoz uchun ma’lumot.');
        $this->actingAs($this->user('admin_manager'))->put('/bot-useful-information', $this->catalog())->assertRedirect();
        $metadata = BotContent::query()->sole()->metadata;
        $this->post('/bot-content', ['key' => 'useful-information', 'text' => 'Yangilangan kirish.'])->assertRedirect();
        $this->assertSame($metadata, BotContent::query()->sole()->metadata);
        $this->api()->getJson('/api/v1/bot/content/useful-information')->assertOk()
            ->assertJsonPath('intro', 'Yangilangan kirish.')->assertJsonPath('topics.0.id', 'photo');
    }

    public function test_editor_requires_admin_role_and_renders_untrusted_text_as_plain_content(): void
    {
        $this->get('/bot-useful-information')->assertRedirect('/login');
        foreach (['employee', 'admin_filial', 'courier', 'user'] as $role) {
            $this->actingAs($this->user($role))->get('/bot-useful-information')->assertNotFound();
            $this->put('/bot-useful-information', $this->catalog())->assertNotFound();
        }
        $admin = $this->user('super_admin');
        $data = $this->catalog();
        $data['topics'][0]['body'] = "<script>alert('text')</script>";
        $this->actingAs($admin)->put('/bot-useful-information', $data)->assertRedirect();
        $this->get('/bot-useful-information')->assertOk()->assertSee('Telegram ko‘rinishi')
            ->assertSee($data['topics'][0]['body'])->assertDontSee($data['topics'][0]['body'], false);
    }

    public function test_invalid_callbacks_duplicate_ids_unknown_icons_and_oversized_lists_are_rejected(): void
    {
        $this->actingAs($this->user('super_admin'));
        $valid = $this->catalog();
        $invalid = $valid;
        $invalid['topics'][0]['id'] = 'malicious:id';
        $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics.0.id');
        $invalid = $valid;
        $invalid['topics'][] = $invalid['topics'][0];
        $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics.0.id');
        $invalid = $valid;
        $invalid['topics'][0]['icon'] = 'html';
        $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics.0.icon');
        foreach ([implode("\n", array_fill(0, 7, 'Band')), str_repeat('a', 201)] as $list) {
            $invalid = $valid;
            $invalid['topics'][0]['checklist_text'] = $list;
            $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics.0.checklist_text');
        }
        $invalid = $valid;
        $invalid['topics'][0]['body'] = str_repeat('a', 1601);
        $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics.0.body');
        $invalid = $valid;
        $invalid['topics'] = array_map(fn ($index) => [...$valid['topics'][0], 'id' => 'topic-'.$index], range(1, 13));
        $this->putJson('/bot-useful-information', $invalid)->assertUnprocessable()->assertJsonValidationErrors('topics');
        $this->assertDatabaseCount('bot_contents', 0);
    }

    public function test_empty_topics_stay_empty_and_an_empty_intro_does_not_restore_stale_text(): void
    {
        BotContent::create(['key' => 'useful-information', 'text' => 'Eski matn']);
        $this->actingAs($this->user('super_admin'))->put('/bot-useful-information', ['title' => 'Yangiliklar', 'intro' => '', 'topics' => []])->assertRedirect();
        $this->api()->getJson('/api/v1/bot/content/useful-information')->assertOk()->assertJsonPath('intro', '')
            ->assertJsonPath('topics', [])->assertDontSee('Eski matn');
        $this->get('/bot-useful-information')->assertOk()->assertDontSee('Hujjatni sifatli yuborish');
    }

    private function user(string $role): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function api(): static
    {
        config(['bot.api_key' => 'useful-test-key']);

        return $this->withHeader('Authorization', 'Bearer useful-test-key');
    }

    private function catalog(): array
    {
        return [
            'title' => 'Mijoz uchun qo‘llanma', 'intro' => 'Kerakli mavzuni tanlang.',
            'topics' => [[
                'id' => 'photo', 'icon' => 'photo', 'title' => 'Hujjat rasmi', 'summary' => 'Rasmni sifatli yuboring.',
                'body' => 'Hujjatdagi matn ravshan ko‘rinsin.', 'checklist_text' => " Rasm aniq bo‘lsin.\n\nBarcha sahifalarni qo‘shing. ",
                'tip' => 'Rasmga izoh qo‘shishingiz mumkin.', 'published' => 1,
            ]],
        ];
    }
}
