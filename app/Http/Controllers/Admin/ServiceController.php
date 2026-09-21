<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ServicesModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\ServiceEditRequest;
use Illuminate\Database\QueryException;
use App\Services\PricingService;

class ServiceController extends Controller
{
    public function index()
    {
        $services=ServicesModel::with('addons')->get();
        return view('admin.service.index',compact('services'));
    }

    public function create()
    {
        return view('admin.service.create');
    }

    public function store(ServiceRequest $request)
    {
        $service=new ServicesModel();
        $service->name=$request->name;
        $service->description=$request->description;
        $service->price=$request->price;
        $service->deadline=$request->deadline;
        $service->save();
        app(PricingService::class)->publishServicePrice($service, (float) $service->price, (int) $service->deadline);
        return redirect()->route('superadmin.service.index')->with('success','Service muvaffaqiyatli yaratildi.');
    }

    public function edit($id)
    {
        $service=ServicesModel::findOrFail($id);
        return view('admin.service.edit',compact('service'));
    }

    public function update(ServiceEditRequest $request, $id)
    {
        $service=ServicesModel::findOrFail($id);
        $service->name=$request->name;
        $service->description=$request->description;
        $service->price=$request->price;
        $service->deadline=$request->deadline;
        $service->save();
        app(PricingService::class)->publishServicePrice($service, (float) $service->price, (int) $service->deadline);
        return redirect()->route('superadmin.service.index')->with('success','Service muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $service=ServicesModel::findOrFail($id);
        if ($service->priceTariffs()->exists()) {
            return redirect()->route('superadmin.service.index')
                ->with('error', 'Bu xizmat tarif tarixida ishlatilgan, o‘chirish o‘rniga yangi status/tarif versiyasi kiriting.');
        }
        try {
            $service->delete();
        } catch (QueryException) {
            return redirect()->route('superadmin.service.index')
                ->with('error', 'Bu xizmat hujjatlarga boglangan, ochirib bolmaydi.');
        }

        return redirect()->route('superadmin.service.index')->with('success','Service muvaffaqiyatli o\'chirildi.');
    }
}
