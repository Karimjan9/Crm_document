<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentCreateRequest;
use App\Http\Requests\Admin\DocumentUpdateRequest;
use App\Models\ApostilStatikModel;
use App\Models\ConsulModel;
use App\Models\ConsulationTypeModel;
use App\Models\DirectionTypeModel;
use App\Models\DocumentsModel;
use App\Models\DocumentTypeModel;
use App\Models\PaymentsModel;
use App\Models\PackageTemplate;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Models\DocumentCourier;
use App\Support\PackageTemplateSupport;
use App\Support\StoresDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\OrderCaseService;

class DocumentController extends Controller
{
    use StoresDocuments;

    public function getServiceAddons($serviceId)
    {
        ServicesModel::query()->findOrFail($serviceId);
        $addons = ServiceAddonModel::where('service_id', $serviceId)
            ->select(['id', 'name', 'price'])
            ->get();

        return response()->json($addons);
    }

    public function index()
    {
        $user = auth()->user();

        $documents = DocumentsModel::select([
                'id',
                'client_id',
                'service_id',
                'user_id',
                'document_code',
                'deadline_time',
                'final_price',
                'paid_amount',
                'discount',
                'status_doc',
                'process_mode',
                'document_type_id',
                'direction_type_id',
                'consulate_type_id',
                'created_at',
            ])
            ->with([
                'client:id,name',
                'service:id,name,deadline',
                'addons',
                'user:id,filial_id',
                'files:id,document_id,original_name,file_path',
                'documentType:id,name',
                'directionType:id,name',
                'consulateType:id,name',
                'courierAssignment.courier:id,name',
            ])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->paginate(30);

        $couriers = User::role('courier')
            ->where('filial_id', $user->filial_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employee.document.index', compact('documents', 'couriers'));
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

        return view('employee.document.refactor.create',
            array_merge(compact(
                'services',
                'addons',
                'documentTypes',
                'directions',
                'consulateTypes',
                'consul_price',
                'apostilStatics',
                'consuls',
                'packageTemplates'
            ), [
                'orderId' => $request->integer('order_id') ?: null,
            ])
        );
    }

    public function store(DocumentCreateRequest $request)
    {
        $document = $this->storeDocumentFromRequest($request);

        return redirect($request->filled('order_id')
            ? route('orders.show', $document->order_id)
            : route('employee.document.index'))
            ->with('success', 'Hujjat muvaffaqiyatli yaratildi!');
    }

    public function edit($id)
    {
        $document = DocumentsModel::with(['addons', 'client', 'payments'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $this->authorize('update', $document);

        // 24 soatdan oshganini tekshirish
        if ($document->created_at->diffInHours(now()) > 24) {
            return redirect()->back()->with('error', '24 soatdan oshgan hujjatni o‘zgartirish mumkin emas.');
        }

        $services = ServicesModel::all();
        $addons = ServiceAddonModel::all();
        $documentTypes = DocumentTypeModel::all();
        $directions = DirectionTypeModel::all();
        $consulates = ConsulationTypeModel::all();

        return view('employee.document.edit', compact(
            'document',
            'services',
            'addons',
            'documentTypes',
            'directions',
            'consulates'
        ));
    }

    public function update(DocumentUpdateRequest $request, $id)
    {
        $document = DocumentsModel::with(['addons'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $this->authorize('update', $document);
        $this->updateDocumentFromRequest($document, $request);

        return redirect()->route('employee.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yangilandi!');
    }

    public function doc_summary()
    {
        $user = auth()->user();

        $documents = DocumentsModel::query()
            ->select([
                'id',
                'document_code',
                'final_price',
                'paid_amount',
                'discount',
                'description',
            ])
            ->with([
                'payments:id,document_id,amount,payment_type,paid_by_admin_id,created_at',
            ])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->paginate(25);

        return view('employee.document.summary', compact('documents'));
    }

    public function add_payment(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'amount' => 'required|numeric|min:1000',
            'payment_type' => 'required|in:cash,card,online,transfer,admin_entry',
        ]);

        DB::transaction(function () use ($request) {
            $doc = DocumentsModel::query()
                ->where('user_id', auth()->id())
                ->whereKey($request->document_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorize('pay', $doc);
            $this->recordPayment($doc, $request);
        });

        return response()->json(['status' => 'success']);
    }

    public function paymentHistory(DocumentsModel $document)
    {
        $this->authorize('view', $document);

        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $paymentTypes = [
            'cash' => 'Naqd',
            'card' => 'Plastik karta',
            'online' => 'Onlayn',
            'transfer' => 'Bank transfer',
            'admin_entry' => 'Boshqalar',
        ];

        $payments = PaymentsModel::with(['paidByAdmin' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login'), 'cashier' => fn ($q) => $q->withTrashed()->select('id', 'name', 'login')])
            ->where('document_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->get(['amount', 'payment_type', 'status', 'confirmation_status', 'receipt_number', 'refund_amount', 'online_transaction_id', 'paid_by_admin_id', 'cashier_id', 'created_at'])
            ->map(fn (PaymentsModel $payment) => [
                'amount' => (float) $payment->amount,
                'payment_type' => $payment->payment_type,
                'payment_type_label' => $paymentTypes[$payment->payment_type] ?? $payment->payment_type,
                'status' => $payment->status,
                'confirmation_status' => $payment->confirmation_status,
                'receipt_number' => $payment->receipt_number,
                'refund_amount' => (float) $payment->refund_amount,
                'online_transaction_id' => $payment->online_transaction_id,
                'paid_by_admin_id' => $payment->paid_by_admin_id,
                'paid_by_name' => $payment->cashier?->name ?: ($payment->paidByAdmin?->name ?? 'Noma\'lum'),
                'created_at' => optional($payment->created_at)->toIso8601String(),
            ]);

        return response()->json($payments);
    }

    public function completeDocument(DocumentsModel $document)
    {
        $this->authorize('complete', $document);

        if ($document->courierAssignment && in_array($document->courierAssignment->status, ['sent', 'accepted'])) {
            return redirect()->back()->with('error', 'Courierga yuborilgan hujjatni tugallab bo‘lmaydi.');
        }

        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        app(\App\Services\DocumentWorkflowService::class)->transition(
            $document,
            'ready_for_delivery',
            auth()->user(),
            'Legacy complete tugmasi orqali yakunlandi.',
        );

        if ($document->order_id) {
            app(OrderCaseService::class)->syncFromDocuments(
                Order::query()->findOrFail((int) $document->order_id),
                auth()->user()
            );
        }

        return redirect()->route('employee.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli tugallandi!');
    }

    public function sendToCourier(Request $request, DocumentsModel $document)
    {
        $this->authorize('sendToCourier', $document);

        $user = auth()->user();

        if ($document->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'courier_id' => 'required|exists:users,id',
            'comment' => 'nullable|string|max:1000',
        ]);

        $courier = User::role('courier')
            ->where('filial_id', $user->filial_id)
            ->where('id', $request->courier_id)
            ->firstOrFail();

        $assignment = DocumentCourier::firstOrNew(['document_id' => $document->id]);
        if ($assignment->exists && in_array($assignment->status, ['sent', 'accepted'])) {
            return redirect()->back()->with('error', 'Bu hujjat allaqachon courierda.');
        }

        $assignment->courier_id = $courier->id;
        $assignment->sent_by_id = $user->id;
        $assignment->status = 'sent';
        $assignment->sent_comment = $request->comment;
        $assignment->courier_comment = null;
        $assignment->return_comment = null;
        $assignment->sent_at = now();
        $assignment->accepted_at = null;
        $assignment->rejected_at = null;
        $assignment->returned_at = null;
        $assignment->save();

        app(OrderCaseService::class)->syncDocumentCourier($assignment);

        return redirect()->back()->with('success', 'Hujjat courierga yuborildi.');
    }
}
