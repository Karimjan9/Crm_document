<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentTypeModel;
use App\Models\FilialModel;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateItem;
use App\Models\Partner;
use App\Models\PartnerApiKey;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\PartnerBillingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class B2bPartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_cabinet_creates_discounted_order_only_in_allowed_branch(): void
    {
        [$filial, $otherFilial, $product] = $this->catalog();
        $partner = $this->partner($filial, 'UNI', 10);
        $user = $this->partnerUser($partner);

        $this->actingAs($user)->get(route('partner.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('partner.orders.create'))->assertOk();

        $response = $this->actingAs($user)->post(route('partner.orders.store'), [
            'filial_id' => $filial->id,
            'client_name' => 'Student One',
            'client_phone' => '998901112233',
            'package_template_id' => $product->id,
            'package_variant' => 'standard',
            'partner_reference' => 'UNI-001',
            'priority' => 'normal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'partner_id' => $partner->id,
            'filial_id' => $filial->id,
            'partner_reference' => 'UNI-001',
            'partner_discount_percent' => 10,
            'total_amount' => 900000,
        ]);

        $forbidden = $this->actingAs($user)->post(route('partner.orders.store'), [
            'filial_id' => $otherFilial->id,
            'client_name' => 'Student Two',
            'client_phone' => '998901112244',
            'package_template_id' => $product->id,
        ]);
        $forbidden->assertSessionHasErrors('filial_id');
    }

    public function test_api_key_can_create_and_isolate_partner_orders(): void
    {
        [$filial, , $product] = $this->catalog();
        $partner = $this->partner($filial, 'VISA', 5);
        $other = $this->partner($filial, 'OTHER', 0);
        $token = 'b2b_' . Str::random(56);
        PartnerApiKey::create([
            'partner_id' => $partner->id,
            'name' => 'Test API',
            'token_prefix' => substr($token, 0, 16),
            'token_hash' => hash('sha256', $token),
            'abilities' => ['orders:read', 'orders:write'],
        ]);
        $otherOrder = $other->orders()->create($this->orderAttributes($filial));

        $this->withToken($token)->getJson('/api/v1/partner/catalog?filial_id=' . $filial->id)
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonMissingPath('products.0.expected_profit')
            ->assertJsonMissingPath('products.0.margin_percent');

        $response = $this->withToken($token)->postJson('/api/v1/partner/orders', [
            'filial_id' => $filial->id,
            'client_name' => 'API Client',
            'client_phone' => '998901112255',
            'package_template_id' => $product->id,
        ]);
        $response->assertCreated()->assertJsonPath('partner_reference', null);
        $this->assertDatabaseHas('orders', ['partner_id' => $partner->id, 'client_id' => ClientsModel::where('phone_number', '998901112255')->value('id')]);

        $this->withToken($token)->getJson('/api/v1/partner/orders/' . $otherOrder->id)->assertNotFound();
        $this->withToken('b2b_invalid')->getJson('/api/v1/partner/orders')->assertUnauthorized();
    }

    public function test_monthly_billing_is_idempotent_and_marks_orders_invoiced(): void
    {
        [$filial] = $this->catalog();
        $partner = $this->partner($filial, 'HR', 15);
        $order = $partner->orders()->create(array_merge($this->orderAttributes($filial), [
            'subtotal_amount' => 1000,
            'discount_amount' => 150,
            'total_amount' => 850,
            'created_at' => now()->startOfMonth()->addDay(),
        ]));

        $billing = app(PartnerBillingService::class);
        $start = CarbonImmutable::now()->startOfMonth();
        $invoice = $billing->generateForPeriod($partner, $start, $start->endOfMonth());

        $this->assertNotNull($invoice);
        $this->assertSame('invoiced', $order->fresh()->billing_status);
        $this->assertDatabaseHas('partner_invoice_lines', ['partner_invoice_id' => $invoice->id, 'order_id' => $order->id]);
        $this->assertNull($billing->generateForPeriod($partner, $start, $start->endOfMonth()));
    }

    public function test_white_label_tracking_checks_partner_code(): void
    {
        [$filial] = $this->catalog();
        $partner = $this->partner($filial, 'UNIWHITE', 0);
        $order = $partner->orders()->create($this->orderAttributes($filial));

        $this->get(route('orders.partner-track', ['partnerCode' => 'UNIWHITE', 'trackingToken' => $order->tracking_token]))
            ->assertOk()
            ->assertSee($partner->company_name);
        $this->get(route('orders.partner-track', ['partnerCode' => 'WRONG', 'trackingToken' => $order->tracking_token]))
            ->assertNotFound();
    }

    public function test_partner_operator_cannot_manage_api_keys_or_branding(): void
    {
        [$filial] = $this->catalog();
        $partner = $this->partner($filial, 'OPERATOR', 0);
        Role::firstOrCreate(['name' => 'partner_operator', 'guard_name' => 'web']);
        $operator = User::create([
            'name' => 'Partner Operator',
            'login' => 'operator-' . strtolower($partner->code),
            'phone' => '998' . random_int(100000000, 999999999),
            'password' => Hash::make('password'),
            'partner_id' => $partner->id,
        ]);
        $operator->assignRole('partner_operator');

        $this->actingAs($operator)->post(route('partner.api-keys.store'), ['name' => 'Not allowed'])->assertForbidden();
        $this->actingAs($operator)->post(route('partner.branding.update'), [
            'brand_primary_color' => '#2563eb',
            'brand_secondary_color' => '#0f172a',
        ])->assertForbidden();
    }

    private function catalog(): array
    {
        $filial = FilialModel::create(['name' => 'B2B Main', 'code' => 'B2B-M']);
        $other = FilialModel::create(['name' => 'B2B Other', 'code' => 'B2B-O']);
        $service = ServicesModel::create(['name' => 'B2B Translation', 'price' => 1000000, 'deadline' => 3]);
        $documentType = DocumentTypeModel::create(['name' => 'B2B Document']);
        $product = PackageTemplate::create([
            'name' => 'B2B Package',
            'product_code' => 'B2B-PKG',
            'base_price' => 1000000,
            'standard_price' => 1000000,
            'express_price' => 1200000,
            'standard_deadline_days' => 3,
            'express_deadline_days' => 1,
            'margin_percent' => 50,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'is_active' => true,
            'is_sellable' => true,
        ]);
        PackageTemplateItem::create([
            'package_template_id' => $product->id,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'base_price' => 1000000,
            'sort_order' => 1,
        ]);

        return [$filial, $other, $product];
    }

    private function partner(FilialModel $filial, string $code, float $discount): Partner
    {
        $partner = Partner::create([
            'company_name' => $code . ' Partner',
            'code' => $code,
            'type' => 'consulting',
            'discount_percent' => $discount,
            'payment_terms_days' => 30,
            'currency' => 'UZS',
            'status' => 'active',
        ]);
        $partner->filials()->attach($filial->id, ['is_active' => true]);

        return $partner;
    }

    private function partnerUser(Partner $partner): User
    {
        Role::firstOrCreate(['name' => 'partner_admin', 'guard_name' => 'web']);
        $user = User::create([
            'name' => 'Partner Admin',
            'login' => strtolower($partner->code) . '-admin',
            'phone' => '998' . random_int(100000000, 999999999),
            'password' => Hash::make('password'),
            'partner_id' => $partner->id,
        ]);
        $user->assignRole('partner_admin');

        return $user;
    }

    private function orderAttributes(FilialModel $filial): array
    {
        return [
            'client_id' => ClientsModel::create(['name' => 'Existing Client ' . Str::random(4), 'phone_number' => '998' . random_int(100000000, 999999999), 'filial_id' => $filial->id])->id,
            'filial_id' => $filial->id,
            'order_code' => 'ORD-' . Str::upper(Str::random(12)),
            'tracking_token' => Str::random(64),
            'status' => 'received',
            'currency' => 'UZS',
            'billing_status' => 'unbilled',
        ];
    }
}
