<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceEditRequest;
use App\Http\Requests\ServiceRequest;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use Illuminate\Database\QueryException;
use App\Services\PricingService;

class ServiceAddonController extends Controller
{
    public function create($service_id)
    {
        ServicesModel::query()->findOrFail($service_id);
        $service_addons = ServiceAddonModel::where('service_id', $service_id)->get();

        return view('admin.service_addon.create', compact('service_addons', 'service_id'));
    }

    public function store(ServiceRequest $request, $service_id)
    {
        ServicesModel::query()->findOrFail($service_id);
        $addon = new ServiceAddonModel;
        $addon->service_id = $service_id;
        $addon->name = $request->name;
        $addon->description = $request->description;
        $addon->price = $request->price;
        $addon->deadline = $request->deadline;
        $addon->save();
        app(PricingService::class)->publishAddonPrice($addon, (float) $addon->price, (int) $addon->deadline);

        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', 'Service addon muvaffaqiyatli yaratildi.');
    }

    public function edit($service_id, $id)
    {
        ServicesModel::query()->findOrFail($service_id);
        $addon = ServiceAddonModel::query()
            ->where('service_id', $service_id)
            ->findOrFail($id);

        return view('admin.service_addon.edit', compact('addon', 'service_id'));
    }

    public function update(ServiceEditRequest $request, $service_id, $id)
    {
        ServicesModel::query()->findOrFail($service_id);
        $addon = ServiceAddonModel::query()
            ->where('service_id', $service_id)
            ->findOrFail($id);
        $addon->name = $request->name;
        $addon->description = $request->description;
        $addon->price = $request->price;
        $addon->deadline = $request->deadline;
        $addon->save();
        app(PricingService::class)->publishAddonPrice($addon, (float) $addon->price, (int) $addon->deadline);

        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', "Qo'shimcha servis muvaffaqiyatli yangilandi.");
    }

    public function destroy($service_id, $id)
    {
        ServicesModel::query()->findOrFail($service_id);
        $addon = ServiceAddonModel::query()
            ->where('service_id', $service_id)
            ->findOrFail($id);
        if ($addon->priceTariffs()->exists()) {
            return redirect()->route('superadmin.service.index', ['service' => $service_id])
                ->with('error', 'Bu addon tarif tarixida ishlatilgan, o‘chirish o‘rniga yangi tarif versiyasi kiriting.');
        }
        try {
            $addon->delete();
        } catch (QueryException) {
            return redirect()->route('superadmin.service.index', ['service' => $service_id])
                ->with('error', 'Bu qoshimcha servis hujjatlarga boglangan, ochirib bolmaydi.');
        }

        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', "Qo'shimcha servis muvaffaqiyatli o\'chirildi.");
    }
}
