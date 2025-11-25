<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceEditRequest;
use App\Http\Requests\ServiceRequest;
use App\Models\ServiceAddonModel;

class ServiceAddonController extends Controller
{
    public function create($service_id)
    {
        $service_addons = ServiceAddonModel::where('service_id', $service_id)->get();
        return view('admin.service_addon.create', compact('service_addons', 'service_id'));
    }

    public function store(ServiceRequest $request, $service_id)
    {
        $addon = new ServiceAddonModel();
        $addon->service_id = $service_id;
        $addon->name = $request->name;
        $addon->description = $request->description;
        $addon->price = $request->price;
        $addon->deadline = $request->deadline;
        $addon->save();
        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', 'Service addon muvaffaqiyatli yaratildi.');
    }

    public function edit($service_id, $id)
    {
        $addon = ServiceAddonModel::findOrFail($id);
        return view('admin.service_addon.edit', compact('addon', 'service_id'));
    }

    public function update(ServiceEditRequest $request, $service_id, $id)
    {
        $addon = ServiceAddonModel::findOrFail($id);
        $addon->name = $request->name;
        $addon->description = $request->description;
        $addon->price = $request->price;
        $addon->deadline = $request->deadline;
        $addon->save();
        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', "Qo'shimcha servis muvaffaqiyatli yangilandi.");
    }

    public function destroy($service_id, $id)
    {
        $addon = ServiceAddonModel::findOrFail($id);
        $addon->delete();
        return redirect()->route('superadmin.service.index', ['service' => $service_id])->with('success', "Qo'shimcha servis muvaffaqiyatli o\'chirildi.");
    }

}
