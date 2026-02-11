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
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Support\StoresDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    use StoresDocuments;

    public function getServiceAddons($serviceId)
    {
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
                'consulateType:id,name'
            ])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->paginate(30);

        return view('employee.document.index', compact('documents'));
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

        return view('employee.document.refactor.create',
            compact(
                'services',
                'addons',
                'documentTypes',
                'directions',
                'consulateTypes',
                'consul_price',
                'apostilStatics',
                'consuls'
            )
        );
    }

    public function store(DocumentCreateRequest $request)
    {
        $this->storeDocumentFromRequest($request);

        return redirect()->route('employee.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yaratildi!');
    }

    public function edit($id)
    {
        $document = DocumentsModel::with(['addons', 'client', 'payments'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

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

        $service = ServicesModel::findOrFail($request->service_id);
        $servicePrice = $service->price;
        $deadlineTime = $service->deadline;

        $addons_total = 0;
        $addonsData = [];
        if ($request->addons) {
            $addons = DB::table('service_addons')->whereIn('id', $request->addons)->get();
            foreach ($addons as $addon) {
                $addons_total += $addon->price;
                $deadlineTime += $addon->deadline;
                $addonsData[$addon->id] = [
                    'addon_price' => $addon->price,
                    'addon_deadline' => $addon->deadline,
                ];
            }
        }

        $discount = $request->discount ?? 0;
        $totalPrice = $servicePrice + $addons_total;
        $finalPrice = $totalPrice - ($totalPrice * ($discount / 100));

        $document->update([
            'service_id' => $request->service_id,
            'service_price' => $servicePrice,
            'addons_total_price' => $addons_total,
            'deadline_time' => $deadlineTime,
            'final_price' => $finalPrice,
            'paid_amount' => $request->paid_amount ?? 0,
            'discount' => $discount,
            'description' => $request->description,
            'document_type_id' => $request->document_type_id,
            'direction_type_id' => $request->direction_type_id,
            'consulate_type_id' => $request->consulate_type_id,
        ]);

        $document->addons()->sync($addonsData);

        if ($request->paid_amount && $request->payment_type) {
            PaymentsModel::updateOrCreate(
                ['document_id' => $document->id],
                [
                    'amount' => $request->paid_amount,
                    'payment_type' => $request->payment_type,
                    'paid_by_admin_id' => auth()->id(),
                ]
            );
        }

        return redirect()->route('employee.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yangilandi!');
    }

    public function doc_summary()
    {
        $user = auth()->user();

        $documents = DocumentsModel::with(['client', 'service', 'addons', 'payments', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('employee.document.summary', compact('documents'));
    }

    public function add_payment(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'amount' => 'required|numeric|min:1000',
            'payment_type' => 'required|string',
        ]);

        $doc = DocumentsModel::where('user_id', auth()->id())->findOrFail($request->document_id);
        $balance = $doc->final_price - $doc->paid_amount;

        if ($request->amount > $balance) {
            return response()->json([
                'status' => 'error',
                'message' => "To'lov summasi qoldiqdan oshmasligi kerak!",
            ], 422);
        }

        PaymentsModel::create([
            'document_id' => $doc->id,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'paid_by_admin_id' => auth()->id(),
        ]);

        $doc->paid_amount += $request->amount;
        $doc->save();

        return response()->json(['status' => 'success']);
    }

    public function paymentHistory(DocumentsModel $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $payments = PaymentsModel::where('document_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->get(['amount', 'payment_type', 'paid_by_admin_id', 'created_at']);

        return response()->json($payments);
    }

    public function completeDocument(DocumentsModel $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $document->status_doc = 'finish';
        $document->save();

        return redirect()->route('employee.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli tugallandi!');
    }
}
