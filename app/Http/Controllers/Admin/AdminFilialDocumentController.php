<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentCreateRequest;
use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\ServicesAddonsModel;
use App\Models\ServicesModel;
use Illuminate\Support\Facades\Auth;

class AdminFilialDocumentController extends Controller
{
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
        // masalan service_id bo‘yicha addonslarni olamiz
        $addons = ServicesAddonsModel::where('service_id', $serviceId)->get(['id', 'name', 'price', 'deadline']);
        return response()->json($addons);
    }
    public function index()
    {
        $userFilialId = auth()->user()->filial_id;

        $documents = DocumentsModel::with(['client', 'service', 'addons', 'user'])
            ->whereHas('user', function ($q) use ($userFilialId) {
                $q->where('filial_id', $userFilialId);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return view('admin_filial.admin_filial_document.index', compact('documents'));
    }

    public function create()
    {
        $userFilialId = auth()->user()->filial_id;

        // Faqat o‘z filialidagi xizmatlar
        $services = ServicesModel::all();

        // Faqat o‘z filialidagi qo‘shimcha xizmatlar
        $addons = ServicesAddonsModel::all();

        return view('admin_filial.admin_filial_document.create', compact('services', 'addons'));
    }

    // -------------------------------
    // STORE: Ma’lumotni saqlash
    // -------------------------------
    public function store(DocumentCreateRequest $request)
    {

        if ($request->client_id) {
            $clientId = $request->client_id;
        } else {
            $client = ClientsModel::create([
                'name'         => $request->new_client_name,
                'phone_number' => $request->new_client_phone,
                'description'  => $request->new_client_desc,
            ]);
            $clientId = $client->id;
        }

        $service      = ServicesModel::findOrFail($request->service_id);
        $servicePrice = $service->price;
        $deadlineTime = $service->deadline_time;

        $addons_total = 0;
        $addonsData   = [];
        if ($request->addons) {
            $addons = ServicesAddonsModel::whereIn('id', $request->addons)->get();
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

        $document = DocumentsModel::create([
            'client_id'          => $clientId,
            'service_id'         => $request->service_id,
            'service_price'      => $servicePrice,
            'addons_total_price' => $addons_total,
            'deadline_time'      => $deadlineTime,
            'final_price'        => $finalPrice,
            'paid_amount'        => 0,
            'discount'           => $discount,
            'user_id'            => auth()->id(),
            'description'        => $request->description,
            'filial_id'          => auth()->user()->filial_id,
        ]);

        if (! empty($addonsData)) {
            $document->addons()->attach($addonsData);
        }

        return redirect()->route('admin_filial.document.index')
            ->with('success', 'Hujjat muvaffaqiyatli yaratildi!');
    }

}
