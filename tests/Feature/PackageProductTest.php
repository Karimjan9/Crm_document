<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentTypeModel;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateItem;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\OrderCaseService;
use App\Services\PackageProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PackageProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_product_catalog_respects_filial_and_express_quote(): void
    {
        $firstFilial = FilialModel::create(['name' => 'Product One', 'code' => 'P1']);
        $secondFilial = FilialModel::create(['name' => 'Product Two', 'code' => 'P2']);
        $service = ServicesModel::create(['name' => 'Translation', 'price' => 100000, 'deadline' => 4]);
        $documentType = DocumentTypeModel::create(['name' => 'Passport']);
        $product = PackageTemplate::create([
            'name' => 'Student Abroad Package',
            'product_code' => 'PKG-STUDENT-ABROAD',
            'description' => 'Full student package',
            'process_mode' => 'service',
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'base_price' => 100000,
            'promo_price' => 90000,
            'standard_price' => 1200000,
            'express_price' => 1650000,
            'standard_deadline_days' => 10,
            'express_deadline_days' => 5,
            'margin_percent' => 35,
            'delivery_type' => 'pickup',
            'is_active' => true,
            'is_sellable' => true,
        ]);
        PackageTemplateItem::create([
            'package_template_id' => $product->id,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'base_price' => 100000,
        ]);
        $product->packageFilials()->create(['filial_id' => $firstFilial->id, 'is_available' => true]);

        $catalog = app(PackageProductService::class);
        $this->assertCount(1, $catalog->catalog($firstFilial->id));
        $this->assertCount(0, $catalog->catalog($secondFilial->id));
        $quote = $catalog->quote($product, $firstFilial->id, 'express');
        $this->assertSame(1650000.0, $quote['price']);
        $this->assertSame(5, $quote['deadline_days']);
        $this->assertSame(577500.0, $quote['expected_profit']);
    }

    public function test_selected_product_is_snapshotted_into_order_and_invoice_lines(): void
    {
        $filial = FilialModel::create(['name' => 'Order Product', 'code' => 'OP']);
        Role::findOrCreate('employee', 'web');
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $client = ClientsModel::create([
            'name' => 'Package Client',
            'phone_number' => '998901234567',
            'filial_id' => $filial->id,
        ]);
        $service = ServicesModel::create(['name' => 'Corporate translation', 'price' => 100000, 'deadline' => 3]);
        $documentType = DocumentTypeModel::create(['name' => 'Contract']);
        $product = PackageTemplate::create([
            'name' => 'Corporate Translation Package',
            'product_code' => 'PKG-CORP-001',
            'process_mode' => 'service',
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'base_price' => 100000,
            'promo_price' => 500000,
            'standard_price' => 500000,
            'express_price' => 700000,
            'standard_deadline_days' => 7,
            'express_deadline_days' => 3,
            'margin_percent' => 40,
            'is_active' => true,
            'is_sellable' => true,
        ]);
        PackageTemplateItem::create([
            'package_template_id' => $product->id,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'base_price' => 100000,
        ]);

        $order = app(OrderCaseService::class)->createForClient($client, $filial->id, $employee->id);
        $order = app(PackageProductService::class)->attachToOrder($order, $product->id, 'express');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'package_template_id' => $product->id,
            'package_variant' => 'express',
            'package_price' => 700000,
            'package_deadline_days' => 3,
            'package_name_snapshot' => 'Corporate Translation Package',
        ]);
        $this->assertDatabaseHas('order_price_lines', [
            'order_id' => $order->id,
            'line_type' => 'package',
            'total_price' => 700000,
        ]);
        $this->assertSame(700000.0, (float) Order::findOrFail($order->id)->total_amount);
    }

    public function test_order_form_can_sell_a_product_with_express_variant(): void
    {
        $filial = FilialModel::create(['name' => 'Store Product', 'code' => 'SP']);
        Role::findOrCreate('employee', 'web');
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $client = ClientsModel::create(['name' => 'Store Client', 'phone_number' => '998901234568', 'filial_id' => $filial->id]);
        $service = ServicesModel::create(['name' => 'Visa service', 'price' => 100000, 'deadline' => 2]);
        $documentType = DocumentTypeModel::create(['name' => 'Visa form']);
        $product = PackageTemplate::create([
            'name' => 'Visa Document Package',
            'product_code' => 'PKG-VISA-001',
            'process_mode' => 'service',
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'standard_price' => 400000,
            'express_price' => 600000,
            'standard_deadline_days' => 8,
            'express_deadline_days' => 2,
            'margin_percent' => 30,
            'is_active' => true,
            'is_sellable' => true,
        ]);
        PackageTemplateItem::create([
            'package_template_id' => $product->id,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'base_price' => 100000,
        ]);

        $this->actingAs($employee)
            ->post(route('orders.store'), [
                'client_id' => $client->id,
                'package_template_id' => $product->id,
                'package_variant' => 'express',
                'priority' => 'normal',
                'customer_source' => 'Instagram',
                'delivery_type' => 'courier',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('express', $order->package_variant);
        $this->assertSame('Instagram', $order->customer_source);
        $this->assertSame('courier', $order->delivery_type);
        $this->assertSame(600000.0, (float) $order->total_amount);
    }
}
