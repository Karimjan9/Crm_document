<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\PriceTariff;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\FilialPnlService;
use App\Services\OrderCaseService;
use App\Services\PaymentLedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilialPnlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_filial_pnl_calculates_finance_operations_and_deadline_metrics(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-13 12:00:00'));

        $filial = FilialModel::create([
            'name' => 'P&L filial',
            'code' => 'PNL',
            'phone' => '+998901112233',
            'address' => 'Toshkent',
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'working_days' => [1, 2, 3, 4, 5, 6],
            'holiday_dates' => [],
            'monthly_expense' => 500,
            'target_amount' => 2000,
            'commission_percent' => 3.5,
            'daily_capacity' => 10,
        ]);
        $admin = $this->user('admin_filial', $filial->id);
        $client = ClientsModel::create([
            'name' => 'P&L client',
            'phone_number' => '998901234567',
            'filial_id' => $filial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $admin->id, [
            'responsible_user_id' => $admin->id,
        ]);
        $order->priceLines()->create([
            'line_type' => 'service',
            'name' => 'P&L service',
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
            'cost_amount' => 200,
        ]);
        app(OrderCaseService::class)->recalculate($order);
        app(OrderCaseService::class)->recordPayment($order->fresh(), 400, 'cash', $admin);

        $service = ServicesModel::create([
            'name' => 'P&L document service',
            'price' => 1000,
            'deadline' => 1,
        ]);

        ExpenseAdminModel::create([
            'user_id' => $admin->id,
            'filial_id' => $filial->id,
            'amount' => 100,
            'description' => 'Internet',
            'payment_method' => 'cash',
            'approval_status' => 'approved',
            'expense_type' => 'branch',
            'expense_date' => '2026-08-13',
        ]);

        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'service_id' => $service->id,
            'filial_id' => $filial->id,
            'user_id' => $admin->id,
            'assigned_to_id' => $admin->id,
            'document_code' => 'PNL-DOC-1',
            'status_doc' => 'in_processing',
            'deadline_time' => 0,
            'final_price' => 1000,
            'paid_amount' => 400,
        ]);
        $document->forceFill(['created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)])->save();

        PriceTariff::create([
            'filial_id' => $filial->id,
            'line_type' => 'base_service',
            'variant' => 'express',
            'name' => 'PNL filial express',
            'price' => 1200,
            'cost_amount' => 250,
            'currency' => 'UZS',
            'effective_from' => Carbon::now()->subDay(),
            'is_active' => true,
        ]);

        $report = app(FilialPnlService::class)->build($filial->fresh(), Carbon::now()->startOfMonth(), Carbon::now()->endOfDay());

        $this->assertSame(1000.0, $report['kpis']['revenue']);
        $this->assertSame(400.0, $report['kpis']['paid']);
        $this->assertSame(600.0, $report['kpis']['debt']);
        $this->assertSame(300.0, $report['kpis']['expense']);
        $this->assertSame(700.0, $report['kpis']['gross_profit']);
        $this->assertSame(1, $report['kpis']['order_count']);
        $this->assertSame(1000.0, $report['kpis']['average_order_value']);
        $this->assertSame(1, $report['kpis']['deadline_breach_count']);
        $this->assertSame(1, $report['kpis']['deadline_breach_documents']);
        $this->assertSame(1, $report['employees'][0]['order_count']);
        $this->assertSame(1, count($report['special_prices']));
        $this->assertSame(500.0, $report['profile']['monthly_expense']);
    }

    public function test_filial_pnl_endpoint_isolated_for_filial_admin(): void
    {
        $first = FilialModel::create(['name' => 'First P&L', 'code' => 'P1']);
        $second = FilialModel::create(['name' => 'Second P&L', 'code' => 'P2']);
        $admin = $this->user('admin_filial', $first->id);

        $response = $this->actingAs($admin)->getJson(route('finance.filials.index'));

        $response->assertOk()
            ->assertJsonPath('branches.0.profile.id', $first->id)
            ->assertJsonMissing(['name' => 'Second P&L']);

        $this->actingAs($admin)
            ->getJson(route('finance.filials.show', $second))
            ->assertForbidden();
    }

    public function test_filial_manager_assignment_cannot_cross_branch_boundaries(): void
    {
        $first = FilialModel::create(['name' => 'Manager First', 'code' => 'MF1']);
        $second = FilialModel::create(['name' => 'Manager Second', 'code' => 'MF2']);
        $branchAdmin = $this->user('admin_filial', $first->id);
        $manager = User::factory()->create(['filial_id' => null]);
        Role::findOrCreate('admin_manager', 'web');
        $manager->assignRole('admin_manager');

        $this->actingAs($manager)
            ->post(route('admin.filial.store'), [
                'name' => 'Invalid New Branch',
                'code' => 'MF3',
                'manager_id' => $branchAdmin->id,
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertDatabaseMissing('filial', ['code' => 'MF3']);

        $this->actingAs($manager)
            ->put(route('admin.filial.update', $second), [
                'name' => 'Manager Second Updated',
                'code' => 'MF2',
                'manager_id' => $branchAdmin->id,
            ])
            ->assertSessionHasErrors('manager_id');

        $this->assertSame('Manager Second', $second->refresh()->name);
    }

    public function test_employee_expense_statistics_ignores_malformed_month_filter(): void
    {
        $filial = FilialModel::create(['name' => 'Expense Filter', 'code' => 'EF1']);
        $employee = $this->user('employee', $filial->id);

        $this->actingAs($employee)
            ->get('/employee/expense_admin/statistika?month_year=not-a-month')
            ->assertOk();
    }

    public function test_filial_pnl_keeps_full_refunds_in_refund_metrics(): void
    {
        $filial = FilialModel::create(['name' => 'Refund P&L', 'code' => 'RPF']);
        $admin = $this->user('admin_filial', $filial->id);
        $client = ClientsModel::create([
            'name' => 'Refund client',
            'phone_number' => '998901234578',
            'filial_id' => $filial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $admin->id);
        $order->priceLines()->create([
            'line_type' => 'service',
            'name' => 'Refund service',
            'quantity' => 1,
            'unit_price' => 500,
            'total_price' => 500,
            'cost_amount' => 0,
        ]);
        $order = app(OrderCaseService::class)->recalculate($order);
        $payment = app(OrderCaseService::class)->recordPayment($order, 500, 'cash', $admin);

        app(PaymentLedgerService::class)->refund($payment, 500, $admin, 'To‘liq qaytarildi');

        $report = app(FilialPnlService::class)->build($filial->fresh(), today()->startOfDay(), today()->endOfDay());

        $this->assertSame(0.0, $report['kpis']['paid']);
        $this->assertSame(500.0, $report['kpis']['refunds']);
        $this->assertSame(500.0, $report['kpis']['debt']);
    }

    private function user(string $role, int $filialId): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);

        return $user;
    }
}
