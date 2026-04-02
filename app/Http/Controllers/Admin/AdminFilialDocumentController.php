<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentCreateRequest;
use App\Http\Requests\Admin\DocumentUpdateRequest;
use App\Models\ApostilStatikModel;
use App\Models\ClientsModel;
use App\Models\ConsulationTypeModel;
use App\Models\ConsulModel;
use App\Models\DirectionTypeModel;
use App\Models\DocumentsModel;
use App\Models\DocumentTypeModel;
use App\Models\PaymentsModel;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use App\Models\User;
use App\Models\DocumentCourier;
use App\Support\StoresDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminFilialDocumentController extends Controller
{
    use StoresDocuments;
//     if (auth()->user()->role == 'superadmin') {
//     $documents = DocumentsModel::with(['client','service','addons','user'])
//         ->orderBy('id','DESC')
//         ->get();
// } else {
//     $userFilialId = auth()->user()->filial_id;

//     $documents = DocumentsModel::with(['client','service','addons','user'])
//         ->whereHas('user', function ($q) use ($userFilialId) {
//             $q->where('filial_id', $userFilialId);
//         })
//         ->orderBy('id','DESC')
//         ->get();
// }

   public function getServiceAddons($serviceId)
    {
        $addons = ServiceAddonModel::where('service_id', $serviceId)
                    ->select(['id', 'name', 'price'])
                    ->get();

        return response()->json($addons);
    }

    public function index()
    {
        $userFilialId = auth()->user()->filial_id;

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
            ->whereHas('user', function ($q) use ($userFilialId) {
                $q->where('filial_id', $userFilialId);
            })
            ->orderBy('id', 'DESC')
            ->paginate(30);

        $couriers = User::role('courier')
            ->where('filial_id', $userFilialId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin_filial.admin_filial_document.index', compact('documents', 'couriers'));
    }

    public function create()
    {
        $userFilialId   = auth()->user()->filial_id;
        $documentTypes  = DocumentTypeModel::all();
        $directions = DirectionTypeModel::all();
        $consulateTypes = ConsulationTypeModel::all();
        // Faqat o‘z filialidagi xizmatlar
        $services = ServicesModel::all();

        // Faqat o‘z filialidagi qo‘shimcha xizmatlar
        $addons = ServiceAddonModel::all();
        $consuls=ConsulModel::all();
        // return view('admin_filial.admin_filial_document.create',
        //     compact('services', 'addons', 'documentTypes',
        //         'directionTypes', 'consulateTypes'));

        $consul_price = 1000;
        $apostilStatics=ApostilStatikModel::all();
        return view('admin_filial.admin_filial_document.refactor.create',
            compact('services', 'addons', 'documentTypes',
                'directions', 'consulateTypes', 'consul_price', 'apostilStatics', 'consuls'));
    }

    // -------------------------------
    // STORE: Ma’lumotni saqlash
    // -------------------------------
    public function store(DocumentCreateRequest $request)
    {
        $this->storeDocumentFromRequest($request);

        return redirect()->route('admin_filial.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yaratildi!');
    }

  public function edit($id)
{
    $document = DocumentsModel::with(['addons', 'client', 'payments'])->findOrFail($id);

    // 24 soatdan oshganini tekshirish
    if ($document->created_at->diffInHours(now()) > 24) {
        return redirect()->back()->with('error', '24 soatdan oshgan hujjatni o‘zgartirish mumkin emas.');
    }

    // ro'yxatlar
    $services      = ServicesModel::all();
    $addons        = ServiceAddonModel::all();
    $documentTypes = DocumentTypeModel::all();
    $directions    = DirectionTypeModel::all();
    $consulates    = ConsulationTypeModel::all();

    return view('admin_filial.admin_filial_document.edit', compact(
        'document', 'services', 'addons',
        'documentTypes', 'directions', 'consulates'
    ));
}



    public function update(DocumentUpdateRequest $request, $id)
    {
        $document = DocumentsModel::with(['addons'])->findOrFail($id);

        // Client o'zgarmaydi

        // Service va addons
        $service      = ServicesModel::findOrFail($request->service_id);
        $servicePrice = $service->price;
        $deadlineTime = $service->deadline;

        $addons_total = 0;
        $addonsData   = [];
        if ($request->addons) {
            $addons = DB::table('service_addons')->whereIn('id', $request->addons)->get();
            foreach ($addons as $addon) {
                $addons_total += $addon->price;
                $deadlineTime += $addon->deadline;
                $addonsData[$addon->id] = [
                    'addon_price'    => $addon->price,
                    'addon_deadline' => $addon->deadline,
                ];
            }
        }

        $discount   = $request->discount ?? 0;
        $totalPrice = $servicePrice + $addons_total;
        $finalPrice = $totalPrice - ($totalPrice * ($discount / 100));

        // Document update
        $document->update([
            'service_id'         => $request->service_id,
            'service_price'      => $servicePrice,
            'addons_total_price' => $addons_total,
            'deadline_time'      => $deadlineTime,
            'final_price'        => $finalPrice,
            'paid_amount'        => $request->paid_amount ?? 0,
            'discount'           => $discount,
            'description'        => $request->description,
            'document_type_id'   => $request->document_type_id,
            'direction_type_id'  => $request->direction_type_id,
            'consulate_type_id'  => $request->consulate_type_id,
        ]);

        // Addons update
        $document->addons()->sync($addonsData);

        // Payment update yoki create
        if ($request->paid_amount && $request->payment_type) {
            PaymentsModel::updateOrCreate(
                ['document_id' => $document->id],
                [
                    'amount'           => $request->paid_amount,
                    'payment_type'     => $request->payment_type,
                    'paid_by_admin_id' => auth()->id(),
                ]
            );
        }

        return redirect()->route('admin_filial.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yangilandi!');
    }

        public function doc_summary()
    {
        $user = auth()->user();
        $userFilialId = $user->filial_id;

        $query = DocumentsModel::with(['client', 'service', 'addons', 'payments', 'user']);

        $query->whereHas('user', function ($q) use ($userFilialId) {
            $q->where('filial_id', $userFilialId);
        });

        $documents = $query->orderBy('id', 'DESC')->get();

        return view('admin_filial.summary_doc.index', compact('documents'));
    }


    public function add_payment(Request $request)
    {
        $request->validate([
            'document_id'  => 'required|exists:documents,id',
            'amount'       => 'required|numeric|min:1000',
            'payment_type' => 'required|string',
        ]);

        $doc = DocumentsModel::find($request->document_id);

        $balance = $doc->final_price - $doc->paid_amount;

        // >>> BACKEND CHEK: to‘lov qoldiqdan oshmasin!
        if ($request->amount > $balance) {
            return response()->json([
                'status'  => 'error',
                'message' => "To'lov summasi qoldiqdan oshmasligi kerak!",
            ], 422);
        }

        PaymentsModel::create([
            'document_id'      => $request->document_id,
            'amount'           => $request->amount,
            'payment_type'     => $request->payment_type,
            'paid_by_admin_id' => auth()->id(),
        ]);

        $doc->paid_amount += $request->amount;
        $doc->save();

        return response()->json(['status' => 'success']);
    }

    public function paymentHistory(DocumentsModel $document)
    {
        $payments = PaymentsModel::where('document_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->get(['amount', 'payment_type', 'paid_by_admin_id', 'created_at']);

        return response()->json($payments);
    }
    public function completeDocument(DocumentsModel $document)
    {
        if ($document->courierAssignment && in_array($document->courierAssignment->status, ['sent', 'accepted'])) {
            return redirect()->back()->with('error', 'Courierga yuborilgan hujjatni tugallab bo‘lmaydi.');
        }

        // Hujjatni tugallash
        $document->status_doc = 'finish';
        $document->save();

        return redirect()->route('admin_filial.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli tugallandi!');
    }

    public function sendToCourier(Request $request, DocumentsModel $document)
    {
        $user = auth()->user();

        if ($document->filial_id !== $user->filial_id) {
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

        return redirect()->back()->with('success', 'Hujjat courierga yuborildi.');
    }
}

