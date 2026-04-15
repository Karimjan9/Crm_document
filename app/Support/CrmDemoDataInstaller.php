<?php

namespace App\Support;

use App\Models\ApostilStatikModel;
use App\Models\ClientsModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DocumentCourier;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\DocumentProcessChargeModel;
use App\Models\DocumentsModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Holiday;
use App\Models\PackageTemplate;
use App\Models\PaymentsModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrmDemoDataInstaller
{
    public function seed(): void
    {
        $this->seedServices();
        $this->seedDocumentAdditions();
        $this->seedClients();
        $this->seedHolidays();
        $this->seedPackageTemplates();
        $this->seedDocumentsAndExpenses();
    }

    private function seedServices(): void
    {
        $services = [
            [
                'name' => 'Tarjima xizmati',
                'description' => 'Hujjatlarni tarjima qilish va tayyorlash xizmati.',
                'price' => 120000,
                'deadline' => 2,
                'addons' => [
                    [
                        'name' => 'Notarial tasdiq',
                        'description' => 'Tarjima qilingan hujjatni notarial tasdiqlash.',
                        'price' => 45000,
                        'deadline' => 1,
                    ],
                    [
                        'name' => 'Ekspress navbat',
                        'description' => "Tezkor ko'rib chiqish uchun ustuvor navbat.",
                        'price' => 60000,
                        'deadline' => 0,
                    ],
                ],
            ],
            [
                'name' => 'Notarial tasdiqlash',
                'description' => 'Asl hujjat yoki nusxani notarial tasdiqlash xizmati.',
                'price' => 95000,
                'deadline' => 1,
                'addons' => [
                    [
                        'name' => "Qo'shimcha nusxa",
                        'description' => "Tasdiqlangan qo'shimcha nusxa tayyorlash.",
                        'price' => 25000,
                        'deadline' => 0,
                    ],
                    [
                        'name' => 'Arxiv nusxasi',
                        'description' => 'Ichki arxiv uchun raqamli yoki bosma nusxa.',
                        'price' => 35000,
                        'deadline' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Apostil tayyorlash',
                'description' => "Apostil jarayoni uchun hujjatlarni to'liq tayyorlash.",
                'price' => 180000,
                'deadline' => 3,
                'addons' => [
                    [
                        'name' => 'Davlat boji',
                        'description' => "Apostil uchun to'lovlar va rasmiy yig'imlar.",
                        'price' => 75000,
                        'deadline' => 1,
                    ],
                    [
                        'name' => 'QR tekshiruvi',
                        'description' => "QR va reyestr bo'yicha tekshiruv xizmati.",
                        'price' => 30000,
                        'deadline' => 0,
                    ],
                ],
            ],
            [
                'name' => 'Legalizatsiya xizmati',
                'description' => "Konsullik va legalizatsiya bilan bog'liq xizmatlar.",
                'price' => 260000,
                'deadline' => 5,
                'addons' => [
                    [
                        'name' => 'Tarjima paketi',
                        'description' => 'Legalizatsiya uchun zarur tarjima ishlari.',
                        'price' => 80000,
                        'deadline' => 2,
                    ],
                    [
                        'name' => 'Elchixona navbati',
                        'description' => 'Elchixona topshiruvi uchun tayyor navbat xizmati.',
                        'price' => 95000,
                        'deadline' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Kuryer xizmati',
                'description' => 'Hujjatlarni filial yoki mijoz manziliga yetkazib berish.',
                'price' => 70000,
                'deadline' => 1,
                'addons' => [
                    [
                        'name' => "Shahar bo'ylab yetkazish",
                        'description' => 'Shahar ichida tezkor yetkazib berish.',
                        'price' => 20000,
                        'deadline' => 0,
                    ],
                    [
                        'name' => "Viloyatga jo'natish",
                        'description' => "Viloyatlarga yuborish va kuzatib borish xizmati.",
                        'price' => 45000,
                        'deadline' => 2,
                    ],
                ],
            ],
        ];

        foreach ($services as $serviceData) {
            $service = ServicesModel::query()->updateOrCreate(
                ['name' => $serviceData['name']],
                Arr::except($serviceData, ['addons'])
            );

            foreach ($serviceData['addons'] as $addonData) {
                ServiceAddonModel::query()->updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'name' => $addonData['name'],
                    ],
                    $addonData
                );
            }
        }
    }

    private function seedDocumentAdditions(): void
    {
        $documentTemplates = [
            [
                'name' => 'Notarial nusxa',
                'description' => "Hujjat uchun notarial qo'shimcha nusxa.",
                'amount' => 30000,
                'day' => 1,
            ],
            [
                'name' => 'Skaner va PDF',
                'description' => "Raqamli ko'rinishda skaner va PDF tayyorlash.",
                'amount' => 15000,
                'day' => 0,
            ],
        ];

        foreach (DocumentTypeModel::query()->orderBy('id')->get() as $documentType) {
            foreach ($documentTemplates as $index => $template) {
                DocumentTypeAdditionModel::query()->updateOrCreate(
                    [
                        'document_type_id' => $documentType->id,
                        'name' => $template['name'],
                    ],
                    [
                        'description' => $template['description'],
                        'amount' => $template['amount'] + ($index * 5000),
                        'day' => $template['day'],
                    ]
                );
            }
        }

        $directionTemplates = [
            [
                'name' => 'Davlat boji',
                'description' => "Yo'nalish bo'yicha davlat yig'imi.",
                'amount' => 40000,
                'day' => 1,
            ],
            [
                'name' => "Tezkor ko'rib chiqish",
                'description' => "Jarayonni tezlashtirish uchun qo'shimcha xizmat.",
                'amount' => 55000,
                'day' => 0,
            ],
        ];

        foreach (DirectionTypeModel::query()->orderBy('id')->get() as $directionType) {
            foreach ($directionTemplates as $index => $template) {
                DocumentDirectionAdditionModel::query()->updateOrCreate(
                    [
                        'document_direction_id' => $directionType->id,
                        'name' => $template['name'],
                    ],
                    [
                        'description' => $template['description'],
                        'amount' => $template['amount'] + ($index * 5000),
                        'day' => $template['day'],
                    ]
                );
            }
        }
    }

    private function seedClients(): void
    {
        $clients = [
            [
                'name' => 'Aziza Karimova',
                'phone_number' => '998901100001',
                'description' => 'Talaba vizasi uchun hujjat topshirgan mijoz.',
            ],
            [
                'name' => 'Jasur Raxmatov',
                'phone_number' => '998901100002',
                'description' => 'Ishga joylashish uchun tarjima va notarial tasdiq olmoqda.',
            ],
            [
                'name' => 'Malika Toirova',
                'phone_number' => '998901100003',
                'description' => "Konsullik legalizatsiyasi bo'yicha murojaat qilgan.",
            ],
            [
                'name' => 'Sardor Qodirov',
                'phone_number' => '998901100004',
                'description' => "Apostil va qo'shimcha skaner xizmati kerak.",
            ],
            [
                'name' => 'Nilufar Eshonqulova',
                'phone_number' => '998901100005',
                'description' => 'Shoshilinch hujjat tayyorlash uchun demo mijoz.',
            ],
            [
                'name' => 'Bahrom Sattorov',
                'phone_number' => '998901100006',
                'description' => "Viloyatdan yuborilgan hujjatlar bo'yicha mijoz.",
            ],
            [
                'name' => 'Dilnoza Nurmatova',
                'phone_number' => '998901100007',
                'description' => "Tibbiy yo'nalishdagi hujjatlar bilan ishlamoqda.",
            ],
            [
                'name' => 'Bekzod Aminov',
                'phone_number' => '998901100008',
                'description' => "Xorijga o'qishga topshirish uchun xizmat olmoqda.",
            ],
            [
                'name' => 'Umida Xolmatova',
                'phone_number' => '998901100009',
                'description' => "Kuryer orqali qaytarilishi kerak bo'lgan hujjatlar.",
            ],
            [
                'name' => 'Sherzod Ergashev',
                'phone_number' => '998901100010',
                'description' => "Korporativ paket bo'yicha test mijoz.",
            ],
        ];

        foreach ($clients as $client) {
            ClientsModel::query()->updateOrCreate(
                ['phone_number' => $client['phone_number']],
                [
                    'name' => $client['name'],
                    'description' => $client['description'],
                ]
            );
        }
    }

    private function seedHolidays(): void
    {
        $superAdminId = User::query()
            ->where('login', 'superadmin')
            ->value('id');

        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);

        $records = [
            [
                'title' => 'Jamoa yigini',
                'date' => $startOfWeek->copy()->addDays(2)->toDateString(),
                'type' => 'company',
                'color' => '#4ecdc4',
                'description' => 'Test off day for deadline checks',
            ],
            [
                'title' => 'Hududiy tanaffus',
                'date' => $startOfWeek->copy()->addDays(4)->toDateString(),
                'type' => 'regional',
                'color' => '#ffd166',
                'description' => 'Seeded non-working day',
            ],
            [
                'title' => 'Ichki texnik kun',
                'date' => $startOfWeek->copy()->addWeek()->addDay()->toDateString(),
                'type' => 'other',
                'color' => '#00bbf9',
                'description' => 'Deadline should skip this day',
            ],
        ];

        foreach ($records as $record) {
            Holiday::query()->updateOrCreate(
                ['date' => $record['date']],
                $record + [
                    'is_recurring' => false,
                    'is_active' => true,
                    'created_by' => $superAdminId,
                ]
            );
        }
    }

    private function seedPackageTemplates(): void
    {
        $documentTypes = DocumentTypeModel::query()->orderBy('id')->get(['id', 'name'])->keyBy('id');
        $services = ServicesModel::query()->orderBy('id')->get(['id', 'name'])->keyBy('id');
        $directions = DirectionTypeModel::query()->orderBy('id')->get(['id', 'name'])->keyBy('id');
        $apostilGroup1 = ApostilStatikModel::query()->where('group_id', 1)->orderBy('id')->first(['id']);
        $apostilGroup2 = ApostilStatikModel::query()->where('group_id', 2)->orderBy('id')->first(['id']);
        $consul = ConsulModel::query()->orderBy('id')->first(['id']);
        $consulateType = ConsulationTypeModel::query()->orderBy('id')->first(['id']);

        if (
            $documentTypes->isEmpty()
            || $services->isEmpty()
            || $directions->isEmpty()
            || ! $apostilGroup1
            || ! $apostilGroup2
            || ! $consul
            || ! $consulateType
        ) {
            return;
        }

        $documentAddon = DocumentTypeAdditionModel::query()
            ->where('document_type_id', $documentTypes->keys()->first())
            ->orderBy('id')
            ->first(['id']);

        $directionAddon = DocumentDirectionAdditionModel::query()
            ->where('document_direction_id', $directions->keys()->first())
            ->orderBy('id')
            ->first(['id']);

        $serviceAddonByServiceId = ServiceAddonModel::query()
            ->orderBy('id')
            ->get(['id', 'service_id'])
            ->groupBy('service_id');

        $serviceAddonFor = function (int $serviceId, int $offset = 0) use ($serviceAddonByServiceId): ?int {
            return $serviceAddonByServiceId->get($serviceId)?->values()->get($offset)?->id;
        };

        $packages = [
            [
                'name' => 'Paket_1',
                'highlight' => '5 ta xizmatli bundle',
                'description' => "2 ta apostil, 2 ta legalizatsiya va 1 ta oddiy xizmat bitta paketga jamlangan. Xodim tanlashi bilan barcha wizardlar tayyor bo'ladi.",
                'promo_price' => 620000,
                'sort_order' => 10,
                'is_active' => true,
                'items' => array_filter([
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(1)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(1)?->id ?? $services->keys()->first(),
                        'process_mode' => 'apostil',
                        'direction_type_id' => $directions->get(1)?->id ?? $directions->keys()->first(),
                        'apostil_group1_id' => $apostilGroup1->id,
                        'apostil_group2_id' => $apostilGroup2->id,
                        'selected_addons' => array_filter([
                            $documentAddon ? ['id' => $documentAddon->id, 'sourceType' => 'document'] : null,
                            $directionAddon ? ['id' => $directionAddon->id, 'sourceType' => 'direction'] : null,
                            ($addonId = $serviceAddonFor(1, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(4)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(4)?->id ?? $services->keys()->last(),
                        'process_mode' => 'apostil',
                        'direction_type_id' => $directions->get(1)?->id ?? $directions->keys()->first(),
                        'apostil_group1_id' => $apostilGroup1->id,
                        'apostil_group2_id' => $apostilGroup2->id,
                        'selected_addons' => array_filter([
                            $directionAddon ? ['id' => $directionAddon->id, 'sourceType' => 'direction'] : null,
                            ($addonId = $serviceAddonFor(4, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(2)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(1)?->id ?? $services->keys()->first(),
                        'process_mode' => 'consul',
                        'selection_mode' => 'mixed',
                        'consul_id' => $consul->id,
                        'consulate_type_id' => $consulateType->id,
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(1, 1)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(3)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(2)?->id ?? $services->keys()->first(),
                        'process_mode' => 'consul',
                        'selection_mode' => 'legalization',
                        'consulate_type_id' => $consulateType->id,
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(2, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(5)?->id ?? $documentTypes->keys()->last(),
                        'service_id' => $services->get(4)?->id ?? $services->keys()->last(),
                        'process_mode' => 'service',
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(4, 1)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                ]),
            ],
            [
                'name' => 'Apostil start paketi',
                'highlight' => 'Tez boshlash',
                'description' => "Apostil va legalizatsiya oqimlari aralashgan ixcham paket. Xodimga tarkib va chegirma bir qarashda ko'rinadi.",
                'promo_price' => 399000,
                'sort_order' => 20,
                'is_active' => true,
                'items' => array_filter([
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(1)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(1)?->id ?? $services->keys()->first(),
                        'process_mode' => 'apostil',
                        'direction_type_id' => $directions->get(1)?->id ?? $directions->keys()->first(),
                        'apostil_group1_id' => $apostilGroup1->id,
                        'apostil_group2_id' => $apostilGroup2->id,
                        'selected_addons' => array_filter([
                            $documentAddon ? ['id' => $documentAddon->id, 'sourceType' => 'document'] : null,
                            $directionAddon ? ['id' => $directionAddon->id, 'sourceType' => 'direction'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(2)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(2)?->id ?? $services->keys()->first(),
                        'process_mode' => 'consul',
                        'selection_mode' => 'consul',
                        'consul_id' => $consul->id,
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(2, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(4)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(1)?->id ?? $services->keys()->first(),
                        'process_mode' => 'service',
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(1, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                ]),
            ],
            [
                'name' => 'Korporativ express paketi',
                'highlight' => 'Fix narxli combo',
                'description' => "Notarial xizmat, apostil va konsullik elementi bitta professional paketga jamlangan. Umumiy summa avtomatik, fix narx esa aksiyali ko'rinadi.",
                'promo_price' => 429000,
                'sort_order' => 30,
                'is_active' => true,
                'items' => array_filter([
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(1)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(1)?->id ?? $services->keys()->first(),
                        'process_mode' => 'service',
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(1, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                            ($addonId = $serviceAddonFor(1, 1)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(4)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(4)?->id ?? $services->keys()->last(),
                        'process_mode' => 'apostil',
                        'direction_type_id' => $directions->get(1)?->id ?? $directions->keys()->first(),
                        'apostil_group1_id' => $apostilGroup1->id,
                        'apostil_group2_id' => $apostilGroup2->id,
                        'selected_addons' => array_filter([
                            $directionAddon ? ['id' => $directionAddon->id, 'sourceType' => 'direction'] : null,
                            ($addonId = $serviceAddonFor(4, 0)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                    $this->itemConfig([
                        'document_type_id' => $documentTypes->get(2)?->id ?? $documentTypes->keys()->first(),
                        'service_id' => $services->get(4)?->id ?? $services->keys()->last(),
                        'process_mode' => 'consul',
                        'selection_mode' => 'mixed',
                        'consul_id' => $consul->id,
                        'consulate_type_id' => $consulateType->id,
                        'selected_addons' => array_filter([
                            ($addonId = $serviceAddonFor(4, 1)) ? ['id' => $addonId, 'sourceType' => 'service'] : null,
                        ]),
                    ]),
                ]),
            ],
        ];

        foreach ($packages as $package) {
            if (count($package['items']) < 1) {
                continue;
            }

            $this->storeTemplate($package);
        }
    }

    private function seedDocumentsAndExpenses(): void
    {
        $clients = ClientsModel::query()->orderBy('id')->get()->keyBy('phone_number');
        $services = ServicesModel::query()->orderBy('id')->get()->values();
        $documentTypes = DocumentTypeModel::query()->orderBy('id')->get()->values();
        $directions = DirectionTypeModel::query()->orderBy('id')->get()->values();
        $consulates = ConsulationTypeModel::query()->orderBy('id')->get()->values();
        $consuls = ConsulModel::query()->orderBy('id')->get()->values();
        $apostilGroup1 = ApostilStatikModel::query()->where('group_id', 1)->orderBy('id')->get()->values();
        $apostilGroup2 = ApostilStatikModel::query()->where('group_id', 2)->orderBy('id')->get()->values();
        $filials = FilialModel::query()->orderBy('id')->get()->values();
        $staff = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                'super_admin',
                'admin_manager',
                'admin_filial',
                'employee',
            ]))
            ->orderBy('id')
            ->get()
            ->values();
        $couriers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'courier'))
            ->orderBy('id')
            ->get()
            ->values();
        $serviceAddons = ServiceAddonModel::query()->orderBy('id')->get()->groupBy('service_id');
        $documentTypeAddons = DocumentTypeAdditionModel::query()->orderBy('id')->get()->groupBy('document_type_id');
        $directionAddons = DocumentDirectionAdditionModel::query()->orderBy('id')->get()->groupBy('document_direction_id');

        if (
            $clients->isEmpty()
            || $services->isEmpty()
            || $documentTypes->isEmpty()
            || $directions->isEmpty()
            || $consulates->isEmpty()
            || $consuls->isEmpty()
            || $apostilGroup1->isEmpty()
            || $apostilGroup2->isEmpty()
            || $filials->isEmpty()
            || $staff->isEmpty()
        ) {
            return;
        }

        $definitions = [
            [
                'document_code' => 'DEMO-24001',
                'client_phone' => '998901100001',
                'service_index' => 0,
                'document_type_index' => 0,
                'direction_index' => 0,
                'filial_index' => 0,
                'staff_index' => 0,
                'process_mode' => 'apostil',
                'service_addon_offsets' => [0, 1],
                'document_addon_offsets' => [0],
                'direction_addon_offsets' => [0],
                'discount' => 20000,
                'status_doc' => 'process',
                'payment_ratios' => [0.35],
                'payment_types' => ['cash'],
                'days_ago' => 12,
                'courier_status' => 'sent',
            ],
            [
                'document_code' => 'DEMO-24002',
                'client_phone' => '998901100002',
                'service_index' => 1,
                'document_type_index' => 1,
                'direction_index' => 2,
                'filial_index' => 0,
                'staff_index' => 1,
                'process_mode' => 'apostil',
                'service_addon_offsets' => [0],
                'document_addon_offsets' => [0, 1],
                'direction_addon_offsets' => [0, 1],
                'discount' => 10000,
                'status_doc' => 'finish',
                'payment_ratios' => [0.6, 0.4],
                'payment_types' => ['cash', 'card'],
                'days_ago' => 10,
                'courier_status' => 'accepted',
            ],
            [
                'document_code' => 'DEMO-24003',
                'client_phone' => '998901100003',
                'service_index' => 3,
                'document_type_index' => 2,
                'filial_index' => 1,
                'staff_index' => 2,
                'process_mode' => 'consul',
                'selection_mode' => 'legalization',
                'service_addon_offsets' => [0],
                'document_addon_offsets' => [1],
                'discount' => 15000,
                'status_doc' => 'process',
                'payment_ratios' => [0.5],
                'payment_types' => ['card'],
                'days_ago' => 9,
            ],
            [
                'document_code' => 'DEMO-24004',
                'client_phone' => '998901100004',
                'service_index' => 3,
                'document_type_index' => 3,
                'filial_index' => 1,
                'staff_index' => 3,
                'process_mode' => 'consul',
                'selection_mode' => 'mixed',
                'service_addon_offsets' => [0, 1],
                'document_addon_offsets' => [0],
                'discount' => 30000,
                'status_doc' => 'finish',
                'payment_ratios' => [0.5, 0.5],
                'payment_types' => ['online', 'card'],
                'days_ago' => 7,
                'courier_status' => 'returned',
            ],
            [
                'document_code' => 'DEMO-24005',
                'client_phone' => '998901100005',
                'service_index' => 2,
                'document_type_index' => 4,
                'direction_index' => 4,
                'filial_index' => 2,
                'staff_index' => 4,
                'process_mode' => 'apostil',
                'service_addon_offsets' => [1],
                'document_addon_offsets' => [1],
                'direction_addon_offsets' => [1],
                'discount' => 0,
                'status_doc' => 'process',
                'payment_ratios' => [0.4, 0.15],
                'payment_types' => ['cash', 'online'],
                'days_ago' => 6,
                'courier_status' => 'rejected',
            ],
            [
                'document_code' => 'DEMO-24006',
                'client_phone' => '998901100006',
                'service_index' => 0,
                'document_type_index' => 0,
                'filial_index' => 2,
                'staff_index' => 5,
                'process_mode' => 'consul',
                'selection_mode' => 'consul',
                'service_addon_offsets' => [0],
                'document_addon_offsets' => [0],
                'discount' => 5000,
                'status_doc' => 'finish',
                'payment_ratios' => [1],
                'payment_types' => ['cash'],
                'days_ago' => 5,
            ],
            [
                'document_code' => 'DEMO-24007',
                'client_phone' => '998901100007',
                'service_index' => 4,
                'document_type_index' => 1,
                'filial_index' => 0,
                'staff_index' => 1,
                'process_mode' => 'consul',
                'selection_mode' => 'mixed',
                'service_addon_offsets' => [0, 1],
                'document_addon_offsets' => [1],
                'discount' => 12000,
                'status_doc' => 'process',
                'payment_ratios' => [0.25],
                'payment_types' => ['admin_entry'],
                'days_ago' => 3,
                'courier_status' => 'sent',
            ],
            [
                'document_code' => 'DEMO-24008',
                'client_phone' => '998901100008',
                'service_index' => 2,
                'document_type_index' => 2,
                'direction_index' => 1,
                'filial_index' => 1,
                'staff_index' => 2,
                'process_mode' => 'apostil',
                'service_addon_offsets' => [0],
                'document_addon_offsets' => [0],
                'direction_addon_offsets' => [0, 1],
                'discount' => 25000,
                'status_doc' => 'finish',
                'payment_ratios' => [0.55, 0.45],
                'payment_types' => ['card', 'online'],
                'days_ago' => 2,
                'courier_status' => 'accepted',
            ],
        ];

        foreach ($definitions as $index => $definition) {
            $client = $clients->get($definition['client_phone']);

            if (! $client) {
                continue;
            }

            $service = $this->pick($services, $definition['service_index']);
            $documentType = $this->pick($documentTypes, $definition['document_type_index']);
            $filial = $this->pick($filials, $definition['filial_index']);
            $staffUser = $this->pick($staff, $definition['staff_index']);

            $selectedServiceAddons = $this->pickGroupItems(
                $serviceAddons,
                $service->id,
                $definition['service_addon_offsets'] ?? []
            );
            $selectedDocumentAddons = $this->pickGroupItems(
                $documentTypeAddons,
                $documentType->id,
                $definition['document_addon_offsets'] ?? []
            );

            $direction = null;
            $selectedDirectionAddons = collect();
            $apostilChargeItems = [];
            $consulChargeItems = [];
            $consulateType = null;
            $consul = null;

            if ($definition['process_mode'] === 'apostil') {
                $direction = $this->pick($directions, $definition['direction_index'] ?? 0);
                $selectedDirectionAddons = $this->pickGroupItems(
                    $directionAddons,
                    $direction->id,
                    $definition['direction_addon_offsets'] ?? []
                );
                $apostilChargeItems = [
                    $this->pick($apostilGroup1, $index),
                    $this->pick($apostilGroup2, $index + 1),
                ];
            }

            if ($definition['process_mode'] === 'consul') {
                $selectionMode = $definition['selection_mode'] ?? 'mixed';

                if (in_array($selectionMode, ['consul', 'mixed'], true)) {
                    $consul = $this->pick($consuls, $index);
                    $consulChargeItems[] = [
                        'charge_type' => 'consul',
                        'source_id' => $consul->id,
                        'price' => (float) $consul->amount,
                        'days' => (int) $consul->day,
                        'name' => $consul->name,
                    ];
                }

                if (in_array($selectionMode, ['legalization', 'mixed'], true)) {
                    $consulateType = $this->pick($consulates, $index);
                    $consulChargeItems[] = [
                        'charge_type' => 'consulate_type',
                        'source_id' => $consulateType->id,
                        'price' => (float) $consulateType->amount,
                        'days' => (int) $consulateType->day,
                        'name' => $consulateType->name,
                    ];
                }
            }

            $servicePrice = (float) $service->price;
            $serviceAddonTotal = (float) $selectedServiceAddons->sum(fn ($addon) => $addon->price);
            $documentAddonTotal = (float) $selectedDocumentAddons->sum(fn ($addon) => $addon->amount);
            $directionAddonTotal = (float) $selectedDirectionAddons->sum(fn ($addon) => $addon->amount);
            $apostilTotal = collect($apostilChargeItems)->sum(fn ($item) => $item?->price ?? 0);
            $consulTotal = collect($consulChargeItems)->sum('price');
            $addonsTotal = $serviceAddonTotal + $documentAddonTotal + $directionAddonTotal;
            $discount = (float) ($definition['discount'] ?? 0);
            $deadline = (int) $service->deadline
                + (int) $selectedServiceAddons->sum('deadline')
                + (int) $selectedDocumentAddons->sum('day')
                + (int) $selectedDirectionAddons->sum('day')
                + (int) collect($apostilChargeItems)->sum(fn ($item) => $item?->days ?? 0)
                + (int) collect($consulChargeItems)->sum('days');
            $finalPrice = max($servicePrice + $addonsTotal + $apostilTotal + $consulTotal - $discount, 0);

            $createdAt = now()->subDays((int) $definition['days_ago'])->startOfDay()->addHours(9 + ($index % 5));
            $payments = $this->buildPayments(
                $finalPrice,
                $definition['payment_ratios'] ?? [],
                $definition['payment_types'] ?? [],
                $staffUser->id,
                $createdAt
            );
            $paidAmount = collect($payments)->sum('amount');

            $document = DocumentsModel::query()->firstOrNew([
                'document_code' => $definition['document_code'],
            ]);

            $document->fill([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'document_type_id' => $documentType->id,
                'direction_type_id' => $direction?->id,
                'consulate_type_id' => $consulateType?->id,
                'service_price' => $servicePrice,
                'addons_total_price' => $addonsTotal,
                'deadline_time' => $deadline,
                'final_price' => $finalPrice,
                'paid_amount' => $paidAmount,
                'discount' => $discount,
                'user_id' => $staffUser->id,
                'description' => $this->buildDescription($definition['process_mode'], $client->name, $service->name),
                'filial_id' => $filial->id,
                'status_doc' => $definition['status_doc'],
                'process_mode' => $definition['process_mode'],
                'apostil_group1_id' => $definition['process_mode'] === 'apostil' ? $apostilChargeItems[0]?->id : null,
                'apostil_group2_id' => $definition['process_mode'] === 'apostil' ? $apostilChargeItems[1]?->id : null,
                'consul_id' => $consul?->id,
            ]);

            $document->created_at = $createdAt;
            $document->updated_at = $createdAt->copy()->addHours(2);
            $document->timestamps = false;
            $document->save();
            $document->timestamps = true;

            $document->addons()->sync(
                $selectedServiceAddons
                    ->mapWithKeys(fn ($addon) => [
                        $addon->id => [
                            'addon_price' => (float) $addon->price,
                            'addon_deadline' => (int) $addon->deadline,
                        ],
                    ])
                    ->all()
            );

            $this->replacePivotRows(
                'document_type_addons',
                $document->id,
                $selectedDocumentAddons->map(fn ($addon) => [
                    'addon_id' => $addon->id,
                    'addon_price' => (float) $addon->amount,
                ])->all()
            );

            $this->replacePivotRows(
                'document_direction_addons',
                $document->id,
                $selectedDirectionAddons->map(fn ($addon) => [
                    'addon_id' => $addon->id,
                    'addon_price' => (float) $addon->amount,
                ])->all()
            );

            DocumentProcessChargeModel::query()->where('document_id', $document->id)->delete();

            $processCharges = collect();

            if ($definition['process_mode'] === 'apostil') {
                $processCharges = collect($apostilChargeItems)
                    ->filter()
                    ->values()
                    ->map(function ($item, int $chargeIndex) use ($document, $createdAt) {
                        return [
                            'document_id' => $document->id,
                            'charge_type' => $chargeIndex === 0 ? 'apostil_group1' : 'apostil_group2',
                            'source_id' => $item->id,
                            'price' => (float) $item->price,
                            'days' => (int) $item->days,
                            'name' => $item->name,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ];
                    });
            }

            if ($definition['process_mode'] === 'consul') {
                $processCharges = collect($consulChargeItems)
                    ->map(fn (array $charge) => [
                        'document_id' => $document->id,
                        'charge_type' => $charge['charge_type'],
                        'source_id' => $charge['source_id'],
                        'price' => $charge['price'],
                        'days' => $charge['days'],
                        'name' => $charge['name'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
            }

            if ($processCharges->isNotEmpty()) {
                DocumentProcessChargeModel::query()->insert($processCharges->all());
            }

            PaymentsModel::query()->where('document_id', $document->id)->delete();

            if ($payments !== []) {
                PaymentsModel::query()->insert(
                    collect($payments)->map(fn (array $payment) => array_merge($payment, [
                        'document_id' => $document->id,
                    ]))->all()
                );
            }

            if (! empty($definition['courier_status']) && $couriers->isNotEmpty()) {
                $courier = $this->pick($couriers, $index);
                $timestamps = $this->courierTimestamps($definition['courier_status'], $createdAt);

                DocumentCourier::query()->updateOrCreate(
                    ['document_id' => $document->id],
                    [
                        'courier_id' => $courier->id,
                        'sent_by_id' => $staffUser->id,
                        'status' => $definition['courier_status'],
                        'sent_comment' => 'Demo assignment for workflow testing',
                        'courier_comment' => $definition['courier_status'] === 'accepted'
                            ? 'Courier accepted the document'
                            : ($definition['courier_status'] === 'rejected'
                                ? 'Courier rejected due to wrong address'
                                : null),
                        'return_comment' => $definition['courier_status'] === 'returned'
                            ? 'Document delivered and returned successfully'
                            : null,
                        'sent_at' => $timestamps['sent_at'],
                        'accepted_at' => $timestamps['accepted_at'],
                        'rejected_at' => $timestamps['rejected_at'],
                        'returned_at' => $timestamps['returned_at'],
                    ]
                );
            } else {
                DocumentCourier::query()->where('document_id', $document->id)->delete();
            }
        }

        $this->seedExpenses($staff, $filials);
    }

    private function itemConfig(array $config): array
    {
        return [
            'document_type_id' => (int) ($config['document_type_id'] ?? 0),
            'service_id' => (int) ($config['service_id'] ?? 0),
            'process_mode' => PackageTemplateSupport::normalizeProcessMode($config['process_mode'] ?? 'service'),
            'selection_mode' => PackageTemplateSupport::normalizeSelectionMode($config['selection_mode'] ?? null),
            'direction_type_id' => $this->nullableInt($config['direction_type_id'] ?? null),
            'apostil_group1_id' => $this->nullableInt($config['apostil_group1_id'] ?? null),
            'apostil_group2_id' => $this->nullableInt($config['apostil_group2_id'] ?? null),
            'consul_id' => $this->nullableInt($config['consul_id'] ?? null),
            'consulate_type_id' => $this->nullableInt($config['consulate_type_id'] ?? null),
            'selected_addons' => PackageTemplateSupport::normalizeSelectedAddons($config['selected_addons'] ?? []),
        ];
    }

    private function storeTemplate(array $package): void
    {
        $items = collect($package['items'] ?? [])
            ->values()
            ->map(function (array $item, int $index) {
                $pricing = PackageTemplateSupport::calculateItemPricing($item);

                return $item + [
                    'selected_addons' => $pricing['selected_addons'],
                    'base_price' => (float) $pricing['total_price'],
                    'sort_order' => $index,
                ];
            });

        if ($items->isEmpty()) {
            return;
        }

        $basePrice = (float) $items->sum('base_price');
        $promoPrice = $this->resolvePromoPrice($basePrice, $package['promo_price'] ?? null);
        $firstItem = $items->first();

        DB::transaction(function () use ($package, $items, $basePrice, $promoPrice, $firstItem) {
            $template = PackageTemplate::query()->updateOrCreate(
                ['name' => $package['name']],
                [
                    'highlight' => $package['highlight'] ?? null,
                    'description' => $package['description'] ?? null,
                    'process_mode' => $firstItem['process_mode'],
                    'selection_mode' => $firstItem['selection_mode'],
                    'document_type_id' => $firstItem['document_type_id'],
                    'service_id' => $firstItem['service_id'],
                    'direction_type_id' => $firstItem['direction_type_id'],
                    'apostil_group1_id' => $firstItem['apostil_group1_id'],
                    'apostil_group2_id' => $firstItem['apostil_group2_id'],
                    'consul_id' => $firstItem['consul_id'],
                    'consulate_type_id' => $firstItem['consulate_type_id'],
                    'selected_addons' => $firstItem['selected_addons'],
                    'base_price' => $basePrice,
                    'promo_price' => $promoPrice,
                    'sort_order' => (int) ($package['sort_order'] ?? 0),
                    'is_active' => (bool) ($package['is_active'] ?? true),
                ]
            );

            $template->items()->delete();
            $template->items()->createMany(
                $items->map(fn (array $item) => collect($item)
                    ->except('package_template_id')
                    ->all())
                    ->all()
            );
        });
    }

    private function resolvePromoPrice(float $basePrice, ?float $fixedPrice = null): float
    {
        if ($basePrice <= 0) {
            return 0;
        }

        if ($fixedPrice !== null && $fixedPrice > 0 && $fixedPrice < $basePrice) {
            return $fixedPrice;
        }

        $discounted = round($basePrice * 0.9, -3);

        if ($discounted >= $basePrice) {
            $discounted = max($basePrice - 1000, 0);
        }

        return max($discounted, 0);
    }

    private function buildDescription(string $processMode, string $clientName, string $serviceName): string
    {
        return match ($processMode) {
            'apostil' => "{$clientName} uchun apostil jarayonidagi demo hujjat. Xizmat: {$serviceName}.",
            'consul' => "{$clientName} uchun konsullik/legalizatsiya demo jarayoni. Xizmat: {$serviceName}.",
            default => "{$clientName} uchun demo xizmat hujjati.",
        };
    }

    private function buildPayments(
        float $finalPrice,
        array $ratios,
        array $paymentTypes,
        int $paidByAdminId,
        Carbon $createdAt
    ): array {
        if ($finalPrice <= 0 || $ratios === [] || $paymentTypes === []) {
            return [];
        }

        $targetTotal = round($finalPrice * array_sum($ratios), 2);
        $allocated = 0.0;
        $records = [];

        foreach ($ratios as $index => $ratio) {
            $amount = $index === array_key_last($ratios)
                ? round($targetTotal - $allocated, 2)
                : round($finalPrice * $ratio, 2);

            if ($amount <= 0) {
                continue;
            }

            $allocated += $amount;

            $timestamp = $createdAt->copy()->addHours($index + 1);

            $records[] = [
                'document_id' => 0,
                'amount' => $amount,
                'payment_type' => $paymentTypes[$index] ?? $paymentTypes[array_key_last($paymentTypes)],
                'paid_by_admin_id' => $paidByAdminId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $records;
    }

    private function courierTimestamps(string $status, Carbon $createdAt): array
    {
        $sentAt = $createdAt->copy()->addHours(3);

        return [
            'sent_at' => $sentAt,
            'accepted_at' => $status === 'accepted' || $status === 'returned'
                ? $sentAt->copy()->addHours(2)
                : null,
            'rejected_at' => $status === 'rejected'
                ? $sentAt->copy()->addHours(4)
                : null,
            'returned_at' => $status === 'returned'
                ? $sentAt->copy()->addDay()
                : null,
        ];
    }

    private function replacePivotRows(string $table, int $documentId, array $rows): void
    {
        DB::table($table)->where('document_id', $documentId)->delete();

        if ($rows === []) {
            return;
        }

        DB::table($table)->insert(
            collect($rows)->map(fn (array $row) => [
                'document_id' => $documentId,
                'addon_id' => $row['addon_id'],
                'addon_price' => $row['addon_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );
    }

    private function pick(Collection $items, int $offset)
    {
        return $items->values()->get($offset % $items->count());
    }

    private function pickGroupItems(Collection $groupedItems, int $groupId, array $offsets): Collection
    {
        $items = $groupedItems->get($groupId, collect())->values();

        if ($items->isEmpty() || $offsets === []) {
            return collect();
        }

        return collect($offsets)
            ->map(fn (int $offset) => $items->get($offset % $items->count()))
            ->filter()
            ->unique('id')
            ->values();
    }

    private function seedExpenses(Collection $staff, Collection $filials): void
    {
        $records = [
            [
                'user_id' => $this->pick($staff, 0)->id,
                'filial_id' => $this->pick($filials, 0)->id,
                'amount' => 180000,
                'description' => 'Demo: ofis uchun kantselyariya xarajati',
            ],
            [
                'user_id' => $this->pick($staff, 1)->id,
                'filial_id' => $this->pick($filials, 1)->id,
                'amount' => 250000,
                'description' => "Demo: filial bo'yicha transport xarajati",
            ],
            [
                'user_id' => $this->pick($staff, 2)->id,
                'filial_id' => $this->pick($filials, 2)->id,
                'amount' => 320000,
                'description' => 'Demo: reklama va mijoz jalb qilish xarajati',
            ],
        ];

        foreach ($records as $record) {
            ExpenseAdminModel::query()->updateOrCreate(
                ['description' => $record['description']],
                [
                    'user_id' => $record['user_id'],
                    'filial_id' => $record['filial_id'],
                    'amount' => $record['amount'],
                ]
            );
        }
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
