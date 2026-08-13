<?php

namespace Tests\Feature;

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Support\StoresDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BusinessIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_percent_discount_cannot_exceed_one_hundred(): void
    {
        $context = $this->documentContext();

        $this->expectException(ValidationException::class);

        $context['store']->store([
            'client_id' => $context['client']->id,
            'service_id' => $context['service']->id,
            'process_mode' => 'service',
            'discount' => 101,
            'discount_type' => 'percent',
            'paid_amount' => 0,
        ]);
    }

    public function test_initial_payment_cannot_exceed_final_price(): void
    {
        $context = $this->documentContext();

        $this->expectException(ValidationException::class);

        $context['store']->store([
            'client_id' => $context['client']->id,
            'service_id' => $context['service']->id,
            'process_mode' => 'service',
            'discount' => 0,
            'paid_amount' => 100.01,
            'payment_type' => 'cash',
        ]);
    }

    public function test_payment_cannot_exceed_locked_document_balance(): void
    {
        $context = $this->documentContext();
        $document = $context['store']->store([
            'client_id' => $context['client']->id,
            'service_id' => $context['service']->id,
            'process_mode' => 'service',
            'discount' => 0,
            'paid_amount' => 0,
        ]);

        $this->expectException(ValidationException::class);

        $context['store']->pay($document, [
            'amount' => 100.01,
            'payment_type' => 'cash',
        ]);
    }

    public function test_nested_service_addon_cannot_be_read_through_another_service(): void
    {
        Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $firstService = ServicesModel::create([
            'name' => 'First service',
            'price' => 100,
            'deadline' => 1,
        ]);
        $secondService = ServicesModel::create([
            'name' => 'Second service',
            'price' => 200,
            'deadline' => 2,
        ]);
        $addon = ServiceAddonModel::create([
            'service_id' => $secondService->id,
            'name' => 'Second addon',
            'price' => 10,
            'deadline' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('superadmin.addon.edit', [
                'service' => $firstService,
                'addon' => $addon,
            ]))
            ->assertNotFound();
    }

    public function test_weather_proxy_does_not_expose_provider_key(): void
    {
        config([
            'services.openweather.key' => 'test-key',
            'services.openweather.url' => 'https://weather.test/current',
        ]);
        Http::fake([
            'https://weather.test/current*' => Http::response([
                'main' => ['temp' => 24.4],
                'weather' => [['description' => 'clear sky']],
            ]),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('weather', ['city' => 'Tashkent']))
            ->assertOk()
            ->assertJsonPath('main.temp', 24.4);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://weather.test/current?q=Tashkent&appid=test-key&units=metric';
        });

        $this->assertStringNotContainsString('test-key', (string) $this->get(route('weather', ['city' => 'Tashkent']))->getContent());
    }

    private function documentContext(): array
    {
        $filial = FilialModel::create([
            'name' => 'Business Filial',
            'code' => 'BUS',
        ]);
        $this->actingAs(User::factory()->create(['filial_id' => $filial->id]));
        $client = ClientsModel::create([
            'name' => 'Business Client',
            'phone_number' => '901234599',
        ]);
        $service = ServicesModel::create([
            'name' => 'Business Service',
            'price' => 100,
            'deadline' => 1,
        ]);
        $store = new class
        {
            use StoresDocuments;

            public function store(array $payload): DocumentsModel
            {
                return $this->storeDocumentFromPayload($payload);
            }

            public function pay(DocumentsModel $document, array $payload): void
            {
                $this->recordPayment($document, Request::create('/', 'POST', $payload));
            }
        };

        return compact('client', 'service', 'store');
    }
}
