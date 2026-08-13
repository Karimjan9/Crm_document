<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApostilStatikModel;
use App\Models\DocumentsModel;
use App\Models\PackageTemplate;
use App\Models\PackageTemplateItem;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaticApostilController extends Controller
{
    public function index()
    {
        $groups = ApostilStatikModel::query()
            ->orderBy('group_id')
            ->orderBy('id')
            ->get()
            ->groupBy('group_id');

        return view('super_admin.static_apostil.index', compact('groups'));
    }

    public function create()
    {
        return view('super_admin.static_apostil.form', [
            'apostil' => new ApostilStatikModel(),
        ]);
    }

    public function store(Request $request)
    {
        $apostil = ApostilStatikModel::create($this->validatedData($request));
        app(PricingService::class)->publishFixedPrice(
            'apostille',
            (int) $apostil->id,
            'apostille:' . $apostil->id,
            (string) $apostil->name,
            (float) $apostil->price,
            (int) $apostil->days,
        );

        return redirect()
            ->route('superadmin.apostil.index')
            ->with('success', 'Apostil muvaffaqiyatli qo\'shildi.');
    }

    public function show($id)
    {
        return redirect()->route('superadmin.apostil.edit', $id);
    }

    public function edit($id)
    {
        return view('super_admin.static_apostil.form', [
            'apostil' => ApostilStatikModel::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = ApostilStatikModel::findOrFail($id);
        $item->update($this->validatedData($request, $item));
        app(PricingService::class)->publishFixedPrice(
            'apostille',
            (int) $item->id,
            'apostille:' . $item->id,
            (string) $item->name,
            (float) $item->price,
            (int) $item->days,
        );

        return redirect()
            ->route('superadmin.apostil.index')
            ->with('success', 'Apostil muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $apostil = ApostilStatikModel::findOrFail($id);

        $isUsed = DocumentsModel::query()
            ->where('apostil_group1_id', $apostil->id)
            ->orWhere('apostil_group2_id', $apostil->id)
            ->exists();

        $isUsedInPackage = PackageTemplate::query()
            ->where('apostil_group1_id', $apostil->id)
            ->orWhere('apostil_group2_id', $apostil->id)
            ->exists();

        $isUsedInPackageItem = PackageTemplateItem::query()
            ->where('apostil_group1_id', $apostil->id)
            ->orWhere('apostil_group2_id', $apostil->id)
            ->exists();

        if ($isUsed || $isUsedInPackage || $isUsedInPackageItem) {
            return back()->with('error', 'Bu apostil hujjat yoki paket shablonlarida ishlatilgan, o\'chirib bo\'lmaydi.');
        }

        $apostil->delete();

        return redirect()
            ->route('superadmin.apostil.index')
            ->with('success', 'Apostil muvaffaqiyatli o\'chirildi.');
    }

    protected function validatedData(Request $request, ?ApostilStatikModel $existing = null): array
    {
        $groupId = $request->integer('group_id');

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('apostil_static', 'name')
                    ->where(fn ($query) => $query->where('group_id', $groupId))
                    ->ignore($existing?->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'days' => ['required', 'integer', 'min:0'],
            'group_id' => ['required', 'integer', 'in:1,2'],
        ]);

        if ($groupId < 1 || $groupId > 2) {
            throw ValidationException::withMessages([
                'group_id' => 'Apostil guruhi noto\'g\'ri.',
            ]);
        }

        return $data;
    }
}
