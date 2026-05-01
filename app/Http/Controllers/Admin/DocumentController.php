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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
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
        'admin_entry' => 'Boshqalar',
    ];

    protected array $processLabels = [
        'apostil' => 'Apostil',
        'consul' => 'Legalizatsiya',
        'service' => 'Xizmat',
    ];

    protected array $statusLabels = [
        'process' => 'Jarayonda',
        'finish' => 'Tugallangan',
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

    
    public function create()
    {
        $documentTypes = DocumentTypeModel::all();
        $directions = DirectionTypeModel::all();
        $consulateTypes = ConsulationTypeModel::all();
        $services = ServicesModel::all();
        $addons = ServiceAddonModel::all();
        $consuls = ConsulModel::all();
        $consul_price = 1000;
        $apostilStatics = ApostilStatikModel::all();
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

        return view('admin_filial.admin_filial_document.refactor.create', compact(
            'services',
            'addons',
            'documentTypes',
            'directions',
            'consulateTypes',
            'consul_price',
            'apostilStatics',
            'consuls',
            'packageTemplates',
            'apiBase'
        ));
    }

    public function store(Request $request)
    {
        //
    }

  
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        //
    }

   
    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
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
            'payment_type' => 'required|in:cash,card,online,admin_entry',
        ]);

        return DB::transaction(function () use ($request) {
            $document = DocumentsModel::query()
                ->whereKey($request->document_id)
                ->lockForUpdate()
                ->firstOrFail();

            $balance = max((float) $document->final_price - (float) $document->paid_amount, 0);

            if ((float) $request->amount > $balance) {
                return response()->json([
                    'status' => 'error',
                    'message' => "To'lov summasi qoldiqdan oshmasligi kerak!",
                ], 422);
            }

            PaymentsModel::create([
                'document_id' => $document->id,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'paid_by_admin_id' => auth()->id(),
            ]);

            $document->paid_amount = (float) $document->paid_amount + (float) $request->amount;
            $document->save();

            return response()->json(['status' => 'success']);
        });
    }

    public function paymentHistory(DocumentsModel $document)
    {
        $payments = PaymentsModel::query()
            ->with(['paidByAdmin' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login')])
            ->where('document_id', $document->id)
            ->orderByDesc('created_at')
            ->get(['id', 'amount', 'payment_type', 'paid_by_admin_id', 'created_at'])
            ->map(function (PaymentsModel $payment) {
                return [
                    'amount' => (float) $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'payment_type_label' => $this->paymentTypes[$payment->payment_type] ?? $payment->payment_type,
                    'paid_by_admin_id' => $payment->paid_by_admin_id,
                    'paid_by_name' => $payment->paidByAdmin?->name ?? 'Noma\'lum',
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
            'finished' => (clone $query)->where('status_doc', 'finish')->count(),
            'process' => (clone $query)->where('status_doc', 'process')->count(),
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

        $rows = $query
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->groupByRaw('MONTH(created_at)')
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

        return $query
            ->selectRaw('YEAR(created_at) as year, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->groupByRaw('YEAR(created_at)')
            ->orderByRaw('YEAR(created_at)')
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
            ->selectRaw("{$column}, COUNT(*) as documents_count, COALESCE(SUM(final_price), 0) as final_price, COALESCE(SUM(paid_amount), 0) as paid_amount, SUM(CASE WHEN status_doc = 'finish' THEN 1 ELSE 0 END) as finished_count")
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
        $years = DocumentsModel::query()
            ->selectRaw('YEAR(created_at) as year')
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
}
