<?php

namespace App\Http\Controllers\Barn\Storekeeper\TypeOfItems;

use App\Http\Controllers\Controller;
use App\Models\TypeOfItem;
use Illuminate\Http\Request;

class TypeOFItemsController extends Controller
{
    public function index()
    {
        $types=TypeOfItem::paginate(10);
      return view('barn.storekeep.items_types.index',compact('types'));
    }

  
    public function create()
    {
        // dd(1);
        return view('barn.storekeep.items_types.create');
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $new_item=TypeOfItem::create($request->all());
        return redirect()->route('storekeeper_role.type_item_take.index');
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $type=TypeOfItem::where('id',$id)->first();
        return view('barn.storekeep.items_types.edit',compact('type','id'));
    }


    public function update(Request $request, $id)
    {
        $type_update=TypeOfItem::where('id',$id)->update(['name_of_type'=>$request->name_of_type]);
        return redirect()->route('storekeeper_role.type_item_take.index');
    }


    public function destroy($id)
    {
        // dd(1);
        $type_delete=TypeOfItem::where('id',$id)->delete();
        return redirect()->route('storekeeper_role.type_item_take.index');
    }
}
