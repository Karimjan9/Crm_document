<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ServicesModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\ServiceEditRequest;

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
        return redirect()->route('superadmin.service.index')->with('success','Service muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $service=ServicesModel::findOrFail($id);
        $service->delete();
        return redirect()->route('superadmin.service.index')->with('success','Service muvaffaqiyatli o\'chirildi.');
    }
}
