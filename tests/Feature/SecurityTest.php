<?php

namespace Tests\Feature;

use App\Http\Middleware\RoleMiddleware;
use App\Models\ClientsModel;
use App\Models\DocumentFileModel;
use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Holiday;
use App\Models\ServicesModel;
use App\Models\ServiceAddonModel;
use App\Models\User;
use App\Support\StoresDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
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

    public function test_admin_manager_cannot_access_super_admin_routes(): void
    {
        Role::findOrCreate('admin_manager', 'web');

        $manager = User::factory()->create();
        $manager->assignRole('admin_manager');

        $this->actingAs($manager)
            ->get(route('superadmin.index'))
            ->assertNotFound();
    }

    public function test_admin_manager_cannot_assign_privileged_roles(): void
    {
        Role::findOrCreate('admin_manager', 'web');
        $filial = FilialModel::create([
            'name' => 'Security Filial',
            'code' => 'SEC',
            'description' => 'Security test filial',
        ]);

        $manager = User::factory()->create(['filial_id' => $filial->id]);
        $manager->assignRole('admin_manager');

        $this->actingAs($manager)
            ->post(route('admin.store'), [
                'name' => 'Escalation Attempt',
                'login' => 'escalation-attempt',
                'phone' => '901234567',
                'password' => 'TestPassword-2026!',
                'password_confirmation' => 'TestPassword-2026!',
                'role' => 'super_admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['login' => 'escalation-attempt']);
    }

    public function test_filial_admin_cannot_edit_another_filials_document(): void
    {
        Role::findOrCreate('admin_filial', 'web');
        Role::findOrCreate('employee', 'web');

        $firstFilial = FilialModel::create([
            'name' => 'First Filial',
            'code' => 'FST',
            'description' => 'First test filial',
        ]);
        $secondFilial = FilialModel::create([
            'name' => 'Second Filial',
            'code' => 'SND',
            'description' => 'Second test filial',
        ]);

        $filialAdmin = User::factory()->create(['filial_id' => $firstFilial->id]);
        $filialAdmin->assignRole('admin_filial');
        $owner = User::factory()->create(['filial_id' => $secondFilial->id]);
        $owner->assignRole('employee');

        $client = ClientsModel::create([
            'name' => 'Scoped Client',
            'phone_number' => '901234568',
        ]);
        $service = ServicesModel::create([
            'name' => 'Scoped Service',
            'price' => 100,
            'deadline' => 1,
        ]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'service_price' => 100,
            'addons_total_price' => 0,
            'final_price' => 100,
            'paid_amount' => 0,
            'discount' => 0,
            'deadline_time' => 1,
            'user_id' => $owner->id,
            'filial_id' => $secondFilial->id,
            'document_code' => 'SCOPE-TEST-1',
        ]);

        $this->actingAs($filialAdmin)
            ->get(route('admin_filial.document.edit', $document))
            ->assertForbidden();
    }

    public function test_api_client_endpoints_are_scoped_to_the_users_filial(): void
    {
        Role::findOrCreate('employee', 'web');

        $firstFilial = FilialModel::create([
            'name' => 'Client First Filial',
            'code' => 'CF1',
        ]);
        $secondFilial = FilialModel::create([
            'name' => 'Client Second Filial',
            'code' => 'CF2',
        ]);

        $employee = User::factory()->create(['filial_id' => $firstFilial->id]);
        $employee->assignRole('employee');

        $firstClient = ClientsModel::create([
            'name' => 'Visible Client',
            'phone_number' => '901234571',
            'filial_id' => $firstFilial->id,
        ]);
        $secondClient = ClientsModel::create([
            'name' => 'Hidden Client',
            'phone_number' => '901234572',
            'filial_id' => $secondFilial->id,
        ]);

        Sanctum::actingAs($employee, ['clients:read', 'clients:write']);

        $this->getJson('/api/v1/clients')
            ->assertOk()
            ->assertJsonFragment(['id' => $firstClient->id])
            ->assertJsonMissing(['id' => $secondClient->id]);

        $this->getJson('/api/v1/clients/'.$secondClient->id)->assertForbidden();
        $this->putJson('/api/v1/clients/'.$secondClient->id, [
            'name' => 'Attempted change',
            'phone_number' => '901234573',
        ])->assertForbidden();
        $this->deleteJson('/api/v1/clients/'.$secondClient->id)->assertForbidden();
    }

    public function test_complete_document_state_change_is_not_available_over_get(): void
    {
        $this->get('/employee/document/complete/1')->assertMethodNotAllowed();
        $this->get('/admin_filial/document/complete/1')->assertMethodNotAllowed();
    }

    public function test_document_file_requires_document_authorization(): void
    {
        Storage::fake('private');
        Role::findOrCreate('employee', 'web');

        $filial = FilialModel::create([
            'name' => 'Private Filial',
            'code' => 'PRI',
            'description' => 'Private file test filial',
        ]);
        $owner = User::factory()->create(['filial_id' => $filial->id]);
        $owner->assignRole('employee');
        $otherUser = User::factory()->create(['filial_id' => $filial->id]);
        $otherUser->assignRole('employee');

        $client = ClientsModel::create([
            'name' => 'Private Client',
            'phone_number' => '901234569',
        ]);
        $service = ServicesModel::create([
            'name' => 'Private Service',
            'price' => 100,
            'deadline' => 1,
        ]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'service_price' => 100,
            'final_price' => 100,
            'paid_amount' => 0,
            'discount' => 0,
            'deadline_time' => 1,
            'user_id' => $owner->id,
            'filial_id' => $filial->id,
            'document_code' => 'PRIVATE-TEST-1',
        ]);
        $path = 'documents/private-test.pdf';
        Storage::disk('private')->put($path, 'private document');
        $file = DocumentFileModel::create([
            'document_id' => $document->id,
            'original_name' => 'private-test.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 16,
        ]);

        $this->actingAs($owner)
            ->get(route('document-files.show', $file))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($otherUser)
            ->get(route('document-files.show', $file))
            ->assertForbidden();
    }

    public function test_expense_receipt_is_not_visible_to_another_employee_in_same_filial(): void
    {
        Storage::fake('private');
        Role::findOrCreate('employee', 'web');

        $filial = FilialModel::create(['name' => 'Receipt Filial', 'code' => 'RCP']);
        $owner = User::factory()->create(['filial_id' => $filial->id]);
        $owner->assignRole('employee');
        $other = User::factory()->create(['filial_id' => $filial->id]);
        $other->assignRole('employee');
        $expense = ExpenseAdminModel::create([
            'user_id' => $owner->id,
            'filial_id' => $filial->id,
            'amount' => 1000,
            'description' => 'Private receipt',
            'receipt_path' => 'expenses/private-receipt.pdf',
            'receipt_original_name' => 'private-receipt.pdf',
        ]);
        Storage::disk('private')->put($expense->receipt_path, 'private receipt');

        $this->actingAs($other)
            ->get(route('expenses.receipt', $expense))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('expenses.receipt', $expense))
            ->assertDownload('private-receipt.pdf');
    }

    public function test_addon_must_belong_to_the_selected_service(): void
    {
        $filial = FilialModel::create([
            'name' => 'Addon Filial',
            'code' => 'ADD',
            'description' => 'Addon scope test filial',
        ]);
        $user = User::factory()->create(['filial_id' => $filial->id]);
        $this->actingAs($user);

        $client = ClientsModel::create([
            'name' => 'Addon Client',
            'phone_number' => '901234570',
        ]);
        $selectedService = ServicesModel::create([
            'name' => 'Selected Service',
            'price' => 100,
            'deadline' => 1,
        ]);
        $otherService = ServicesModel::create([
            'name' => 'Other Service',
            'price' => 200,
            'deadline' => 2,
        ]);
        $otherServiceAddon = ServiceAddonModel::create([
            'service_id' => $otherService->id,
            'name' => 'Other service addon',
            'price' => 50,
            'deadline' => 1,
        ]);

        $documentStore = new class
        {
            use StoresDocuments;

            public function store(array $payload)
            {
                return $this->storeDocumentFromPayload($payload);
            }
        };

        $this->expectException(ValidationException::class);

        $documentStore->store([
            'client_id' => $client->id,
            'service_id' => $selectedService->id,
            'process_mode' => 'service',
            'selected_addons' => [
                ['id' => $otherServiceAddon->id, 'sourceType' => 'service'],
            ],
            'discount' => 0,
            'paid_amount' => 0,
        ]);
    }
}
