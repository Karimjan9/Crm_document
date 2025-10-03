<?php

namespace App\Http\Controllers\Barn\Storekeeper\Items;

use App\Http\Controllers\Controller;
use App\Models\ItemUnityModel;
use Illuminate\Http\Request;

class ItemsUnityController extends Controller
{
   
    public function index()
    {
        $unitys=ItemUnityModel::paginate(10);

        return view('barn.storekeep.items_unity.index',compact('unitys'));
    }


    public function create()
    {
        return view('barn.storekeep.items_unity.create');
    }

 
    public function store(Request $request)
    {
        $item_create=ItemUnityModel::create($request->all());

        return redirect()->route('storekeeper_role.item_unity.index');
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        $unity=ItemUnityModel::find($id);
       
        return view('barn.storekeep.items_unity.edit',compact('unity','id'));
    }

 
    public function update(Request $request, $id)
    {
        $type_update=ItemUnityModel::where('id',$id)->update(['name'=>$request->name]);
        return redirect()->route('storekeeper_role.item_unity.index');
    }

    public function destroy($id)
    {
        //
    }
}
