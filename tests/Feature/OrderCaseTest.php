<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\OrderCaseService;
use App\Support\StoresDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_employee_can_create_order_and_open_tracking_invoice_and_qr(): void
    {
        $filial = $this->filial('ORD');
        $employee = $this->user('employee', $filial->id);
        $client = ClientsModel::create([
            'name' => 'Order Client',
            'phone_number' => '901234580',
            'filial_id' => $filial->id,
        ]);

        $this->actingAs($employee)
            ->post(route('orders.store'), [
                'client_id' => $client->id,
                'title' => 'Full case',
                'priority' => 'high',
                'source' => 'test',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'received',
        ]);
        $this->assertDatabaseHas('order_notifications', [
            'order_id' => $order->id,
            'event' => 'order_created',
        ]);

        $this->actingAs($employee)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_code);

        $this->actingAs($employee)
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('INVOICE');

        $this->get(route('orders.track', ['trackingToken' => $order->tracking_token]))
            ->assertOk()
            ->assertSee($order->order_code);

        $this->actingAs($employee)
            ->get(route('orders.qr', $order))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_document_is_attached_to_order_with_price_line_checklist_and_payment(): void
    {
        $filial = $this->filial('FIN');
        $employee = $this->user('employee', $filial->id);
        $this->actingAs($employee);
        $client = ClientsModel::create([
            'name' => 'Financial Client',
            'phone_number' => '901234581',
            'filial_id' => $filial->id,
        ]);
        $service = ServicesModel::create([
            'name' => 'Financial Service',
            'price' => 100,
            'deadline' => 2,
        ]);

        $store = new class
        {
            use StoresDocuments;

            public function store(array $payload)
            {
                return $this->storeDocumentFromPayload($payload);
            }
        };

        $document = $store->store([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'discount' => 0,
            'paid_amount' => 0,
        ]);
        $order = $document->order()->with(['priceLines', 'checklists'])->firstOrFail();

        $this->assertSame(100.0, (float) $order->total_amount);
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertCount(1, $order->priceLines);
        $this->assertCount(1, $order->checklists);

        app(OrderCaseService::class)->recordPayment($order, 100, 'cash', $employee, $document->id);

        $order->refresh();
        $document->refresh();
        $this->assertSame(100.0, (float) $order->paid_amount);
        $this->assertSame(100.0, (float) $document->paid_amount);
        $this->assertSame('paid', $order->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'document_id' => $document->id,
            'amount' => 100,
        ]);
    }

    public function test_order_payment_cannot_overpay_even_when_balance_is_zero(): void
    {
        $filial = $this->filial('PAY');
        $employee = $this->user('employee', $filial->id);
        $client = ClientsModel::create([
            'name' => 'Payment Client',
            'phone_number' => '901234582',
            'filial_id' => $filial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $employee->id);

        $this->expectException(ValidationException::class);

        app(OrderCaseService::class)->recordPayment($order, 1, 'cash', $employee);
    }

    public function test_employee_cannot_view_another_filials_order(): void
    {
        $firstFilial = $this->filial('ONE');
        $secondFilial = $this->filial('TWO');
        $firstEmployee = $this->user('employee', $firstFilial->id);
        $secondEmployee = $this->user('employee', $secondFilial->id);
        $client = ClientsModel::create([
            'name' => 'Scoped Order Client',
            'phone_number' => '901234583',
            'filial_id' => $secondFilial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $secondFilial->id, $secondEmployee->id);

        $this->actingAs($firstEmployee)
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_order_cost_and_customer_notification_are_visible_in_case(): void
    {
        $filial = $this->filial('OPS');
        $employee = $this->user('employee', $filial->id);
        $client = ClientsModel::create([
            'name' => 'Operations Client',
            'phone_number' => '901234584',
            'filial_id' => $filial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $employee->id);

        $this->actingAs($employee)
            ->post(route('orders.costs.store', $order), [
                'category' => 'Courier fuel',
                'amount' => 25,
            ])
            ->assertRedirect();

        $this->actingAs($employee)
            ->post(route('orders.notifications.store', $order), [
                'channel' => 'sms',
                'message' => 'Order tayyorlanmoqda.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('order_costs', ['order_id' => $order->id, 'amount' => 25]);
        $this->assertDatabaseHas('order_notifications', [
            'order_id' => $order->id,
            'channel' => 'sms',
            'status' => 'queued',
        ]);
        $this->assertSame(-25.0, (float) $order->refresh()->profit_amount);
    }

    private function filial(string $code): FilialModel
    {
        return FilialModel::create(['name' => $code . ' Filial', 'code' => $code]);
    }

    private function user(string $role, int $filialId): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);

        return $user;
    }
}
