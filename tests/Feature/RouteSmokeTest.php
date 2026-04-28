<?php

namespace Tests\Feature;

use App\Models\ApostilStatikModel;
use App\Models\ClientsModel;
use App\Models\ConsulationTypeModel;
use App\Models\ConsulModel;
use App\Models\DirectionTypeModel;
use App\Models\DocumentCourier;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\DocumentsModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentTypeModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Holiday;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateItem;
use App\Models\PaymentsModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\SMSMessageTextModel;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $ids = [];

    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->seedFakeData();
    }

    public function test_project_routes_do_not_throw_server_errors_with_fake_data(): void
    {
        $failures = [];
        $checked = 0;

        foreach (RouteFacade::getRoutes() as $route) {
            if ($this->shouldSkipRoute($route)) {
                continue;
            }

            $methods = $this->methodsToSmoke($route);

            foreach ($methods as $method) {
                $checked++;

                $uri = $this->uriFor($route);
                $payload = $this->payloadFor($route, $method, $checked);
                $actor = $this->actorFor($route);
                $transactionLevel = DB::transactionLevel();

                DB::beginTransaction();

                try {
                    if ($actor) {
                        if (in_array('auth:sanctum', $route->gatherMiddleware(), true)) {
                            Sanctum::actingAs($actor);
                        } else {
                            $this->actingAs($actor);
                        }
                    }

                    $response = $this->call($method, $uri, $payload, [], [], [
                        'HTTP_ACCEPT' => str_contains($uri, '/api/') || str_starts_with($uri, '/api')
                            ? 'application/json'
                            : 'text/html,application/xhtml+xml',
                    ]);

                    if ($response->getStatusCode() >= 500) {
                        $failures[] = sprintf(
                            '%s %s [%s] returned %s',
                            $method,
                            $uri,
                            $route->getName() ?: $route->getActionName(),
                            $response->getStatusCode()
                        );
                    }
                } catch (\Throwable $exception) {
                    $failures[] = sprintf(
                        '%s %s [%s] threw %s: %s',
                        $method,
                        $uri,
                        $route->getName() ?: $route->getActionName(),
                        $exception::class,
                        $exception->getMessage()
                    );
                } finally {
                    while (DB::transactionLevel() > $transactionLevel) {
                        DB::rollBack();
                    }

                    Auth::guard('web')->logout();
                    $this->flushSession();
                }
            }
        }

        $this->assertGreaterThan(100, $checked, 'Route smoke test did not cover enough routes.');
        $this->assertSame([], $failures, implode(PHP_EOL, $failures));
    }

    private function seedFakeData(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'admin_manager', 'employee', 'admin_filial', 'courier'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $filial = FilialModel::create([
            'name' => 'Test Filial',
            'code' => 'TST',
            'description' => 'Fake data for route smoke tests',
        ]);

        foreach (['super_admin', 'admin_manager', 'employee', 'admin_filial', 'courier'] as $role) {
            $user = User::factory()->create([
                'name' => Str::headline($role),
                'login' => $role . '_tester',
                'phone' => (string) random_int(100000000, 999999999),
                'filial_id' => $filial->id,
                'password' => Hash::make('password'),
            ]);

            $user->assignRole($role);
            $this->users[$role] = $user;
        }

        $service = ServicesModel::create([
            'name' => 'Translation',
            'description' => 'Fake service',
            'price' => 100000,
            'deadline' => 3,
        ]);

        $serviceAddon = ServiceAddonModel::create([
            'service_id' => $service->id,
            'name' => 'Urgent',
            'description' => 'Fake addon',
            'price' => 20000,
            'deadline' => 1,
        ]);

        $client = ClientsModel::create([
            'name' => 'Fake Client',
            'phone_number' => '901234567',
            'description' => 'Fake client',
        ]);

        $documentType = DocumentTypeModel::create([
            'name' => 'Passport',
            'description' => 'Fake document type',
        ]);

        $documentTypeAddition = DocumentTypeAdditionModel::create([
            'document_type_id' => $documentType->id,
            'name' => 'Notary copy',
            'description' => 'Fake type addon',
            'amount' => 15000,
            'day' => 1,
        ]);

        $directionType = DirectionTypeModel::create([
            'name' => 'Uzbek to English',
            'description' => 'Fake direction',
        ]);

        $directionAddition = DocumentDirectionAdditionModel::create([
            'document_direction_id' => $directionType->id,
            'name' => 'Extra page',
            'description' => 'Fake direction addon',
            'amount' => 10000,
            'day' => 1,
        ]);

        $consulateType = ConsulationTypeModel::create([
            'name' => 'Legalization',
            'description' => 'Fake consulate type',
            'amount' => 50000,
            'day' => 2,
        ]);

        $consul = ConsulModel::create([
            'name' => 'Fake Consul',
            'amount' => 80000,
            'day' => 2,
        ]);

        $apostil = ApostilStatikModel::create([
            'name' => 'Fake Apostil',
            'price' => 75000,
            'group_id' => 1,
            'days' => 2,
        ]);

        $document = DocumentsModel::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'document_type_id' => $documentType->id,
            'direction_type_id' => $directionType->id,
            'consulate_type_id' => $consulateType->id,
            'service_price' => 100000,
            'addons_total_price' => 20000,
            'deadline_time' => 4,
            'final_price' => 120000,
            'paid_amount' => 10000,
            'discount' => 0,
            'user_id' => $this->users['employee']->id,
            'description' => 'Fake document',
            'filial_id' => $filial->id,
            'document_code' => 'TST-0001',
            'status_doc' => 'process',
            'process_mode' => 'service',
        ]);

        $document->addons()->attach($serviceAddon->id, [
            'addon_price' => $serviceAddon->price,
            'addon_deadline' => $serviceAddon->deadline,
        ]);

        PaymentsModel::create([
            'document_id' => $document->id,
            'amount' => 10000,
            'payment_type' => 'cash',
            'paid_by_admin_id' => $this->users['employee']->id,
        ]);

        $documentCourier = DocumentCourier::create([
            'document_id' => $document->id,
            'courier_id' => $this->users['courier']->id,
            'sent_by_id' => $this->users['employee']->id,
            'status' => 'returned',
            'sent_comment' => 'Fake send',
            'return_comment' => 'Fake return',
            'sent_at' => now()->subDay(),
            'returned_at' => now(),
        ]);

        $expense = ExpenseAdminModel::create([
            'user_id' => $this->users['employee']->id,
            'amount' => 50000,
            'filial_id' => $filial->id,
            'description' => 'Fake expense',
        ]);

        $smsText = SMSMessageTextModel::create([
            'name' => 'Ready notification',
            'type' => 'xabarnoma',
            'message_text1' => 'Your document is ready',
            'message_text2' => 'Please visit branch',
            'message_text3' => 'Thanks',
            'description' => 'Fake SMS text',
        ]);

        $holiday = Holiday::create([
            'title' => 'Fake Holiday',
            'date' => now()->addMonth()->toDateString(),
            'type' => 'national',
            'color' => '#3366ff',
            'description' => 'Fake holiday',
            'is_recurring' => false,
            'is_active' => true,
            'created_by' => $this->users['super_admin']->id,
        ]);

        $packageTemplate = PackageTemplate::create([
            'name' => 'Fast package',
            'highlight' => 'Popular',
            'description' => 'Fake package',
            'process_mode' => 'service',
            'selection_mode' => null,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'direction_type_id' => null,
            'apostil_group1_id' => null,
            'apostil_group2_id' => null,
            'consul_id' => null,
            'consulate_type_id' => null,
            'selected_addons' => [],
            'base_price' => 100000,
            'promo_price' => 90000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PackageTemplateItem::create([
            'package_template_id' => $packageTemplate->id,
            'document_type_id' => $documentType->id,
            'service_id' => $service->id,
            'process_mode' => 'service',
            'selection_mode' => null,
            'direction_type_id' => null,
            'apostil_group1_id' => null,
            'apostil_group2_id' => null,
            'consul_id' => null,
            'consulate_type_id' => null,
            'selected_addons' => [],
            'base_price' => 100000,
            'sort_order' => 1,
        ]);

        $this->ids = [
            'filial' => $filial->id,
            'service' => $service->id,
            'addon' => $serviceAddon->id,
            'client' => $client->id,
            'document' => $document->id,
            'documentCourier' => $documentCourier->id,
            'document_type' => $documentType->id,
            'direction_type' => $directionType->id,
            'type_addition' => $documentTypeAddition->id,
            'direction_addition' => $directionAddition->id,
            'consulation' => $consulateType->id,
            'consul' => $consul->id,
            'apostil' => $apostil->id,
            'expense' => $expense->id,
            'sms_message_text' => $smsText->id,
            'holiday' => $holiday->id,
            'templatePackage' => $packageTemplate->id,
            'user' => $this->users['employee']->id,
        ];
    }

    private function shouldSkipRoute(LaravelRoute $route): bool
    {
        $uri = $route->uri();
        $action = $route->getActionName();

        return str_starts_with($uri, '_debugbar')
            || str_starts_with($uri, 'sanctum/')
            || str_contains($action, 'Debugbar');
    }

    private function methodsToSmoke(LaravelRoute $route): array
    {
        $methods = array_values(array_filter(
            $route->methods(),
            fn (string $method) => ! in_array($method, ['HEAD', 'OPTIONS'], true)
        ));

        if (in_array('GET', $methods, true)) {
            return ['GET'];
        }

        if (in_array('PUT', $methods, true)) {
            return ['PUT'];
        }

        if (in_array('PATCH', $methods, true)) {
            return ['PATCH'];
        }

        return array_slice($methods, 0, 1);
    }

    private function uriFor(LaravelRoute $route): string
    {
        $uri = $route->uri();

        $uri = preg_replace_callback('/\{([^}]+)\}/', function (array $matches) use ($route) {
            $name = rtrim($matches[1], '?');

            return (string) $this->routeParameterValue($name, $route);
        }, $uri);

        return '/' . ltrim((string) $uri, '/');
    }

    private function routeParameterValue(string $name, LaravelRoute $route): string|int
    {
        return match ($name) {
            'filial' => $this->ids['filial'],
            'service' => $this->ids['service'],
            'addon' => $this->ids['addon'],
            'client' => $this->ids['client'],
            'document' => $this->ids['document'],
            'documentCourier' => $this->ids['documentCourier'],
            'document_type' => $this->ids['document_type'],
            'direction_type' => $this->ids['direction_type'],
            'type_addition' => $this->ids['type_addition'],
            'direction_addition' => $this->ids['direction_addition'],
            'consulation', 'consulate' => $this->ids['consulation'],
            'apostil' => $this->ids['apostil'],
            'expense', 'expense_admin' => $this->ids['expense'],
            'sms_message_text' => $this->ids['sms_message_text'],
            'templatePackage' => $this->ids['templatePackage'],
            'dataset' => 'clients',
            'date' => now()->addMonth()->toDateString(),
            'type' => 'service',
            'addition' => str_contains($route->uri(), 'direction') ? $this->ids['direction_addition'] : $this->ids['type_addition'],
            'id' => $this->genericIdFor($route),
            default => 1,
        };
    }

    private function genericIdFor(LaravelRoute $route): int
    {
        $uri = $route->uri();

        if (str_contains($uri, 'holidays')) {
            return $this->ids['holiday'];
        }

        if (str_contains($uri, 'consulation')) {
            return $this->ids['consulation'];
        }

        return $this->ids['user'];
    }

    private function actorFor(LaravelRoute $route): ?User
    {
        $uri = $route->uri();

        if ($uri === '/' || str_starts_with($uri, 'login')) {
            return null;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! str_starts_with($middleware, 'role:')) {
                continue;
            }

            $roles = explode('|', substr($middleware, 5));

            foreach (['super_admin', 'admin_manager', 'admin_filial', 'employee', 'courier'] as $role) {
                if (in_array($role, $roles, true)) {
                    return $this->users[$role];
                }
            }
        }

        return $this->users['super_admin'];
    }

    private function payloadFor(LaravelRoute $route, string $method, int $sequence): array
    {
        $uri = $route->uri();
        $phone = (string) (900000000 + $sequence);

        $payload = [
            'name' => 'Fake Name ' . $sequence,
            'title' => 'Fake Title ' . $sequence,
            'code' => 'T' . $sequence,
            'description' => 'Fake description ' . $sequence,
            'login' => 'fake_login_' . $sequence,
            'phone' => $phone,
            'phone_number' => $phone,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
            'filial_id' => $this->ids['filial'],
            'user_id' => $this->users['employee']->id,
            'service_id' => $this->ids['service'],
            'document_id' => $this->ids['document'],
            'document_type_id' => $this->ids['document_type'],
            'direction_type_id' => $this->ids['direction_type'],
            'consulate_type_id' => $this->ids['consulation'],
            'client_id' => $this->ids['client'],
            'new_client_name' => 'Fake Client ' . $sequence,
            'new_client_phone' => $phone,
            'new_client_desc' => 'Fake client description',
            'amount' => 1000,
            'price' => 100000,
            'deadline' => 3,
            'day' => 1,
            'days' => 1,
            'discount' => 0,
            'final_price' => 120000,
            'paid_amount' => 1000,
            'payment_type' => 'cash',
            'process_mode' => 'service',
            'selection_mode' => null,
            'apostil_group1_id' => $this->ids['apostil'],
            'apostil_group2_id' => $this->ids['apostil'],
            'consul_id' => $this->ids['consul'],
            'addons' => [$this->ids['addon']],
            'selected_addons' => json_encode([]),
            'courier_id' => $this->users['courier']->id,
            'comment' => 'Fake courier comment',
            'date' => now()->addMonths(2)->toDateString(),
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->endOfMonth()->toDateString(),
            'type' => 'national',
            'color' => '#3366ff',
            'is_recurring' => false,
            'is_active' => true,
            'weather_city' => 'Tashkent',
            'reduced_motion' => false,
            'current_password' => 'password',
            'base_price' => 100000,
            'promo_price' => 90000,
            'sort_order' => 1,
            'highlight' => 'Popular',
        ];

        if (str_contains($uri, 'login')) {
            return [
                'login' => $this->users['super_admin']->login,
                'password' => 'password',
            ];
        }

        if (str_contains($uri, 'account/password')) {
            return [
                'current_password' => 'password',
                'password' => 'new-password-' . $sequence,
                'password_confirmation' => 'new-password-' . $sequence,
            ];
        }

        if (str_contains($uri, 'account/settings')) {
            return [
                'weather_city' => 'Tashkent',
                'reduced_motion' => false,
            ];
        }

        if (str_contains($uri, 'api/document/save-all')) {
            return [
                'client_id' => $this->ids['client'],
                'items' => [[
                    'client_id' => $this->ids['client'],
                    'service_id' => $this->ids['service'],
                    'document_type_id' => $this->ids['document_type'],
                    'process_mode' => 'service',
                    'discount' => 0,
                    'paid_amount' => 0,
                    'selected_addons' => [],
                ]],
            ];
        }

        if (str_contains($uri, 'template-package')) {
            return $payload + [
                'items' => [[
                    'document_type_id' => $this->ids['document_type'],
                    'service_id' => $this->ids['service'],
                    'process_mode' => 'service',
                    'selected_addons' => [],
                    'base_price' => 100000,
                    'sort_order' => 1,
                ]],
            ];
        }

        return $payload;
    }
}
