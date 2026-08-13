<?php

namespace Tests\Feature;

use App\Models\FilialModel;
use App\Models\User;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('employee', 'web');
    }

    public function test_api_token_can_be_issued_and_used_without_a_web_session(): void
    {
        $filial = FilialModel::create([
            'name' => 'API Filial',
            'code' => 'API',
        ]);

        $user = User::factory()->create([
            'filial_id' => $filial->id,
            'login' => 'api-user',
            'password' => Hash::make('TestPassword-2026!'),
        ]);
        $user->assignRole('employee');

        $tokenResponse = $this->postJson('/api/v1/auth/token', [
            'login' => 'api-user',
            'password' => 'TestPassword-2026!',
            'device_name' => 'feature-test',
        ])->assertCreated()
            ->assertJsonStructure([
                'token',
                'token_type',
                'expires_at',
                'abilities',
                'user' => ['id', 'login', 'roles'],
            ]);

        $this->assertSame(TokenController::ABILITIES, $tokenResponse->json('abilities'));
        $this->assertNotNull($user->tokens()->latest('id')->first()?->expires_at);

        $token = $tokenResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('data.login', 'api-user');

        $this->assertGuest('web');
    }

    public function test_api_document_routes_require_sanctum_and_role(): void
    {
        $this->getJson('/api/v1/documents/1')->assertUnauthorized();

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['documents:read']);

        $this->getJson('/api/v1/documents/1')->assertNotFound();
    }
}
