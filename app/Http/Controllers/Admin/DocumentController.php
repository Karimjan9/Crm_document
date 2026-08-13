<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApostilStatikModel;
use App\Models\DocumentsModel;
use App\Models\FilialModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\DocumentTypeModel;
use App\Models\PackageTemplate;
use App\Models\PaymentsModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Support\PackageTemplateSupport;
use App\Support\StoresDocuments;
use App\Http\Requests\Admin\DocumentCreateRequest;
use App\Http\Requests\Admin\DocumentUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\OrderCaseService;
use App\Models\Order;

class DocumentController extends Controller
{
    use StoresDocuments;

    protected array $monthNames = [
        1 => 'Yanvar',
        2 => 'Fevral',
        3 => 'Mart',
        4 => 'Aprel',
        5 => 'May',
        6 => 'Iyun',
        7 => 'Iyul',
        8 => 'Avgust',
        9 => 'Sentabr',
        10 => 'Oktabr',
        11 => 'Noyabr',
        12 => 'Dekabr',
    ];

    protected array $paymentTypes = [
        'cash' => 'Naqd',
        'card' => 'Plastik karta',
        'online' => 'Onlayn',
        'transfer' => 'Bank transfer',
        'admin_entry' => 'Boshqalar',
    ];

    protected array $processLabels = [
        'apostil' => 'Apostil',
        'consul' => 'Legalizatsiya',
        'service' => 'Xizmat',
    ];

    protected array $statusLabels = [
        ...DocumentsModel::STATUS_LABELS,
    ];

    protected function routePrefix(): string
    {
        return request()->routeIs('superadmin.*') ? 'superadmin' : 'admin';
    }

