<?php

namespace Tests\Feature;

use App\Http\Middleware\RoleMiddleware;
use App\Models\ClientsModel;
use App\Models\FilialModel;
use App\Models\Holiday;
use App\Models\ServicesModel;
use App\Models\User;
use App\Support\StoresDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_role_middleware_allows_pipe_separated_roles(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $request = Request::create('/superadmin/index');
        $request->setUserResolver(fn () => $user);

        $response = (new RoleMiddleware)->handle(
            $request,
            fn () => response('ok'),
            'admin_manager|super_admin'
        );

        $this->assertSame('ok', $response->getContent());
    }

    public function test_role_middleware_rejects_users_without_required_role(): void
    {
        Role::findOrCreate('employee', 'web');

        $user = User::factory()->create();
        $user->assignRole('employee');

        $request = Request::create('/superadmin/index');
        $request->setUserResolver(fn () => $user);

        $this->expectException(NotFoundHttpException::class);

        (new RoleMiddleware)->handle(
            $request,
            fn () => response('ok'),
            'admin_manager|super_admin'
        );
    }

    public function test_holiday_mutation_routes_require_authentication(): void
    {
        $this->post('/holidays', [
            'date' => now()->addMonth()->toDateString(),
        ])->assertRedirect('/login');

        $this->delete('/holidays/'.now()->addMonth()->toDateString())
            ->assertRedirect('/login');
    }

    public function test_holiday_update_ignores_uneditable_created_by_field(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $creator = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $holiday = Holiday::create([
            'title' => 'Original holiday',
            'date' => '2030-01-02',
            'type' => 'national',
            'color' => '#3366ff',
            'description' => 'Original description',
            'is_recurring' => false,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $this->actingAs($admin)
            ->put('/superadmin/fl/holidays/'.$holiday->id, [
                'title' => 'Updated holiday',
                'created_by' => $admin->id,
                'is_active' => false,
            ])
            ->assertOk();

        $holiday->refresh();

        $this->assertSame('Updated holiday', $holiday->title);
        $this->assertFalse($holiday->is_active);
        $this->assertSame($creator->id, $holiday->created_by);
    }

    public function test_service_process_mode_is_persisted_explicitly(): void
    {
        $filial = FilialModel::create([
            'name' => 'Test Filial',
            'code' => 'TST',
            'description' => 'Test branch',
        ]);

        $user = User::factory()->create(['filial_id' => $filial->id]);
        $this->actingAs($user);

        $client = ClientsModel::create([
            'name' => 'Test Client',
            'phone_number' => '901234567',
            'description' => 'Test client',
        ]);

        $service = ServicesModel::create([
            'name' => 'Translation',
            'description' => 'Test service',
            'price' => 100000,
            'deadline' => 3,
        ]);

        $documentStore = new class
        {
            use StoresDocuments;

            public function store(array $payload)
            {
                return $this->storeDocumentFromPayload($payload);
            }
        };

        $document = $documentStore->store([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'discount' => 0,
            'paid_amount' => 0,
        ]);

        $this->assertSame('service', $document->refresh()->process_mode);
    }
}
