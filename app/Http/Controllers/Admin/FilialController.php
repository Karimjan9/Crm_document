<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\FilialModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilialCreateRequest;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class FilialController extends Controller
{
    
    public function index()
    {
        $filials=FilialModel::all();
        return view('admin.filial.index',compact('filials'));
    }

   
    public function create()
    {
        return view('admin.filial.create');
    }

   
    public function store(FilialCreateRequest $request)
    {
        try {
            $filial=new FilialModel();
            $filial->name=$request->name;
            $filial->code=$request->code;
            $filial->description=$request->description;
            $filial->save();
            return redirect()->route('superadmin.filial.index')->with('success','Filial muvaffaqiyatli yaratildi');
        } catch (Exception $exception) {
            Log::info($exception);
        }
    }


    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        $filial=FilialModel::find($id);
        if(!$filial){
            return redirect()->route('admin.filial.index')->with('error','Bunday filial mavjud emas');
        }
        return view('admin.filial.edit',compact('filial'));
    }

 
    public function update(FilialCreateRequest $request, FilialModel $filial)
    {
          if (!$filial) {
        return redirect()->route('admin.filial.index')
            ->with('error', 'Bunday filial mavjud emas');
    }

    try {
        $filial->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.filial.index')
            ->with('success', 'Filial muvaffaqiyatli tahrirlandi');
    } catch (Exception $exception) {
        Log::error($exception);
        return redirect()->route('admin.filial.index')
            ->with('error', 'Tahrirlashda xatolik yuz berdi');
    }
    }

 
    public function destroy($id)
    {
        $filial=FilialModel::find($id);
        if(!$filial){
            return redirect()->route('admin.filial.index')->with('error','Bunday filial mavjud emas');
        }
        try {
            $filial->delete();
            return redirect()->route('admin.filial.index')->with('success','Filial muvaffaqiyatli o\'chirildi');
        } catch (Exception $exception) {
            Log::info($exception);
        }
    }
}