    public function index(Request $request)
    {
        $defaults = $this->normalizePeriodDefaults($request);
        $query = $this->filteredDocumentsQuery($request);

        $documents = (clone $query)
            ->with([
                'client:id,name,phone_number',
                'service:id,name',
                'filial:id,name',
                'documentType:id,name',
                'user' => fn ($q) => $q->withTrashed()->select('id', 'name', 'filial_id', 'login'),
            ])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.document.index', array_merge(
            $this->sharedFilterData($request, $query),
            [
                'documents' => $documents,
                'routePrefix' => $this->routePrefix(),
                'selectedYear' => $defaults['year'],
                'selectedMonth' => $defaults['month'],
            ]
        ));
    }

    
    public function create(Request $request)
    {
        $documentTypes = DocumentTypeModel::all();
        $directions = DirectionTypeModel::all();
        $consulateTypes = ConsulationTypeModel::all();
        $services = ServicesModel::all();
        $addons = ServiceAddonModel::all();
        $consuls = ConsulModel::all();
        $consul_price = 1000;
        $apostilStatics = ApostilStatikModel::all();
        $filials = auth()->user()?->filial_id === null
            ? FilialModel::query()->orderBy('name')->get(['id', 'name'])
            : collect();
        $packageTemplates = PackageTemplateSupport::buildSelectionPayloads(
            PackageTemplate::query()
                ->active()
                ->whereHas('items')
                ->ordered()
                ->with([
                    'items.documentType:id,name',
                    'items.service:id,name,price,deadline',
                    'items.directionType:id,name',
                    'items.apostilGroup1:id,name,price,days',
                    'items.apostilGroup2:id,name,price,days',
                    'items.consul:id,name,amount,day',
                    'items.consulateType:id,name,amount,day',
                ])
                ->get()
        );
        $apiBase = url($this->routePrefix() . '/api');

        return view('admin_filial.admin_filial_document.refactor.create', array_merge(compact(
            'services',
            'addons',
            'documentTypes',
            'directions',
            'consulateTypes',
            'consul_price',
            'apostilStatics',
            'consuls',
            'packageTemplates',
            'filials',
            'apiBase'
        ), [
            'orderId' => $request->integer('order_id') ?: null,
        ]));
    }

    public function store(DocumentCreateRequest $request)
    {
        $document = $this->storeDocumentFromRequest($request);

        return redirect($request->filled('order_id')
            ? route('orders.show', $document->order_id)
            : route('superadmin.document.index'))
            ->with('success', 'Hujjat muvaffaqiyatli yaratildi.');
    }

    public function show($id)
    {
        $document = DocumentsModel::query()
            ->with([
                'client',
                'service',
                'filial',
                'documentType',
                'directionType',
                'consulateType',
                'user' => fn ($query) => $query->withTrashed(),
                'assignedTo',
                'qaUser',
                'files',
                'payments' => fn ($query) => $query->latest(),
                'courierAssignment.courier',
                'pricingApprovals.requestedBy',
                'pricingApprovals.approvedBy',
                'statusHistories.changedBy',
                'assignmentHistories.assignedTo',
                'assignmentHistories.qaUser',
                'assignmentHistories.assignedBy',
                'checklists.completedBy',
                'latestQaReview.reviewer',
            ])
            ->findOrFail($id);

        $this->authorize('view', $document);

        return view('admin.document.show', [
            'document' => $document,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function edit($id)
    {
        $document = DocumentsModel::query()
            ->with([
                'addons',
                'document_type_addons',
                'document_direction_addons',
                'client',
                'payments',
            ])
            ->findOrFail($id);

        $this->authorize('update', $document);

        $services = ServicesModel::query()->orderBy('name')->get();
        $addons = ServiceAddonModel::query()->orderBy('name')->get();
        $documentTypes = DocumentTypeModel::query()->orderBy('name')->get();
        $directions = DirectionTypeModel::query()->orderBy('name')->get();
        $consulates = ConsulationTypeModel::query()->orderBy('name')->get();
        $documentRoutePrefix = $this->routePrefix();
        $addonsUrlTemplate = route($documentRoutePrefix . '.api.addons.index', [
            'type' => 'service',
            'id' => ':id',
        ]);

        return view('admin_filial.admin_filial_document.edit', compact(
            'document',
            'services',
            'addons',
            'documentTypes',
            'directions',
            'consulates',
            'documentRoutePrefix',
            'addonsUrlTemplate'
        ));
    }

    public function update(DocumentUpdateRequest $request, $id)
    {
        $document = DocumentsModel::query()->findOrFail($id);
        $this->authorize('update', $document);
        $this->updateDocumentFromRequest($document, $request);

        return redirect()
            ->route($this->routePrefix() . '.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $document = DocumentsModel::query()
            ->with('files')
            ->findOrFail($id);
        $this->authorize('update', $document);

        $paths = $document->files->pluck('file_path')->filter()->values()->all();

        if ($document->payments()->exists()) {
            throw ValidationException::withMessages([
                'document' => 'Payment ledger mavjud bo‘lgan hujjatni o‘chirib bo‘lmaydi. Avval to‘lovni cancel/refund qiling.',
            ]);
        }

        DB::transaction(function () use ($document): void {
            // Several legacy pivot tables do not have ON DELETE CASCADE. Clean
            // those rows explicitly so a valid document can always be removed.
            $document->addons()->detach();
            $document->document_type_addons()->detach();
            $document->document_direction_addons()->detach();
            $document->processCharges()->delete();
            $document->courierAssignment()->delete();
            $document->files()->delete();
            $document->delete();
        });

        if ($paths !== []) {
            Storage::disk('private')->delete($paths);
        }

        return redirect()
            ->route($this->routePrefix() . '.document.index')
            ->with('success', 'Hujjat o\'chirildi.');
    }

    public function statistika(Request $request)
    {
        $defaults = $this->normalizePeriodDefaults($request);
        $query = $this->filteredDocumentsQuery($request);

        return view('admin.document.statistika', array_merge(
            $this->sharedFilterData($request, $query),
            [
                'routePrefix' => $this->routePrefix(),
                'selectedYear' => $defaults['year'],
                'selectedMonth' => $defaults['month'],
            ]
        ));
    }

    public function add_payment(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'amount' => 'required|numeric|min:1000',
            'payment_type' => 'required|in:cash,card,online,transfer,admin_entry',
            'cash_session_id' => 'nullable|integer|exists:cash_sessions,id',
            'online_transaction_id' => 'nullable|string|max:160',
            'payment_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        return DB::transaction(function () use ($request) {
            $document = DocumentsModel::query()
                ->whereKey($request->document_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($document->order_id) {
                app(OrderCaseService::class)->recordPayment(
                    Order::query()->findOrFail((int) $document->order_id),
                    (float) $request->amount,
                    (string) $request->payment_type,
                    auth()->user(),
                    (int) $document->id,
                    [
                        'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->cash_session_id : null,
                        'online_transaction_id' => $request->input('online_transaction_id'),
                        'payment_proof' => $request->file('payment_proof'),
                    ]
                );

                return response()->json(['status' => 'success']);
            }

            $balance = max((float) $document->final_price - (float) $document->paid_amount, 0);

            if ((float) $request->amount > $balance) {
                return response()->json([
                    'status' => 'error',
                    'message' => "To'lov summasi qoldiqdan oshmasligi kerak!",
                ], 422);
            }

            app(\App\Services\PaymentService::class)->recordForDocument(
                $document,
                (float) $request->amount,
                (string) $request->payment_type,
                auth()->user(),
                [
                    'cash_session_id' => $request->filled('cash_session_id') ? (int) $request->cash_session_id : null,
                    'online_transaction_id' => $request->input('online_transaction_id'),
                    'payment_proof' => $request->file('payment_proof'),
                ],
            );

            if ($document->order_id) {
                app(OrderCaseService::class)->recalculate(
                    Order::query()->findOrFail((int) $document->order_id)
                );
            }

            return response()->json(['status' => 'success']);
        });
    }

    public function paymentHistory(DocumentsModel $document)
    {
        $payments = PaymentsModel::query()
            ->with(['paidByAdmin' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login'), 'cashier' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login')])
            ->where('document_id', $document->id)
            ->orderByDesc('created_at')
            ->get(['id', 'amount', 'payment_type', 'status', 'confirmation_status', 'receipt_number', 'refund_amount', 'online_transaction_id', 'paid_by_admin_id', 'cashier_id', 'created_at'])
            ->map(function (PaymentsModel $payment) {
                return [
                    'amount' => (float) $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'payment_type_label' => $this->paymentTypes[$payment->payment_type] ?? $payment->payment_type,
                    'status' => $payment->status,
                    'confirmation_status' => $payment->confirmation_status,
                    'receipt_number' => $payment->receipt_number,
                    'refund_amount' => (float) $payment->refund_amount,
                    'online_transaction_id' => $payment->online_transaction_id,
                    'paid_by_admin_id' => $payment->paid_by_admin_id,
                    'paid_by_name' => $payment->cashier?->name ?: ($payment->paidByAdmin?->name ?? 'Noma\'lum'),
                    'created_at' => optional($payment->created_at)->toIso8601String(),
                ];
            });

        return response()->json($payments);
    }

    protected function sharedFilterData(Request $request, $query): array
    {
        $defaults = $this->normalizePeriodDefaults($request);

        return [
            'filters' => $request->query(),
            'filials' => FilialModel::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::withTrashed()
                ->whereIn('id', DocumentsModel::query()->select('user_id')->distinct())
                ->orderBy('name')
                ->get(['id', 'name', 'login', 'filial_id']),
            'yearOptions' => $this->yearOptions($defaults['year']),
            'monthNames' => $this->monthNames,
            'paymentTypes' => $this->paymentTypes,
            'processLabels' => $this->processLabels,
            'statusLabels' => $this->statusLabels,
            'summary' => $this->summaryForQuery(clone $query),
            'monthlyStats' => $this->monthlyStats($request, $defaults['year']),
            'yearlyStats' => $this->yearlyStats($request),
            'filialStats' => $this->groupedStats($query, 'filial_id', FilialModel::query()->pluck('name', 'id')->all()),
            'userStats' => $this->groupedStats(
                $query,
                'user_id',
                User::withTrashed()->pluck('name', 'id')->all()
            ),
            'typeStats' => $this->groupedStats(
                $query,
                'document_type_id',
                DocumentTypeModel::withTrashed()->pluck('name', 'id')->all()
            ),
        ];
    }

    protected function filteredDocumentsQuery(Request $request, array $ignore = [])
    {
        $query = DocumentsModel::query();

        $this->applyFilters($query, $request, $ignore);

        return $query;
    }

    protected function applyFilters($query, Request $request, array $ignore = []): void
    {
        $defaults = $this->normalizePeriodDefaults($request);

        if (!in_array('filial_id', $ignore, true) && $request->filled('filial_id')) {
            $query->where('filial_id', $request->integer('filial_id'));
        }

        if (!in_array('user_id', $ignore, true) && $request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if (!in_array('status_doc', $ignore, true) && $request->filled('status_doc')) {
            $query->where('status_doc', $request->input('status_doc'));
        }

        if (!in_array('process_mode', $ignore, true) && $request->filled('process_mode')) {
            if ($request->input('process_mode') === 'service') {
                $query->where(fn ($q) => $q->whereNull('process_mode')->orWhere('process_mode', 'service'));
            } else {
                $query->where('process_mode', $request->input('process_mode'));
            }
        }

        if (!in_array('payment_status', $ignore, true) && $request->filled('payment_status')) {
            $this->applyPaymentStatusFilter($query, $request->input('payment_status'));
        }

        if (!in_array('year', $ignore, true) && $defaults['year']) {
            $query->whereYear('created_at', $defaults['year']);
        }

        if (!in_array('month', $ignore, true) && $defaults['month']) {
            $query->whereMonth('created_at', $defaults['month']);
        }

        if (!in_array('date_from', $ignore, true) && $request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if (!in_array('date_to', $ignore, true) && $request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if (!in_array('q', $ignore, true) && $request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('document_code', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('service', fn ($service) => $service->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }
    }

    protected function applyPaymentStatusFilter($query, string $status): void
    {
        match ($status) {
            'paid' => $query->whereRaw('COALESCE(paid_amount, 0) >= COALESCE(final_price, 0)'),
            'partial' => $query->whereRaw('COALESCE(paid_amount, 0) > 0 AND COALESCE(paid_amount, 0) < COALESCE(final_price, 0)'),
            'debt' => $query->whereRaw('COALESCE(final_price, 0) > 0 AND COALESCE(paid_amount, 0) <= 0'),
            default => null,
        };
    }

    protected function summaryForQuery($query): array
    {
        return [
            'documents' => (clone $query)->count(),
            'final_price' => (float) (clone $query)->sum('final_price'),
            'paid_amount' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)
                ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(final_price, 0) - COALESCE(paid_amount, 0) > 0 THEN COALESCE(final_price, 0) - COALESCE(paid_amount, 0) ELSE 0 END), 0) as total')
                ->value('total'),
            'finished' => (clone $query)->whereIn('status_doc', ['finish', 'completed', 'delivered'])->count(),
            'process' => (clone $query)->whereNotIn('status_doc', ['finish', 'completed', 'delivered', 'cancelled', 'refunded'])->count(),
            'paid_documents' => (clone $query)->whereRaw('COALESCE(paid_amount, 0) >= COALESCE(final_price, 0)')->count(),
            'partial_documents' => (clone $query)->whereRaw('COALESCE(paid_amount, 0) > 0 AND COALESCE(paid_amount, 0) < COALESCE(final_price, 0)')->count(),
            'debt_documents' => (clone $query)->whereRaw('COALESCE(final_price, 0) > 0 AND COALESCE(paid_amount, 0) <= 0')->count(),
        ];
    }

    protected function monthlyStats(Request $request, int $year): array
    {
        $query = DocumentsModel::query();
        $this->applyFilters($query, $request, ['month', 'date_from', 'date_to']);
        $query->whereYear('created_at', $year);
        $monthExpression = $this->monthExpression();

        $rows = $query
            ->selectRaw("{$monthExpression} as month, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount")
            ->groupByRaw($monthExpression)
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function (int $month) use ($rows) {
            $row = $rows->get($month);

            return [
                'month' => $month,
                'label' => $this->monthNames[$month],
                'documents' => (int) ($row->documents_count ?? 0),
                'final_price' => (float) ($row->final_price ?? 0),
                'paid_amount' => (float) ($row->paid_amount ?? 0),
            ];
        })->all();
    }

    protected function yearlyStats(Request $request): array
    {
        $query = DocumentsModel::query();
        $this->applyFilters($query, $request, ['year', 'month', 'date_from', 'date_to']);
        $yearExpression = $this->yearExpression();

        return $query
            ->selectRaw("{$yearExpression} as year, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount")
            ->groupByRaw($yearExpression)
            ->orderByRaw($yearExpression)
            ->get()
            ->map(fn ($row) => [
                'year' => (int) $row->year,
                'documents' => (int) $row->documents_count,
                'final_price' => (float) $row->final_price,
                'paid_amount' => (float) $row->paid_amount,
            ])
            ->all();
    }

    protected function groupedStats($query, string $column, array $labels): array
    {
        return (clone $query)
            ->selectRaw("{$column}, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount, SUM(CASE WHEN status_doc IN ('finish', 'completed', 'delivered') THEN 1 ELSE 0 END) as finished_count")
            ->groupBy($column)
            ->orderByDesc('documents_count')
            ->limit(12)
            ->get()
            ->map(function ($row) use ($column, $labels) {
                $id = $row->{$column};
                $finalPrice = (float) $row->final_price;
                $paidAmount = (float) $row->paid_amount;

                return [
                    'id' => $id,
                    'label' => $labels[$id] ?? 'Noma\'lum',
                    'documents' => (int) $row->documents_count,
                    'finished' => (int) $row->finished_count,
                    'final_price' => $finalPrice,
                    'paid_amount' => $paidAmount,
                    'balance' => max($finalPrice - $paidAmount, 0),
                ];
            })
            ->all();
    }

    protected function yearOptions(int $selectedYear)
    {
        $yearExpression = $this->yearExpression();

        $years = DocumentsModel::query()
            ->selectRaw("{$yearExpression} as year")
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->values();

        return $years->contains($selectedYear)
            ? $years
            : $years->push($selectedYear)->unique()->sortDesc()->values();
    }

    protected function normalizePeriodDefaults(Request $request): array
    {
        return [
            'year' => (int) ($request->input('year') ?: now()->year),
            'month' => $request->filled('month') ? (int) $request->input('month') : null,
        ];
    }

    protected function yearExpression(): string
    {
        return DocumentsModel::query()->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';
    }

    protected function monthExpression(): string
    {
        return DocumentsModel::query()->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';
    }
}
