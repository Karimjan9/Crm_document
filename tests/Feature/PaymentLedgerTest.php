<?php

namespace Tests\Feature;

use App\Models\CashSession;
use App\Models\ClientsModel;
use App\Models\FilialModel;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentRefund;
use App\Models\PaymentsModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\OrderCaseService;
use App\Services\PaymentLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_payment_has_receipt_invoice_and_partial_refund_updates_order_balance(): void
    {
        $filial = $this->filial('LED');
        $admin = $this->user('admin_filial', $filial->id);
        $order = $this->orderWithPrice($filial, $admin, 100000);

        $payment = app(OrderCaseService::class)->recordPayment($order, 100000, 'cash', $admin);
        $invoice = app(PaymentLedgerService::class)->issueInvoice($order, $admin);

        $this->assertNotNull($payment->receipt_number);
        $this->assertSame('confirmed', $payment->status);
        $this->assertSame('INV-' . $order->order_code, $invoice->invoice_number);
        $this->assertDatabaseHas('payment_status_histories', [
            'payment_id' => $payment->id,
            'to_status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('invoice_lines', ['invoice_id' => $invoice->id]);

        $refund = app(PaymentLedgerService::class)->refund($payment, 40000, $admin, 'Mijoz qisman qaytardi');
        app(OrderCaseService::class)->recalculate($order);

        $this->assertInstanceOf(PaymentRefund::class, $refund);
        $this->assertSame('partially_refunded', $payment->fresh()->status);
        $this->assertSame(60000.0, (float) $order->fresh()->paid_amount);
        $this->assertSame(40000.0, (float) $order->fresh()->balance_amount);
        $this->assertSame(60000.0, (float) $invoice->fresh()->paid_amount);
    }

    public function test_cancel_does_not_delete_payment_and_reopens_order_debt(): void
    {
        $filial = $this->filial('CNL');
        $admin = $this->user('admin_filial', $filial->id);
        $order = $this->orderWithPrice($filial, $admin, 50000);
        $payment = app(OrderCaseService::class)->recordPayment($order, 50000, 'card', $admin);

        app(PaymentLedgerService::class)->cancel($payment, $admin, 'Karta operatsiyasi bekor qilindi');
        app(OrderCaseService::class)->recalculate($order);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'cancelled']);
        $this->assertSame(0.0, (float) $order->fresh()->paid_amount);
        $this->assertSame('awaiting_payment', $order->fresh()->status);
    }

    public function test_cash_session_closing_calculates_expected_cash_and_variance(): void
    {
        $filial = $this->filial('CSH');
        $admin = $this->user('admin_filial', $filial->id);
        $ledger = app(PaymentLedgerService::class);
        $session = $ledger->openCashSession($filial->id, $admin, 100000);
        $order = $this->orderWithPrice($filial, $admin, 50000);

        $payment = app(OrderCaseService::class)->recordPayment($order, 50000, 'cash', $admin);
        $closed = $ledger->closeCashSession($session, 140000, $admin, 'Kun yakuni');

        $this->assertSame($session->id, $payment->fresh()->cash_session_id);
        $this->assertSame(150000.0, (float) $closed->expected_cash);
        $this->assertSame(-10000.0, (float) $closed->variance);
        $this->assertSame('variance', $closed->reconciliation->status);
    }

    public function test_online_transaction_and_proof_are_saved_in_private_ledger(): void
    {
        Storage::fake('private');
        $filial = $this->filial('ONL');
        $admin = $this->user('admin_filial', $filial->id);
        $order = $this->orderWithPrice($filial, $admin, 75000);
        $proof = UploadedFile::fake()->create('click-proof.pdf', 100, 'application/pdf');

        $payment = app(OrderCaseService::class)->recordPayment(
            $order,
            75000,
            'online',
            $admin,
            null,
            [
                'online_transaction_id' => 'CLICK-123456',
                'payment_proof' => $proof,
            ],
        );

        $this->assertSame('CLICK-123456', $payment->online_transaction_id);
        $this->assertNotNull($payment->payment_proof_path);
        Storage::disk('private')->assertExists($payment->payment_proof_path);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'confirmation_status' => 'confirmed',
        ]);
    }

    public function test_filial_payment_ledger_does_not_expose_another_branch(): void
    {
        $first = $this->filial('ONE');
        $second = $this->filial('TWO');
        $firstAdmin = $this->user('admin_filial', $first->id);
        $secondAdmin = $this->user('admin_filial', $second->id);
        $firstOrder = $this->orderWithPrice($first, $firstAdmin, 10000);
        $secondOrder = $this->orderWithPrice($second, $secondAdmin, 20000);
        $firstPayment = app(OrderCaseService::class)->recordPayment($firstOrder, 10000, 'cash', $firstAdmin);
        app(OrderCaseService::class)->recordPayment($secondOrder, 20000, 'cash', $secondAdmin);

        $this->actingAs($firstAdmin)
            ->get(route('finance.ledger.index'))
            ->assertOk()
            ->assertSee($firstPayment->receipt_number)
            ->assertDontSee('RCPT-TWO');
    }

    private function orderWithPrice(FilialModel $filial, User $actor, float $amount): Order
    {
        $client = ClientsModel::create([
            'name' => 'Ledger client ' . $filial->code,
            'phone_number' => '99890' . str_pad((string) $filial->id, 5, '0', STR_PAD_LEFT),
            'filial_id' => $filial->id,
        ]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $actor->id);
        $order->priceLines()->create([
            'line_type' => 'service',
            'name' => 'Ledger service',
            'quantity' => 1,
            'unit_price' => $amount,
            'total_price' => $amount,
        ]);
        app(OrderCaseService::class)->recalculate($order);

        return $order->fresh();
    }

    private function filial(string $code): FilialModel
    {
        return FilialModel::create(['name' => $code . ' filial', 'code' => $code]);
    }

    private function user(string $role, int $filialId): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create(['filial_id' => $filialId]);
        $user->assignRole($role);

        return $user;
    }
}
