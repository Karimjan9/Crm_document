<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\IntakeSession;
use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\OrderPaymentLink;
use App\Models\ServicesModel;
use App\Models\ServiceAddonModel;
use App\Models\User;
use App\Services\MarginLeakDetectorService;
use App\Services\CashReconciliationService;
use App\Services\NotificationDeliveryService;
use App\Services\OrderCaseService;
use App\Services\OrderPaymentLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WorkflowIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_smart_intake_returns_service_price_deadline_files_and_addon_recommendation(): void
    {
        $service = ServicesModel::create([
            'name' => 'Smart translation',
            'description' => 'Translation service',
            'price' => 100000,
            'deadline' => 3,
        ]);
        $addon = ServiceAddonModel::create([
            'service_id' => $service->id,
            'name' => 'Urgent review',
            'price' => 25000,
            'deadline' => 1,
        ]);

        $this->postJson(route('intake.analyze'), [
            'service_id' => $service->id,
            'addon_ids' => [$addon->id],
            'needs_original' => true,
        ])->assertOk()
            ->assertJsonPath('data.estimated_price', 125000)
            ->assertJsonPath('data.estimated_deadline_days', 4)
            ->assertJsonFragment(['name' => 'Urgent review'])
            ->assertJsonFragment(['0' => 'Original hujjat']);
    }

    public function test_customer_portal_has_payment_link_without_exposing_client_identity_and_accepts_support(): void
    {
        $filial = FilialModel::create(['name' => 'Portal filial', 'code' => 'POR']);
        $client = ClientsModel::create([
            'name' => 'Private Customer Name',
            'phone_number' => '901234599',
            'email' => 'private@example.test',
            'filial_id' => $filial->id,
        ]);
        $user = User::factory()->create(['filial_id' => $filial->id]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $user->id);
        $order->priceLines()->create([
            'line_type' => 'service',
            'name' => 'Portal service',
            'quantity' => 1,
            'unit_price' => 50000,
            'total_price' => 50000,
        ]);
        app(OrderCaseService::class)->recalculate($order);
        app(OrderPaymentLinkService::class)->ensure($order);

        $this->get(route('orders.portal', $order->tracking_token))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Mijoz kabineti')
            ->assertDontSee('Private Customer Name')
            ->assertDontSee('901234599');

        $this->get(route('orders.portal.receipt', $order->tracking_token))
            ->assertOk()
            ->assertSee('QR receipt')
            ->assertDontSee('Private Customer Name');

        $this->assertDatabaseHas('order_payment_links', [
            'order_id' => $order->id,
            'status' => 'pending',
            'amount' => 50000,
        ]);

        $this->post(route('orders.portal.support', $order->tracking_token), [
            'subject' => 'Savol',
            'message' => 'Buyurtmam haqida savolim bor.',
        ])->assertRedirect(route('orders.portal', $order->tracking_token));

        $this->assertDatabaseHas('order_support_tickets', [
            'order_id' => $order->id,
            'message' => 'Buyurtmam haqida savolim bor.',
        ]);
    }

    public function test_repeat_order_reprices_catalog_and_sets_new_deadline(): void
    {
        $filial = FilialModel::create(['name' => 'Repeat filial', 'code' => 'RPT']);
        $client = ClientsModel::create(['name' => 'Repeat client', 'phone_number' => '901234595', 'filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Repeat service', 'price' => 100, 'deadline' => 2]);
        $addon = ServiceAddonModel::create(['service_id' => $service->id, 'name' => 'Repeat addon', 'price' => 20, 'deadline' => 1]);
        $order = app(OrderCaseService::class)->createForClient($client, $filial->id);
        $order->priceLines()->createMany([
            ['line_type' => 'service', 'source_id' => $service->id, 'name' => $service->name, 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100],
            ['line_type' => 'addon', 'source_id' => $addon->id, 'name' => $addon->name, 'quantity' => 1, 'unit_price' => 20, 'total_price' => 20],
        ]);
        $order->checklists()->create(['title' => 'Passport checklist', 'is_required' => true, 'sort_order' => 1]);
        app(OrderCaseService::class)->recalculate($order);
        $service->update(['price' => 140]);
        $addon->update(['price' => 35]);

        $deadline = now()->addDays(5)->toDateString();
        $this->post(route('orders.portal.repeat', $order->tracking_token), ['promised_at' => $deadline])
            ->assertRedirect();
        $repeat = Order::query()->where('repeat_of_order_id', $order->id)->firstOrFail();

        $this->assertSame('140.00', (string) $repeat->priceLines()->where('line_type', 'service')->value('total_price'));
        $this->assertSame('35.00', (string) $repeat->priceLines()->where('line_type', 'addon')->value('total_price'));
        $this->assertSame($deadline, $repeat->promised_at->toDateString());
        $this->assertDatabaseHas('order_checklists', ['order_id' => $repeat->id, 'title' => 'Passport checklist']);
    }

    public function test_document_custody_records_signed_handoff_chain(): void
    {
        Role::findOrCreate('employee', 'web');
        $filial = FilialModel::create(['name' => 'Custody filial', 'code' => 'CUS']);
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $client = ClientsModel::create(['name' => 'Custody client', 'phone_number' => '901234598', 'filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Custody service', 'price' => 100, 'deadline' => 1]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'service_price' => 100,
            'addons_total_price' => 0,
            'final_price' => 100,
            'paid_amount' => 0,
            'discount' => 0,
            'deadline_time' => 1,
            'user_id' => $employee->id,
            'filial_id' => $filial->id,
            'document_code' => 'CUS-001',
        ]);

        $this->actingAs($employee)
            ->postJson(route('document-custody.store', $document), [
                'event_type' => 'received',
                'to_user_id' => $employee->id,
                'notes' => 'Original qabul qilindi',
            ])
            ->assertCreated()
            ->assertJsonPath('data.event_type', 'received')
            ->assertJsonPath('chain_valid', true);

        $this->assertDatabaseHas('document_custody_events', [
            'document_id' => $document->id,
            'event_type' => 'received',
            'to_user_id' => $employee->id,
        ]);
        $this->assertNotEmpty($document->custodyEvents()->firstOrFail()->signature);
    }

    public function test_ocr_requires_human_approval_before_use(): void
    {
        Role::findOrCreate('employee', 'web');
        $filial = FilialModel::create(['name' => 'OCR filial', 'code' => 'OCR']);
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $session = IntakeSession::create(['token' => str_repeat('o', 80), 'filial_id' => $filial->id, 'status' => 'started']);

        $upload = $this->post(route('intake.ocr.upload', $session->token), [
            'file' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf'),
        ]);
        $upload->assertRedirect(route('intake.start', ['session' => $session->token]));
        $this->assertDatabaseHas('intake_ocr_documents', [
            'intake_session_id' => $session->id,
            'status' => 'pending_review',
        ]);

        $ocr = $session->ocrDocuments()->firstOrFail();
        $this->actingAs($employee)
            ->postJson(route('intake.ocr.approve', [$session->token, $ocr]), [
                'extracted_data' => ['document_type' => 'passport'],
                'approval_note' => 'Inson tekshirdi',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_notification_adapter_delivers_telegram_message(): void
    {
        Http::fake(['https://api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 42],
        ])]);
        config()->set('services.notifications.telegram.bot_token', 'test-token');

        $filial = FilialModel::create(['name' => 'Notification filial', 'code' => 'NOT']);
        $client = ClientsModel::create([
            'name' => 'Notification client',
            'phone_number' => '901234596',
            'telegram_chat_id' => '123456',
            'filial_id' => $filial->id,
        ]);
        $order = Order::create([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'order_code' => 'ORD-NOT-001',
            'tracking_token' => str_repeat('n', 64),
            'status' => 'received',
        ]);
        $notification = $order->notifications()->create([
            'event' => 'order_received',
            'channel' => 'telegram',
            'recipient' => '123456',
            'message' => 'Test notification',
            'status' => 'queued',
        ]);

        $result = app(NotificationDeliveryService::class)->deliver($notification);

        $this->assertSame('sent', $result['status']);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/bottest-token/sendMessage')
            && $request['chat_id'] === '123456');
    }

    public function test_cash_variance_becomes_margin_leak(): void
    {
        $filial = FilialModel::create(['name' => 'Cash filial', 'code' => 'CSH']);
        app(CashReconciliationService::class)->record($filial->id, today(), 80000, 100000);

        $leaks = app(MarginLeakDetectorService::class)->scan(today()->startOfDay(), today()->endOfDay(), $filial->id);
        $this->assertTrue(collect($leaks)->contains(fn ($leak) => $leak->leak_type === 'cash_variance'));
    }

    public function test_margin_leak_detector_flags_large_discount(): void
    {
        $filial = FilialModel::create(['name' => 'Margin filial', 'code' => 'MAR']);
        $client = ClientsModel::create(['name' => 'Margin client', 'phone_number' => '901234597', 'filial_id' => $filial->id]);
        $order = Order::create([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'order_code' => 'ORD-MAR-001',
            'tracking_token' => str_repeat('m', 64),
            'status' => 'received',
            'subtotal_amount' => 100000,
            'discount_amount' => 30000,
            'total_amount' => 70000,
            'paid_amount' => 0,
            'cost_amount' => 0,
            'profit_amount' => 70000,
        ]);

        $leaks = app(MarginLeakDetectorService::class)->scan(today()->startOfMonth(), today()->endOfDay());

        $this->assertTrue(collect($leaks)->contains(fn ($leak) => $leak->leak_type === 'large_discount' && $leak->order_id === $order->id));
        $this->assertDatabaseHas('margin_leaks', ['order_id' => $order->id, 'leak_type' => 'large_discount']);
    }

    public function test_margin_leak_detector_flags_addon_price_mismatch(): void
    {
        $filial = FilialModel::create(['name' => 'Addon margin filial', 'code' => 'AMD']);
        $client = ClientsModel::create(['name' => 'Addon margin client', 'phone_number' => '901234594', 'filial_id' => $filial->id]);
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Addon service', 'price' => 100, 'deadline' => 1]);
        $addon = ServiceAddonModel::create(['service_id' => $service->id, 'name' => 'Paid addon', 'price' => 100, 'deadline' => 1]);
        $order = Order::create([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'order_code' => 'ORD-AMD-001',
            'tracking_token' => str_repeat('a', 64),
            'status' => 'received',
            'subtotal_amount' => 150,
            'total_amount' => 150,
        ]);
        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'service_id' => $service->id,
            'service_price' => 100,
            'final_price' => 150,
            'deadline_time' => 1,
            'user_id' => $employee->id,
            'filial_id' => $filial->id,
        ]);
        $order->priceLines()->create([
            'document_id' => $document->id,
            'line_type' => 'addon',
            'source_id' => $addon->id,
            'name' => $addon->name,
            'quantity' => 1,
            'unit_price' => 50,
            'total_price' => 50,
        ]);

        $leaks = app(MarginLeakDetectorService::class)->scan(today()->startOfMonth(), today()->endOfDay());

        $this->assertTrue(collect($leaks)->contains(fn ($leak) => $leak->leak_type === 'addon_price_mismatch'));
    }
}
