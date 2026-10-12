<?php

namespace Tests\Feature;

use App\Models\FilialModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseAllocationWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_expense_allocation_returns_422_and_rolls_back_the_receipt(): void
    {
        Storage::fake('private');
        Role::findOrCreate('admin_manager', 'web');
        $actor = User::factory()->create();
        $actor->assignRole('admin_manager');
        $branch = FilialModel::create(['name' => 'Expense test', 'code' => 'EXP']);
        $this->actingAs($actor)->postJson(route('admin.expense.store'), [
            'filial_id' => $branch->id,
            'amount' => 10000,
            'description' => 'Allocation test',
            'allocations' => [['filial_id' => $branch->id, 'amount' => 5000]],
            'receipt' => UploadedFile::fake()->image('receipt.png'),
        ])->assertUnprocessable()->assertJsonValidationErrors('allocations');
        $this->assertDatabaseCount('expense_admin', 0);
        $this->assertSame([], Storage::disk('private')->allFiles('expense-receipts'));
    }
}
