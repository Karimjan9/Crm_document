<?php

namespace Tests\Feature;

use App\Models\ApostilStatikModel;
use App\Models\ClientsModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\ServicesModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CrudCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_global_expense_crud_persists_changes(): void
    {
        $actor = $this->userWithRole('super_admin');
        $filial = $this->filial('EXP');

        $this->actingAs($actor)
            ->post(route('superadmin.expense.store'), [
                'filial_id' => $filial->id,
                'user_id' => $actor->id,
                'amount' => 12500,
                'description' => 'Initial expense',
            ])
            ->assertRedirect(route('superadmin.expense.index'));

        $expense = ExpenseAdminModel::query()->latest('id')->firstOrFail();
        $this->assertSame(12500.0, (float) $expense->amount);

        $this->actingAs($actor)
            ->put(route('superadmin.expense.update', $expense), [
                'filial_id' => $filial->id,
                'user_id' => $actor->id,
                'amount' => 15000,
                'description' => 'Updated expense',
            ])
            ->assertRedirect(route('superadmin.expense.index'));

        $this->assertDatabaseHas('expense_admin', [
            'id' => $expense->id,
            'amount' => 15000,
            'description' => 'Updated expense',
        ]);

        $this->actingAs($actor)
            ->delete(route('superadmin.expense.destroy', $expense))
            ->assertRedirect(route('superadmin.expense.index'));

        $this->assertDatabaseMissing('expense_admin', ['id' => $expense->id]);
    }

    public function test_filial_expense_update_and_destroy_are_scoped(): void
    {
        $filial = $this->filial('FEX');
        $otherFilial = $this->filial('OTH');
        $actor = $this->userWithRole('admin_filial', $filial->id);

        $expense = ExpenseAdminModel::create([
            'user_id' => $actor->id,
            'filial_id' => $filial->id,
            'amount' => 10000,
            'description' => 'Filial expense',
        ]);
        $otherExpense = ExpenseAdminModel::create([
            'user_id' => $actor->id,
            'filial_id' => $otherFilial->id,
            'amount' => 11000,
            'description' => 'Other filial expense',
        ]);

        $this->actingAs($actor)
            ->put(route('admin_filial.expense_admin.update', $expense), [
                'amount' => 12000,
                'description' => 'Changed in branch',
            ])
            ->assertRedirect(route('admin_filial.expense_admin.index'));

        $this->assertDatabaseHas('expense_admin', [
            'id' => $expense->id,
            'amount' => 12000,
            'description' => 'Changed in branch',
        ]);

        $this->actingAs($actor)
            ->delete(route('admin_filial.expense_admin.destroy', $otherExpense))
            ->assertNotFound();

        $this->actingAs($actor)
            ->delete(route('admin_filial.expense_admin.destroy', $expense))
            ->assertRedirect(route('admin_filial.expense_admin.index'));

        $this->assertDatabaseHas('expense_admin', ['id' => $otherExpense->id]);
        $this->assertDatabaseMissing('expense_admin', ['id' => $expense->id]);
    }

    public function test_static_apostil_crud_persists_changes(): void
    {
        $actor = $this->userWithRole('super_admin');

        $this->actingAs($actor)
            ->post(route('superadmin.apostil.store'), [
                'name' => 'Standard apostil',
                'group_id' => 1,
                'price' => 25000,
                'days' => 3,
            ])
            ->assertRedirect(route('superadmin.apostil.index'));

        $apostil = ApostilStatikModel::query()->latest('id')->firstOrFail();

        $this->actingAs($actor)
            ->put(route('superadmin.apostil.update', $apostil), [
                'name' => 'Updated apostil',
                'group_id' => 1,
                'price' => 30000,
                'days' => 4,
            ])
            ->assertRedirect(route('superadmin.apostil.index'));

        $this->assertDatabaseHas('apostil_static', [
            'id' => $apostil->id,
            'name' => 'Updated apostil',
            'price' => 30000,
            'days' => 4,
        ]);

        $this->actingAs($actor)
            ->delete(route('superadmin.apostil.destroy', $apostil))
            ->assertRedirect(route('superadmin.apostil.index'));

        $this->assertDatabaseMissing('apostil_static', ['id' => $apostil->id]);
    }

    public function test_admin_document_crud_persists_changes(): void
    {
        $actor = $this->userWithRole('super_admin');
        $filial = $this->filial('DOC');
        $client = ClientsModel::create([
            'name' => 'CRUD Client',
            'phone_number' => '901234571',
        ]);
        $service = ServicesModel::create([
            'name' => 'CRUD Service',
            'description' => 'Document CRUD service',
            'price' => 100000,
            'deadline' => 3,
        ]);
        $documentType = DocumentTypeModel::create([
            'name' => 'CRUD Document',
            'description' => 'Document CRUD type',
        ]);

        $payload = [
            'client_id' => $client->id,
            'service_id' => $service->id,
            'document_type_id' => $documentType->id,
            'filial_id' => $filial->id,
            'process_mode' => 'service',
            'discount' => 0,
            'paid_amount' => 0,
            'description' => 'Created document',
        ];

        $this->actingAs($actor)
            ->post(route('superadmin.document.store'), $payload)
            ->assertRedirect(route('superadmin.document.index'));

        $document = DocumentsModel::query()->latest('id')->firstOrFail();

        $this->actingAs($actor)
            ->get(route('superadmin.document.show', $document))
            ->assertOk();

        $this->actingAs($actor)
            ->put(route('superadmin.document.update', $document), [
                ...$payload,
                'final_price' => 100000,
                'description' => 'Updated document',
            ])
            ->assertRedirect(route('superadmin.document.index'));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'description' => 'Updated document',
        ]);

        $this->actingAs($actor)
            ->delete(route('superadmin.document.destroy', $document))
            ->assertRedirect(route('superadmin.document.index'));

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    private function userWithRole(string $role, ?int $filialId = null): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);

        return $user;
    }

    private function filial(string $code): FilialModel
    {
        return FilialModel::create([
            'name' => $code . ' Filial',
            'code' => $code,
            'description' => 'CRUD test filial',
        ]);
    }
}
