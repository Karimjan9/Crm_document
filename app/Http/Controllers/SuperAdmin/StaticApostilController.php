<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApostilStatikModel;
use Illuminate\Http\Request;

class StaticApostilController extends Controller
{
   
    public function index()
    {
            $groups = ApostilStatikModel::query()
        ->orderBy('group_id')
        ->orderBy('id')
        ->get()
        ->groupBy('group_id');
        return view('super_admin.static_apostil.index',compact('groups'));
        }

    
    public function create()
    {
        
    }

   
    public function store(Request $request)
    {
        
    }

   
    public function show($id)
    {
        
    }

    
    public function edit($id)
    {
        
    }

  
            public function update(Request $request, $id)
        {
            $request->validate([
                'name'  => 'required|string|max:255',
                'price' => 'required|numeric',
                'days'  => 'required|integer',
            ]);

            $item = ApostilStatikModel::findOrFail($id);
            $item->update($request->only('name','price','days'));

            return back()->with('success', 'Apostil muvaffaqiyatli yangilandi ✅');
        }

   
    public function destroy($id)
    {
        $apostil = ApostilStatikModel::findOrFail($id);
        $apostil->delete();
        return redirect()->back()->with('success', 'Apostil muvaffaqiyatli o\'chirildi.');
    }
}
