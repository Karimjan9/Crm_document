<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\PriceTariff;
use App\Models\PricingApproval;
use App\Models\ServicesModel;
use App\Models\User;
use App\Services\PricingService;
use App\Support\StoresDocuments;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PricingHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_branch_and_effective_date_tariff_beats_global_tariff(): void
    {
        $filial = FilialModel::create(['name' => 'Pricing branch', 'code' => 'PRC']);
        $service = ServicesModel::create(['name' => 'Pricing service', 'price' => 10, 'deadline' => 1]);
        $from = CarbonImmutable::parse('2026-01-01 00:00:00');
        $to = CarbonImmutable::parse('2026-02-01 00:00:00');

        PriceTariff::create([
            'line_type' => 'base_service',
            'service_id' => $service->id,
            'variant' => 'standard',
            'name' => 'Global January',
            'price' => 100,
            'effective_from' => $from,
            'effective_to' => $to,
        ]);
        $branchTariff = PriceTariff::create([
            'line_type' => 'base_service',
            'service_id' => $service->id,
            'filial_id' => $filial->id,
            'variant' => 'standard',
            'name' => 'Branch January',
            'price' => 120,
            'effective_from' => $from,
            'effective_to' => $to,
        ]);

        $quote = app(PricingService::class)->resolveService($service, $filial->id, [
            'as_of' => '2026-01-15 12:00:00',
        ]);

        $this->assertSame(120.0, (float) $quote['price']);
        $this->assertSame($branchTariff->id, $quote['tariff_id']);
        $this->assertSame('Branch January', $quote['name']);
    }

    public function test_document_keeps_pricing_snapshot_after_tariff_changes(): void
    {
        [$employee, $filial, $client, $service] = $this->documentContext();
        $tariff = PriceTariff::create([
            'line_type' => 'base_service',
            'service_id' => $service->id,
            'variant' => 'standard',
            'name' => 'Initial tariff',
            'price' => 150,
            'effective_from' => now()->subMinute(),
        ]);

        $this->actingAs($employee);
        $document = $this->store()->store([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'pricing_as_of' => now()->toIso8601String(),
            'discount' => 0,
        ]);

        PriceTariff::create([
            'line_type' => 'base_service',
            'service_id' => $service->id,
            'variant' => 'standard',
            'name' => 'Future tariff',
            'price' => 240,
            'effective_from' => now()->addDay(),
        ]);
        $service->update(['price' => 999]);

        $document->refresh();
        $line = data_get($document->pricing_snapshot, 'line_items.0');

        $this->assertSame(150.0, (float) $document->final_price);
        $this->assertSame(150.0, (float) data_get($line, 'total_price'));
        $this->assertSame($tariff->id, data_get($line, 'price_tariff_id'));
        $this->assertDatabaseHas('order_price_lines', [
            'document_id' => $document->id,
            'price_tariff_id' => $tariff->id,
            'pricing_line_type' => 'base_service',
            'total_price' => 150,
        ]);
    }

    public function test_express_fee_and_tax_are_explicit_price_lines(): void
    {
        [$employee, $filial, $client, $service] = $this->documentContext();
        PriceTariff::create([
            'line_type' => 'base_service',
            'service_id' => $service->id,
            'variant' => 'standard',
            'name' => 'Standard service',
            'price' => 100,
            'effective_from' => now()->subMinute(),
        ]);
        $express = PriceTariff::create([
            'line_type' => 'express_fee',
            'source_id' => $service->id,
            'price_key' => 'service:' . $service->id,
            'variant' => 'express',
            'name' => 'Express fee',
            'price' => 40,
            'effective_from' => now()->subMinute(),
        ]);

        $this->actingAs($employee);
        $document = $this->store()->store([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'pricing_variant' => 'express',
            'tax_percent' => 10,
            'discount' => 0,
        ]);

        $types = collect(data_get($document->pricing_snapshot, 'line_items', []))->pluck('line_type');

        $this->assertTrue($types->contains('base_service'));
        $this->assertTrue($types->contains('express_fee'));
        $this->assertTrue($types->contains('tax'));
        $this->assertSame(154.0, (float) $document->final_price);
        $this->assertDatabaseHas('order_price_lines', [
            'document_id' => $document->id,
            'price_tariff_id' => $express->id,
            'pricing_line_type' => 'express_fee',
        ]);
    }

    public function test_large_discount_requires_approved_pricing_request(): void
    {
        [$employee, $filial, $client, $service] = $this->documentContext();
        $this->actingAs($employee);

        $this->expectException(ValidationException::class);
        $this->store()->store([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'discount' => 20,
            'discount_type' => 'percent',
        ]);
    }

    public function test_approved_discount_can_be_used_for_new_document(): void
    {
        [$employee, $filial, $client, $service] = $this->documentContext();
        $approval = PricingApproval::create([
            'requested_by_id' => $employee->id,
            'status' => 'approved',
            'discount_percent' => 20,
            'discount_amount' => 20,
            'reason' => 'Strategic customer',
            'approved_by_id' => $employee->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($employee);
        $document = $this->store()->store([
            'client_id' => $client->id,
            'filial_id' => $filial->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'discount' => 20,
            'discount_type' => 'percent',
            'pricing_approval_id' => $approval->id,
        ]);

        $this->assertSame(80.0, (float) $document->final_price);
        $this->assertSame('approved', $document->discount_approval_status);
        $this->assertDatabaseHas('pricing_approvals', [
            'id' => $approval->id,
            'document_id' => $document->id,
        ]);
    }

    private function documentContext(): array
    {
        Role::findOrCreate('employee', 'web');
        $filial = FilialModel::create(['name' => 'Document pricing filial', 'code' => 'DPR']);
        $employee = User::factory()->create(['filial_id' => $filial->id]);
        $employee->assignRole('employee');
        $client = ClientsModel::create([
            'name' => 'Pricing client',
            'phone_number' => '901234567',
            'filial_id' => $filial->id,
        ]);
        $service = ServicesModel::create([
            'name' => 'Pricing service',
            'price' => 100,
            'deadline' => 2,
        ]);

        return [$employee, $filial, $client, $service];
    }

    private function store(): object
    {
        return new class
        {
            use StoresDocuments;

            public function store(array $payload): DocumentsModel
            {
                return $this->storeDocumentFromPayload($payload);
            }
        };
    }
}
