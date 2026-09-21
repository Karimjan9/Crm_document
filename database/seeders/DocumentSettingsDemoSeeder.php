<?php

namespace Database\Seeders;

use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DocumentDirectionAdditionModel;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\PackageTemplate;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Client demonstration data for Hujjat sozlamalari.
 *
 * All records have a [DEMO] prefix. The seeder is safe to run repeatedly:
 * it updates only those named demo records and never truncates tables.
 *
 * Production command:
 * php artisan db:seed --class=Database\\Seeders\\DocumentSettingsDemoSeeder --force
 */
class DocumentSettingsDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $documents = $this->seedDocumentTypes();
            $directions = $this->seedDirections();
            $apostil = $this->seedApostilOptions();
            $consuls = $this->seedConsulCategories();
            $consulateTypes = $this->seedConsulateTypes();
            [$services, $addons] = $this->seedServices();

            $documentAddons = $this->seedDocumentAddons($documents);
            $directionAddons = $this->seedDirectionAddons($directions);

            $this->seedPackages(
                $documents,
                $directions,
                $apostil,
                $consuls,
                $consulateTypes,
                $services,
                $addons,
                $documentAddons,
                $directionAddons,
            );
        });
    }

    private function seedDocumentTypes(): array
    {
        $records = [
            'passport' => ['name' => '[DEMO] Xorijga chiqish pasporti', 'description' => 'Mijozga apostil va tarjima oqimini ko‘rsatish uchun demo hujjat.'],
            'diploma' => ['name' => '[DEMO] Diplom va ilova', 'description' => 'O‘qishga topshirish uchun legalizatsiya demo hujjati.'],
            'birth' => ['name' => '[DEMO] Tug‘ilganlik guvohnomasi', 'description' => 'Oilaviy hujjatlar oqimi uchun demo ma’lumot.'],
            'marriage' => ['name' => '[DEMO] Nikoh guvohnomasi', 'description' => 'Konsullik xizmati bilan sinash uchun demo hujjat.'],
            'employment' => ['name' => '[DEMO] Ish joyidan ma’lumotnoma', 'description' => 'Ish vizasi paketi uchun demo hujjat.'],
        ];

        return collect($records)->mapWithKeys(fn (array $record, string $key) => [
            $key => DocumentTypeModel::query()->updateOrCreate(['name' => $record['name']], ['description' => $record['description']]),
        ])->all();
    }

    private function seedDirections(): array
    {
        $records = [
            'education' => ['name' => '[DEMO] Ta’lim', 'description' => 'O‘qish va universitet hujjatlari oqimi.'],
            'health' => ['name' => '[DEMO] Sog‘liqni saqlash', 'description' => 'Tibbiy ma’lumotnomalar oqimi.'],
            'justice' => ['name' => '[DEMO] Adliya', 'description' => 'Huquqiy va notarial jarayonlar oqimi.'],
            'court' => ['name' => '[DEMO] Sud hujjatlari', 'description' => 'Sud organlariga oid hujjatlar oqimi.'],
            'foreign' => ['name' => '[DEMO] Tashqi ishlar', 'description' => 'Tashqi ishlar bo‘yicha apostil oqimi.'],
            'other' => ['name' => '[DEMO] Boshqa yo‘nalish', 'description' => 'Qolgan demo hujjatlar uchun yo‘nalish.'],
        ];

        return collect($records)->mapWithKeys(fn (array $record, string $key) => [
            $key => DirectionTypeModel::query()->updateOrCreate(['name' => $record['name']], ['description' => $record['description']]),
        ])->all();
    }

    private function seedApostilOptions(): array
    {
        $records = [
            'original' => ['group_id' => 1, 'name' => '[DEMO] Asl hujjat', 'price' => 60000, 'days' => 1],
            'notary' => ['group_id' => 1, 'name' => '[DEMO] Notarial tasdiq', 'price' => 85000, 'days' => 1],
            'qr' => ['group_id' => 2, 'name' => '[DEMO] QR kodli apostil', 'price' => 70000, 'days' => 2],
            'plain' => ['group_id' => 2, 'name' => '[DEMO] QR kodsiz apostil', 'price' => 50000, 'days' => 1],
        ];

        return collect($records)->mapWithKeys(fn (array $record, string $key) => [
            $key => ApostilStatikModel::query()->updateOrCreate(
                ['group_id' => $record['group_id'], 'name' => $record['name']],
                ['price' => $record['price'], 'days' => $record['days']],
            ),
        ])->all();
    }

    private function seedConsulCategories(): array
    {
        $records = [
            'education' => ['name' => '[DEMO] Ta’lim va adliya', 'amount' => 150000, 'day' => 3],
            'general' => ['name' => '[DEMO] Umumiy hujjatlar', 'amount' => 250000, 'day' => 5],
            'foreigners' => ['name' => '[DEMO] Chet el fuqarolari', 'amount' => 100000, 'day' => 2],
            'other' => ['name' => '[DEMO] Boshqa konsullik ishlari', 'amount' => 120000, 'day' => 3],
        ];

        return collect($records)->mapWithKeys(fn (array $record, string $key) => [
            $key => ConsulModel::query()->updateOrCreate(['name' => $record['name']], ['amount' => $record['amount'], 'day' => $record['day']]),
        ])->all();
    }

    private function seedConsulateTypes(): array
    {
        $records = [
            'uae' => ['name' => '[DEMO] BAA konsullik xizmati', 'description' => 'BAA uchun hujjatlarni legalizatsiya qilish demo xizmati.', 'amount' => 220000, 'day' => 6],
            'usa' => ['name' => '[DEMO] AQSh konsullik xizmati', 'description' => 'AQSh uchun hujjatlarni legalizatsiya qilish demo xizmati.', 'amount' => 260000, 'day' => 7],
            'uk' => ['name' => '[DEMO] Buyuk Britaniya konsullik xizmati', 'description' => 'Buyuk Britaniya uchun demo konsullik xizmati.', 'amount' => 280000, 'day' => 7],
            'turkey' => ['name' => '[DEMO] Turkiya konsullik xizmati', 'description' => 'Turkiya uchun demo konsullik xizmati.', 'amount' => 170000, 'day' => 5],
            'russia' => ['name' => '[DEMO] Rossiya konsullik xizmati', 'description' => 'Rossiya uchun demo konsullik xizmati.', 'amount' => 150000, 'day' => 4],
            'other' => ['name' => '[DEMO] Boshqa konsullik xizmati', 'description' => 'Boshqa davlat uchun demo konsullik xizmati.', 'amount' => 190000, 'day' => 6],
        ];

        return collect($records)->mapWithKeys(fn (array $record, string $key) => [
            $key => ConsulationTypeModel::query()->updateOrCreate(
                ['name' => $record['name']],
                ['description' => $record['description'], 'amount' => $record['amount'], 'day' => $record['day']],
            ),
        ])->all();
    }

    private function seedServices(): array
    {
        $records = [
            'translation' => [
                'name' => '[DEMO] Tarjima xizmati', 'description' => 'Hujjatni tarjima qilish va tayyorlash demo xizmati.', 'price' => 120000, 'deadline' => 2,
                'addons' => [
                    'notary' => ['name' => '[DEMO] Notarial tasdiq', 'description' => 'Tarjimaning notarial tasdig‘i.', 'price' => 45000, 'deadline' => 1],
                    'express' => ['name' => '[DEMO] Ekspress navbat', 'description' => 'Ustuvor ko‘rib chiqish.', 'price' => 60000, 'deadline' => 0],
                ],
            ],
            'notary' => [
                'name' => '[DEMO] Notarial tasdiqlash', 'description' => 'Asl hujjat yoki nusxani tasdiqlash demo xizmati.', 'price' => 95000, 'deadline' => 1,
                'addons' => [
                    'copy' => ['name' => '[DEMO] Qo‘shimcha nusxa', 'description' => 'Tasdiqlangan qo‘shimcha nusxa.', 'price' => 25000, 'deadline' => 0],
                    'archive' => ['name' => '[DEMO] Arxiv nusxasi', 'description' => 'Raqamli yoki bosma arxiv nusxasi.', 'price' => 35000, 'deadline' => 1],
                ],
            ],
            'apostil' => [
                'name' => '[DEMO] Apostil tayyorlash', 'description' => 'Apostil uchun hujjatni to‘liq tayyorlash demo xizmati.', 'price' => 180000, 'deadline' => 3,
                'addons' => [
                    'fee' => ['name' => '[DEMO] Davlat boji', 'description' => 'Apostil bo‘yicha rasmiy yig‘im.', 'price' => 75000, 'deadline' => 1],
                    'qr' => ['name' => '[DEMO] QR tekshiruvi', 'description' => 'QR va reyestr tekshiruvi.', 'price' => 30000, 'deadline' => 0],
                ],
            ],
            'legalization' => [
                'name' => '[DEMO] Legalizatsiya xizmati', 'description' => 'Konsullik va legalizatsiya bo‘yicha demo xizmat.', 'price' => 260000, 'deadline' => 5,
                'addons' => [
                    'translation' => ['name' => '[DEMO] Tarjima paketi', 'description' => 'Legalizatsiya uchun tarjima ishlari.', 'price' => 80000, 'deadline' => 2],
                    'embassy' => ['name' => '[DEMO] Elchixona navbati', 'description' => 'Elchixona topshiruvi uchun navbat.', 'price' => 95000, 'deadline' => 1],
                ],
            ],
            'courier' => [
                'name' => '[DEMO] Kuryer xizmati', 'description' => 'Hujjatlarni mijozga yetkazib berish demo xizmati.', 'price' => 70000, 'deadline' => 1,
                'addons' => [
                    'city' => ['name' => '[DEMO] Shahar bo‘ylab yetkazish', 'description' => 'Shahar ichida tezkor yetkazish.', 'price' => 20000, 'deadline' => 0],
                    'region' => ['name' => '[DEMO] Viloyatga jo‘natish', 'description' => 'Viloyatga yuborish va kuzatish.', 'price' => 45000, 'deadline' => 2],
                ],
            ],
        ];

        $services = [];
        $addons = [];
        foreach ($records as $serviceKey => $record) {
            $service = ServicesModel::query()->updateOrCreate(
                ['name' => $record['name']],
                ['description' => $record['description'], 'price' => $record['price'], 'deadline' => $record['deadline']],
            );
            $services[$serviceKey] = $service;

            foreach ($record['addons'] as $addonKey => $addon) {
                $addons[$serviceKey . '.' . $addonKey] = ServiceAddonModel::query()->updateOrCreate(
                    ['service_id' => $service->id, 'name' => $addon['name']],
                    ['description' => $addon['description'], 'price' => $addon['price'], 'deadline' => $addon['deadline']],
                );
            }
        }

        return [$services, $addons];
    }

    private function seedDocumentAddons(array $documents): array
    {
        $addons = [];
        foreach ($documents as $key => $document) {
            $addons[$key . '.copy'] = DocumentTypeAdditionModel::query()->updateOrCreate(
                ['document_type_id' => $document->id, 'name' => '[DEMO] Notarial nusxa'],
                ['description' => 'Demo hujjat uchun qo‘shimcha notarial nusxa.', 'amount' => 30000, 'day' => 1],
            );
            $addons[$key . '.scan'] = DocumentTypeAdditionModel::query()->updateOrCreate(
                ['document_type_id' => $document->id, 'name' => '[DEMO] Skaner va PDF'],
                ['description' => 'Demo hujjatning raqamli nusxasi.', 'amount' => 15000, 'day' => 0],
            );
        }

        return $addons;
    }

    private function seedDirectionAddons(array $directions): array
    {
        $addons = [];
        foreach ($directions as $key => $direction) {
            $addons[$key . '.fee'] = DocumentDirectionAdditionModel::query()->updateOrCreate(
                ['document_direction_id' => $direction->id, 'name' => '[DEMO] Davlat boji'],
                ['description' => 'Yo‘nalish bo‘yicha demo davlat yig‘imi.', 'amount' => 40000, 'day' => 1],
            );
            $addons[$key . '.express'] = DocumentDirectionAdditionModel::query()->updateOrCreate(
                ['document_direction_id' => $direction->id, 'name' => '[DEMO] Tezkor ko‘rib chiqish'],
                ['description' => 'Jarayonni tezlashtirish uchun demo xizmat.', 'amount' => 55000, 'day' => 0],
            );
        }

        return $addons;
    }

    private function seedPackages(
        array $documents,
        array $directions,
        array $apostil,
        array $consuls,
        array $consulateTypes,
        array $services,
        array $addons,
        array $documentAddons,
        array $directionAddons,
    ): void {
        $items = [
            'apostil_education' => $this->item(
                $documents['passport'], $services['apostil'], 'apostil',
                direction: $directions['education'], apostilOne: $apostil['original'], apostilTwo: $apostil['qr'],
                selectedAddons: [$documentAddons['passport.copy'], $directionAddons['education.fee'], $addons['apostil.fee']],
            ),
            'apostil_justice' => $this->item(
                $documents['birth'], $services['apostil'], 'apostil',
                direction: $directions['justice'], apostilOne: $apostil['notary'], apostilTwo: $apostil['plain'],
                selectedAddons: [$documentAddons['birth.scan'], $directionAddons['justice.express'], $addons['apostil.qr']],
            ),
            'consul_uae' => $this->item(
                $documents['diploma'], $services['legalization'], 'consul', selectionMode: 'mixed',
                consul: $consuls['education'], consulateType: $consulateTypes['uae'],
                selectedAddons: [$addons['legalization.translation'], $addons['legalization.embassy']],
            ),
            'consul_turkey' => $this->item(
                $documents['marriage'], $services['legalization'], 'consul', selectionMode: 'legalization',
                consulateType: $consulateTypes['turkey'], selectedAddons: [$addons['legalization.translation']],
            ),
            'translation' => $this->item(
                $documents['employment'], $services['translation'], 'service',
                selectedAddons: [$documentAddons['employment.scan'], $addons['translation.notary']],
            ),
            'notary' => $this->item(
                $documents['marriage'], $services['notary'], 'service',
                selectedAddons: [$documentAddons['marriage.copy'], $addons['notary.copy']],
            ),
        ];

        $packages = [
            ['DEMO-PKG-001', '[DEMO] Apostil start paketi', 'Apostil jarayonini tez ko‘rsatish uchun bitta hujjatli paket.', ['apostil_education']],
            ['DEMO-PKG-002', '[DEMO] O‘qishga topshirish paketi', 'Diplom, apostil va BAA konsullik xizmatlari bir paketda.', ['apostil_education', 'consul_uae']],
            ['DEMO-PKG-003', '[DEMO] Tarjima va notarius', 'Ish joyidan ma’lumotnomani tarjima qilish va notarial tasdiqlash paketi.', ['translation']],
            ['DEMO-PKG-004', '[DEMO] Konsullik tayyorlov paketi', 'Nikoh hujjatini Turkiya legalizatsiyasiga tayyorlash paketi.', ['consul_turkey']],
            ['DEMO-PKG-005', '[DEMO] Oilaviy hujjatlar paketi', 'Oilaviy hujjatlar uchun apostil va notarial xizmatlar paketi.', ['apostil_justice', 'notary']],
            ['DEMO-PKG-006', '[DEMO] Ish vizasi paketi', 'Ish joyi ma’lumotnomasi tarjimasi va konsullik tayyorlovi.', ['translation', 'consul_uae']],
            ['DEMO-PKG-007', '[DEMO] Ekspress hujjatlar paketi', 'Apostil, legalizatsiya va tarjima birlashtirilgan tezkor paket.', ['apostil_education', 'consul_turkey', 'translation']],
            ['DEMO-PKG-008', '[DEMO] To‘liq hujjatlar paketi', 'Mijozga barcha asosiy oqimlarni ko‘rsatish uchun kengaytirilgan demo paket.', ['apostil_education', 'apostil_justice', 'consul_uae', 'consul_turkey', 'translation', 'notary']],
        ];

        foreach ($packages as $index => [$code, $name, $description, $itemKeys]) {
            $packageItems = collect($itemKeys)->map(fn (string $key, int $sortOrder) => $items[$key] + ['sort_order' => $sortOrder]);
            $first = $packageItems->first();
            $basePrice = round((float) $packageItems->sum('base_price'), 2);
            $standardPrice = $basePrice;
            $promoPrice = round($standardPrice * 0.9, 2);
            $deadline = max(1, (int) $packageItems->max('deadline_days'));

            $package = PackageTemplate::query()->updateOrCreate(
                ['product_code' => $code],
                [
                    'name' => $name,
                    'highlight' => $packageItems->count() . ' ta demo xizmat',
                    'description' => $description,
                    'process_mode' => $first['process_mode'],
                    'selection_mode' => $first['selection_mode'],
                    'document_type_id' => $first['document_type_id'],
                    'service_id' => $first['service_id'],
                    'direction_type_id' => $first['direction_type_id'],
                    'apostil_group1_id' => $first['apostil_group1_id'],
                    'apostil_group2_id' => $first['apostil_group2_id'],
                    'consul_id' => $first['consul_id'],
                    'consulate_type_id' => $first['consulate_type_id'],
                    'selected_addons' => $first['selected_addons'],
                    'base_price' => $basePrice,
                    'promo_price' => $promoPrice,
                    'standard_price' => $standardPrice,
                    'express_price' => round($standardPrice * 1.2, 2),
                    'standard_deadline_days' => $deadline,
                    'express_deadline_days' => max(1, (int) ceil($deadline / 2)),
                    'margin_percent' => 25,
                    'delivery_type' => 'pickup',
                    'is_active' => true,
                    'is_sellable' => true,
                    'sort_order' => 900 + $index,
                ],
            );

            $package->items()->delete();
            $package->items()->createMany($packageItems->map(fn (array $item) => collect($item)->except('deadline_days')->all())->all());

            $package->packageAddons()->delete();
            $packageItems->pluck('selected_addons')->flatten(1)
                ->where('sourceType', 'service')
                ->pluck('id')
                ->unique()
                ->values()
                ->each(fn (int $addonId, int $sortOrder) => $package->packageAddons()->create([
                    'service_addon_id' => $addonId,
                    'is_included' => false,
                    'quantity' => 1,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ]));
        }
    }

    private function item(
        DocumentTypeModel $document,
        ServicesModel $service,
        string $processMode,
        ?DirectionTypeModel $direction = null,
        ?ApostilStatikModel $apostilOne = null,
        ?ApostilStatikModel $apostilTwo = null,
        ?ConsulModel $consul = null,
        ?ConsulationTypeModel $consulateType = null,
        ?string $selectionMode = null,
        array $selectedAddons = [],
    ): array {
        $selected = collect($selectedAddons)->filter();
        $basePrice = (float) $service->price + $selected->sum(fn ($addon) => (float) ($addon->amount ?? $addon->price ?? 0));
        $deadline = (int) $service->deadline + $selected->sum(fn ($addon) => (int) ($addon->day ?? $addon->deadline ?? 0));

        foreach ([$apostilOne, $apostilTwo] as $option) {
            if ($option) {
                $basePrice += (float) $option->price;
                $deadline += (int) $option->days;
            }
        }

        if ($consul) {
            $basePrice += (float) $consul->amount;
            $deadline += (int) $consul->day;
        }

        if ($consulateType) {
            $basePrice += (float) $consulateType->amount;
            $deadline += (int) $consulateType->day;
        }

        return [
            'document_type_id' => $document->id,
            'service_id' => $service->id,
            'process_mode' => $processMode,
            'selection_mode' => $selectionMode,
            'direction_type_id' => $direction?->id,
            'apostil_group1_id' => $apostilOne?->id,
            'apostil_group2_id' => $apostilTwo?->id,
            'consul_id' => $consul?->id,
            'consulate_type_id' => $consulateType?->id,
            'selected_addons' => $selected->map(function ($addon): array {
                $sourceType = match (true) {
                    $addon instanceof DocumentTypeAdditionModel => 'document',
                    $addon instanceof DocumentDirectionAdditionModel => 'direction',
                    default => 'service',
                };

                return ['sourceType' => $sourceType, 'id' => $addon->id];
            })->values()->all(),
            'base_price' => round($basePrice, 2),
            'deadline_days' => max(0, $deadline),
        ];
    }
}
