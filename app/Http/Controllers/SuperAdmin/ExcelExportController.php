<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\User;
use App\Support\ExcelWorkbookBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ExcelExportController extends Controller
{
    public function __construct(private ExcelWorkbookBuilder $builder)
    {
    }

    public function download(string $dataset): Response
    {
        abort_unless(in_array($dataset, ['clients', 'documents', 'employees', 'all'], true), 404);

        $payload = match ($dataset) {
            'clients' => $this->buildClientsWorkbook(),
            'documents' => $this->buildDocumentsWorkbook(),
            'employees' => $this->buildEmployeesWorkbook(),
            default => $this->buildFullWorkbook(),
        };

        return response($this->builder->build($payload['title'], $payload['sheets']), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $payload['filename'] . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
            'Expires' => '0',
        ]);
    }

    private function buildClientsWorkbook(): array
    {
        return [
            'title' => 'Global Voice CRM - Mijozlar bazasi',
            'filename' => $this->makeFilename('mijozlar-bazasi'),
            'sheets' => $this->clientSheets(),
        ];
    }

    private function buildDocumentsWorkbook(): array
    {
        return [
            'title' => 'Global Voice CRM - Dokumentlar bazasi',
            'filename' => $this->makeFilename('dokumentlar-bazasi'),
            'sheets' => $this->documentSheets(),
        ];
    }

    private function buildEmployeesWorkbook(): array
    {
        return [
            'title' => 'Global Voice CRM - Xodimlar bazasi',
            'filename' => $this->makeFilename('xodimlar-bazasi'),
            'sheets' => $this->employeeSheets(),
        ];
    }

    private function buildFullWorkbook(): array
    {
        return [
            'title' => 'Global Voice CRM - To`liq Excel eksport',
            'filename' => $this->makeFilename('barcha-bazalar'),
            'sheets' => [
                ...$this->clientSheets(),
                ...$this->documentSheets(),
                ...$this->employeeSheets(),
            ],
        ];
    }

    private function clientSheets(): array
    {
        $clients = ClientsModel::with([
            'documents' => function ($query) {
                $query->with([
                    'service:id,name',
                    'documentType:id,name',
                    'directionType:id,name',
                    'consulateType:id,name',
                    'filial:id,name,code',
                    'user' => fn ($userQuery) => $userQuery->withTrashed()->with('roles:id,name'),
                ])->orderByDesc('created_at');
            },
        ])->orderBy('id')->get();

        $summaryRows = $clients->map(function (ClientsModel $client) {
            $documents = $client->documents;
            $latestDocument = $documents->first();
            $totalFinal = (float) $documents->sum('final_price');
            $totalPaid = (float) $documents->sum('paid_amount');

            return [
                $this->intCell($client->id),
                $this->wrapCell($client->name),
                $this->textCell($client->phone_number),
                $this->intCell($documents->count()),
                $this->intCell($documents->where('status_doc', '!=', 'finish')->count()),
                $this->intCell($documents->where('status_doc', 'finish')->count()),
                $this->wrapCell($this->implodeUnique($documents->pluck('document_code'))),
                $this->wrapCell($this->implodeUnique($documents->pluck('documentType.name'))),
                $this->wrapCell($this->implodeUnique($documents->pluck('service.name'))),
                $this->wrapCell($this->implodeUnique($documents->pluck('filial.name'))),
                $this->wrapCell($this->implodeUnique($documents->map(fn ($document) => $this->personLabel($document->user)))),
                $this->moneyCell($totalFinal),
                $this->moneyCell($totalPaid),
                $this->moneyCell($totalFinal - $totalPaid),
                $this->textCell(optional($latestDocument)->document_code),
                $this->textCell(optional($latestDocument)->status_doc),
                $this->textCell($this->formatDate(optional($latestDocument)->created_at)),
                $this->wrapCell((string) ($client->description ?? '')),
                $this->textCell($this->formatDate($client->created_at)),
            ];
        });

        $detailRows = $clients->flatMap(function (ClientsModel $client) {
            return $client->documents->map(function (DocumentsModel $document) use ($client) {
                return [
                    $this->intCell($client->id),
                    $this->wrapCell($client->name),
                    $this->textCell($client->phone_number),
                    $this->textCell($document->document_code),
                    $this->textCell($document->status_doc),
                    $this->wrapCell(optional($document->service)->name),
                    $this->wrapCell(optional($document->documentType)->name),
                    $this->wrapCell(optional($document->directionType)->name),
                    $this->wrapCell(optional($document->consulateType)->name),
                    $this->wrapCell(optional($document->filial)->name),
                    $this->wrapCell($this->personLabel($document->user)),
                    $this->moneyCell($document->final_price),
                    $this->moneyCell($document->paid_amount),
                    $this->moneyCell(((float) $document->final_price) - ((float) $document->paid_amount)),
                    $this->textCell($this->formatDate($document->created_at)),
                    $this->wrapCell((string) ($document->description ?? '')),
                ];
            });
        });

        return [
            [
                'name' => 'Mijozlar',
                'title' => 'Mijozlar bazasi',
                'subtitle' => 'Mijozlar, hujjatlar statistikasi va asosiy relation ma`lumotlari.',
                'columns' => [48, 170, 110, 58, 62, 62, 160, 140, 140, 130, 150, 88, 88, 88, 92, 90, 110, 180, 110],
                'headings' => [
                    'ID',
                    'Mijoz',
                    'Telefon',
                    'Jami hujjat',
                    'Aktiv',
                    'Yakunlangan',
                    'Hujjat kodlari',
                    'Hujjat turlari',
                    'Xizmatlar',
                    'Filiallar',
                    'Mas`ul xodimlar',
                    'Jami summa',
                    'Jami to`langan',
                    'Qoldiq',
                    'Oxirgi kod',
                    'Oxirgi status',
                    'Oxirgi sana',
                    'Izoh',
                    'Yaratilgan',
                ],
                'rows' => $summaryRows->all(),
            ],
            [
                'name' => 'Mijoz hujjatlar',
                'title' => 'Mijozlarga biriktirilgan hujjatlar',
                'subtitle' => 'Har bir mijozning hujjatlari alohida satrlarda.',
                'columns' => [48, 170, 110, 95, 85, 120, 130, 120, 120, 120, 150, 88, 88, 88, 110, 180],
                'headings' => [
                    'Mijoz ID',
                    'Mijoz',
                    'Telefon',
                    'Kod',
                    'Status',
                    'Xizmat',
                    'Hujjat turi',
                    'Yo`nalish',
                    'Konsullik',
                    'Filial',
                    'Mas`ul xodim',
                    'Yakuniy summa',
                    'To`langan',
                    'Qoldiq',
                    'Yaratilgan',
                    'Izoh',
                ],
                'rows' => $detailRows->all(),
            ],
        ];
    }

    private function documentSheets(): array
    {
        $documents = DocumentsModel::with([
            'client:id,name,phone_number,description',
            'service:id,name,price,deadline',
            'documentType:id,name',
            'directionType:id,name',
            'consulateType:id,name',
            'filial:id,name,code',
            'user' => fn ($query) => $query->withTrashed()->with('roles:id,name'),
            'addons:id,name,price,deadline',
            'document_type_addons:id,name,amount,day',
            'document_direction_addons:id,name,amount,day',
            'payments.paidByAdmin:id,name,login',
            'files:id,document_id,original_name,file_path,file_type,file_size,created_at',
            'processCharges:id,document_id,charge_type,source_id,price,days,name,created_at,updated_at',
            'courierAssignment.courier:id,name,login,deleted_at',
            'courierAssignment.sentBy:id,name,login,deleted_at',
        ])->orderByDesc('id')->get();

        $summaryRows = $documents->map(function (DocumentsModel $document) {
            $serviceAddons = $document->addons->map(function ($addon) {
                $price = $addon->pivot?->addon_price;
                $deadline = $addon->pivot?->addon_deadline;

                return trim($addon->name . ' | ' . $this->formatAmountText($price) . ' | ' . $this->formatDaysText($deadline));
            });

            $typeAddons = $document->document_type_addons->map(function ($addon) {
                return trim($addon->name . ' | ' . $this->formatAmountText($addon->pivot?->addon_price ?? $addon->amount) . ' | ' . $this->formatDaysText($addon->day));
            });

            $directionAddons = $document->document_direction_addons->map(function ($addon) {
                return trim($addon->name . ' | ' . $this->formatAmountText($addon->pivot?->addon_price ?? $addon->amount) . ' | ' . $this->formatDaysText($addon->day));
            });

            $payments = $document->payments->map(function ($payment) {
                return trim($payment->payment_type . ' | ' . $this->formatAmountText($payment->amount) . ' | ' . $this->formatDate($payment->created_at));
            });

            $processCharges = $document->processCharges->map(function ($charge) {
                return trim(($charge->name ?: $charge->charge_type) . ' | ' . $this->formatAmountText($charge->price) . ' | ' . $this->formatDaysText($charge->days));
            });

            return [
                $this->intCell($document->id),
                $this->textCell($document->document_code),
                $this->textCell($document->status_doc),
                $this->wrapCell(optional($document->client)->name),
                $this->textCell(optional($document->client)->phone_number),
                $this->wrapCell(optional($document->filial)->name),
                $this->wrapCell(optional($document->service)->name),
                $this->wrapCell(optional($document->documentType)->name),
                $this->wrapCell(optional($document->directionType)->name),
                $this->wrapCell(optional($document->consulateType)->name),
                $this->textCell($document->process_mode),
                $this->wrapCell($this->personLabel($document->user)),
                $this->wrapCell($this->roleLabels($document->user)),
                $this->moneyCell($document->service_price),
                $this->moneyCell($document->addons_total_price),
                $this->moneyCell($document->discount),
                $this->moneyCell($document->final_price),
                $this->moneyCell($document->paid_amount),
                $this->moneyCell(((float) $document->final_price) - ((float) $document->paid_amount)),
                $this->intCell($document->deadline_time),
                $this->textCell($document->deadline_remaining),
                $this->wrapCell($this->implodeUnique($serviceAddons)),
                $this->wrapCell($this->implodeUnique($typeAddons)),
                $this->wrapCell($this->implodeUnique($directionAddons)),
                $this->wrapCell($this->implodeUnique($processCharges)),
                $this->wrapCell($this->implodeUnique($payments)),
                $this->intCell($document->files->count()),
                $this->wrapCell($this->implodeUnique($document->files->pluck('original_name'))),
                $this->textCell(optional($document->courierAssignment)->status),
                $this->wrapCell($this->personLabel(optional($document->courierAssignment)->courier)),
                $this->wrapCell((string) ($document->description ?? '')),
                $this->textCell($this->formatDate($document->created_at)),
                $this->textCell($this->formatDate($document->updated_at)),
            ];
        });

        $paymentRows = $documents->flatMap(function (DocumentsModel $document) {
            return $document->payments->map(function ($payment) use ($document) {
                return [
                    $this->intCell($payment->id),
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->wrapCell(optional($document->filial)->name),
                    $this->textCell($payment->payment_type),
                    $this->moneyCell($payment->amount),
                    $this->wrapCell($this->personLabel($payment->paidByAdmin)),
                    $this->textCell($this->formatDate($payment->created_at)),
                ];
            });
        });

        $fileRows = $documents->flatMap(function (DocumentsModel $document) {
            return $document->files->map(function ($file) use ($document) {
                return [
                    $this->intCell($file->id),
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->wrapCell($file->original_name),
                    $this->textCell($file->file_type),
                    $this->moneyCell($this->bytesToMegabytes($file->file_size)),
                    $this->wrapCell($file->file_path),
                    $this->textCell($this->formatDate($file->created_at)),
                ];
            });
        });

        $courierRows = $documents->filter(fn (DocumentsModel $document) => $document->courierAssignment)
            ->map(function (DocumentsModel $document) {
                $assignment = $document->courierAssignment;

                return [
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->wrapCell(optional($document->filial)->name),
                    $this->wrapCell($this->personLabel($assignment->courier)),
                    $this->wrapCell($this->personLabel($assignment->sentBy)),
                    $this->textCell($assignment->status),
                    $this->wrapCell((string) ($assignment->sent_comment ?? '')),
                    $this->wrapCell((string) ($assignment->courier_comment ?? '')),
                    $this->wrapCell((string) ($assignment->return_comment ?? '')),
                    $this->textCell($this->formatDate($assignment->sent_at)),
                    $this->textCell($this->formatDate($assignment->accepted_at)),
                    $this->textCell($this->formatDate($assignment->rejected_at)),
                    $this->textCell($this->formatDate($assignment->returned_at)),
                ];
            });

        $processRows = $documents->flatMap(function (DocumentsModel $document) {
            return $document->processCharges->map(function ($charge) use ($document) {
                return [
                    $this->intCell($charge->id),
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->textCell($charge->charge_type),
                    $this->intCell($charge->source_id),
                    $this->wrapCell($charge->name),
                    $this->moneyCell($charge->price),
                    $this->intCell($charge->days),
                    $this->textCell($this->formatDate($charge->created_at)),
                    $this->textCell($this->formatDate($charge->updated_at)),
                ];
            });
        });

        $addonRows = $documents->flatMap(function (DocumentsModel $document) {
            $serviceRows = $document->addons->map(function ($addon) use ($document) {
                return [
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->textCell('Service addon'),
                    $this->wrapCell($addon->name),
                    $this->moneyCell($addon->pivot?->addon_price),
                    $this->intCell($addon->pivot?->addon_deadline),
                    $this->wrapCell((string) ($addon->description ?? '')),
                ];
            });

            $typeRows = $document->document_type_addons->map(function ($addon) use ($document) {
                return [
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->textCell('Document type addon'),
                    $this->wrapCell($addon->name),
                    $this->moneyCell($addon->pivot?->addon_price ?? $addon->amount),
                    $this->intCell($addon->day),
                    $this->wrapCell((string) ($addon->description ?? '')),
                ];
            });

            $directionRows = $document->document_direction_addons->map(function ($addon) use ($document) {
                return [
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->textCell('Direction addon'),
                    $this->wrapCell($addon->name),
                    $this->moneyCell($addon->pivot?->addon_price ?? $addon->amount),
                    $this->intCell($addon->day),
                    $this->wrapCell((string) ($addon->description ?? '')),
                ];
            });

            return $serviceRows->concat($typeRows)->concat($directionRows);
        });

        return [
            [
                'name' => 'Dokumentlar',
                'title' => 'Dokumentlar bazasi',
                'subtitle' => 'Asosiy dokumentlar jadvali, relationlar va moliyaviy holat.',
                'columns' => [45, 92, 78, 150, 105, 118, 118, 124, 118, 118, 92, 140, 118, 78, 78, 78, 82, 82, 82, 64, 108, 180, 170, 170, 170, 170, 58, 150, 78, 130, 180, 112, 112],
                'headings' => [
                    'ID',
                    'Kod',
                    'Status',
                    'Mijoz',
                    'Telefon',
                    'Filial',
                    'Xizmat',
                    'Hujjat turi',
                    'Yo`nalish',
                    'Konsullik',
                    'Jarayon',
                    'Mas`ul xodim',
                    'Rol',
                    'Xizmat narxi',
                    'Addon jami',
                    'Chegirma',
                    'Yakuniy summa',
                    'To`langan',
                    'Qoldiq',
                    'Muddat (kun)',
                    'Muddat holati',
                    'Service addonlar',
                    'Document type addonlar',
                    'Direction addonlar',
                    'Process charge',
                    'To`lovlar',
                    'Fayl soni',
                    'Fayl nomlari',
                    'Courier status',
                    'Courier',
                    'Izoh',
                    'Yaratilgan',
                    'Yangilangan',
                ],
                'rows' => $summaryRows->all(),
            ],
            [
                'name' => 'Dok tolovlar',
                'title' => 'Dokumentlar to`lovlari',
                'subtitle' => 'Har bir dokument bo`yicha to`lov tarixi.',
                'columns' => [48, 90, 150, 120, 110, 88, 150, 110],
                'headings' => ['To`lov ID', 'Kod', 'Mijoz', 'Filial', 'To`lov turi', 'Miqdor', 'Qabul qilgan', 'Sana'],
                'rows' => $paymentRows->all(),
            ],
            [
                'name' => 'Dok fayllar',
                'title' => 'Dokument fayllari',
                'subtitle' => 'Biriktirilgan fayllar ro`yxati.',
                'columns' => [48, 90, 150, 180, 110, 76, 220, 110],
                'headings' => ['Fayl ID', 'Kod', 'Mijoz', 'Fayl nomi', 'Turi', 'MB', 'Saqlangan yo`l', 'Sana'],
                'rows' => $fileRows->all(),
            ],
            [
                'name' => 'Dok courier',
                'title' => 'Dokument courier tarixi',
                'subtitle' => 'Courierga yuborilgan hujjatlar holati.',
                'columns' => [90, 150, 120, 145, 145, 80, 170, 170, 170, 110, 110, 110, 110],
                'headings' => ['Kod', 'Mijoz', 'Filial', 'Courier', 'Yuborgan', 'Status', 'Yuborish izohi', 'Courier izohi', 'Qaytish izohi', 'Yuborilgan', 'Qabul qilingan', 'Rad etilgan', 'Qaytarilgan'],
                'rows' => $courierRows->all(),
            ],
            [
                'name' => 'Dok jarayon',
                'title' => 'Dokument process charge',
                'subtitle' => 'Jarayon xarajatlari va vaqt parametrlari.',
                'columns' => [48, 92, 150, 110, 70, 150, 88, 60, 110, 110],
                'headings' => ['ID', 'Kod', 'Mijoz', 'Charge turi', 'Source ID', 'Nomi', 'Narx', 'Kun', 'Yaratilgan', 'Yangilangan'],
                'rows' => $processRows->all(),
            ],
            [
                'name' => 'Dok addonlar',
                'title' => 'Dokument addon relationlari',
                'subtitle' => 'Service, document type va direction addonlar bir joyda.',
                'columns' => [90, 150, 120, 150, 88, 60, 180],
                'headings' => ['Kod', 'Mijoz', 'Manba', 'Addon nomi', 'Narx', 'Kun', 'Izoh'],
                'rows' => $addonRows->all(),
            ],
        ];
    }

    private function employeeSheets(): array
    {
        $users = User::withTrashed()
            ->with([
                'roles:id,name',
                'filial:id,name,code',
                'createdDocuments' => function ($query) {
                    $query->with([
                        'client:id,name,phone_number',
                        'service:id,name',
                        'filial:id,name,code',
                    ])->orderByDesc('created_at');
                },
            ])
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['employee', 'courier', 'admin_filial']))
            ->orderBy('id')
            ->get();

        $summaryRows = $users->map(function (User $user) {
            $documents = $user->createdDocuments;
            $lastDocument = $documents->first();
            $totalFinal = (float) $documents->sum('final_price');
            $totalPaid = (float) $documents->sum('paid_amount');

            return [
                $this->intCell($user->id),
                $this->wrapCell($user->name),
                $this->textCell($user->login),
                $this->textCell($user->phone),
                $this->wrapCell($this->roleLabels($user)),
                $this->wrapCell(optional($user->filial)->name),
                $this->intCell($documents->count()),
                $this->intCell($documents->where('status_doc', '!=', 'finish')->count()),
                $this->intCell($documents->where('status_doc', 'finish')->count()),
                $this->moneyCell($totalFinal),
                $this->moneyCell($totalPaid),
                $this->moneyCell($totalFinal - $totalPaid),
                $this->textCell(optional($lastDocument)->document_code),
                $this->wrapCell(optional(optional($lastDocument)->client)->name),
                $this->textCell($this->formatDate(optional($lastDocument)->created_at)),
                $this->textCell($user->deleted_at ? 'O`chirilgan' : 'Faol'),
                $this->textCell($this->formatDate($user->created_at)),
            ];
        });

        $detailRows = $users->flatMap(function (User $user) {
            return $user->createdDocuments->map(function (DocumentsModel $document) use ($user) {
                return [
                    $this->intCell($user->id),
                    $this->wrapCell($user->name),
                    $this->textCell($user->login),
                    $this->wrapCell($this->roleLabels($user)),
                    $this->wrapCell(optional($user->filial)->name),
                    $this->textCell($document->document_code),
                    $this->wrapCell(optional($document->client)->name),
                    $this->textCell(optional($document->client)->phone_number),
                    $this->wrapCell(optional($document->service)->name),
                    $this->textCell($document->status_doc),
                    $this->moneyCell($document->final_price),
                    $this->moneyCell($document->paid_amount),
                    $this->moneyCell(((float) $document->final_price) - ((float) $document->paid_amount)),
                    $this->wrapCell(optional($document->filial)->name),
                    $this->textCell($this->formatDate($document->created_at)),
                ];
            });
        });

        return [
            [
                'name' => 'Xodimlar',
                'title' => 'Xodimlar bazasi',
                'subtitle' => 'Xodimlar, filial va hujjat yuklamasi statistikasi.',
                'columns' => [45, 160, 110, 100, 115, 120, 68, 68, 68, 88, 88, 88, 92, 150, 110, 92, 110],
                'headings' => [
                    'ID',
                    'F.I.O',
                    'Login',
                    'Telefon',
                    'Rol',
                    'Filial',
                    'Jami hujjat',
                    'Aktiv',
                    'Yakunlangan',
                    'Jami summa',
                    'Jami to`langan',
                    'Qoldiq',
                    'Oxirgi kod',
                    'Oxirgi mijoz',
                    'Oxirgi sana',
                    'Hisob holati',
                    'Yaratilgan',
                ],
                'rows' => $summaryRows->all(),
            ],
            [
                'name' => 'Xodim hujjatlar',
                'title' => 'Xodimlarga tegishli hujjatlar',
                'subtitle' => 'Har bir xodim bo`yicha yaratilgan hujjatlar.',
                'columns' => [45, 150, 110, 110, 120, 95, 150, 105, 120, 80, 88, 88, 88, 120, 110],
                'headings' => [
                    'User ID',
                    'F.I.O',
                    'Login',
                    'Rol',
                    'Filial',
                    'Kod',
                    'Mijoz',
                    'Telefon',
                    'Xizmat',
                    'Status',
                    'Yakuniy summa',
                    'To`langan',
                    'Qoldiq',
                    'Dokument filiali',
                    'Yaratilgan',
                ],
                'rows' => $detailRows->all(),
            ],
        ];
    }

    private function makeFilename(string $slug): string
    {
        return 'global-voice-' . $slug . '-' . now()->format('Y-m-d-His') . '.xls';
    }

    private function textCell(mixed $value): array
    {
        return [
            'value' => $value ?? '',
            'type' => 'String',
            'style' => 'cell',
        ];
    }

    private function wrapCell(mixed $value): array
    {
        return [
            'value' => $value ?? '',
            'type' => 'String',
            'style' => 'wrap',
        ];
    }

    private function intCell(mixed $value): array
    {
        return [
            'value' => $value === null || $value === '' ? '' : (int) $value,
            'type' => 'Number',
            'style' => 'integer',
        ];
    }

    private function moneyCell(mixed $value): array
    {
        return [
            'value' => $value === null || $value === '' ? '' : round((float) $value, 2),
            'type' => 'Number',
            'style' => 'money',
        ];
    }

    private function formatDate(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return $value->format('Y-m-d H:i');
    }

    private function roleLabels(?User $user): string
    {
        if (! $user || ! $user->relationLoaded('roles')) {
            return '';
        }

        return $this->implodeUnique(
            $user->roles->pluck('name')->map(fn ($name) => str_replace('_', ' ', (string) $name))
        , ', ');
    }

    private function personLabel(mixed $user): string
    {
        if (! $user) {
            return '';
        }

        $name = trim((string) ($user->name ?? ''));
        $login = trim((string) ($user->login ?? ''));

        if ($name === '' && $login === '') {
            return '';
        }

        return $login !== '' ? trim($name . ' (' . $login . ')') : $name;
    }

    private function implodeUnique(iterable $values, string $separator = "\n"): string
    {
        return collect($values)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->implode($separator);
    }

    private function formatAmountText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2, '.', ' ');
    }

    private function formatDaysText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (int) $value . ' kun';
    }

    private function bytesToMegabytes(mixed $value): float
    {
        if (! $value) {
            return 0;
        }

        return round(((float) $value) / 1048576, 2);
    }
}
