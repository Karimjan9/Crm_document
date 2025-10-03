<?php

namespace App\Http\Controllers\Barn\Storekeeper\TypeOfItems;

use App\Http\Controllers\Controller;
use App\Models\SecondTypeOfItem;
use App\Models\TypeOfItem;
use Illuminate\Http\Request;

class SecondTypeOfItemsController extends Controller
{

    public function index()
    {
        $second_types=SecondTypeOfItem::with(['get_first_type'])->paginate(10);
        return view('barn.storekeep.second_types.index',compact('second_types'));
    }

    public function create()
    {
        $first_types=TypeOfItem::all();
        return view('barn.storekeep.second_types.create',compact('first_types'));
    }


    public function store(Request $request)
    {
        // dd(1);
        $create=SecondTypeOfItem::create([
            'name'=>$request->name_of_type,
            'type_of_item_id'=>$request->first_type
        ]);
        return redirect()->route('storekeeper_role.second_type_item.index');
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $second_type=SecondTypeOfItem::where('id',$id)->first();
        $first_types=TypeOfItem::all();
        return view('barn.storekeep.second_types.edit',compact('second_type','id','first_types'));
    }


    public function update(Request $request, $id)
    {
        
        
        $second_type_update=SecondTypeOfItem::where('id',$id)->update([
            'name'=>$request->name_of_type,
            'type_of_item_id'=>$request->first_type
        ]);

        return redirect()->route('storekeeper_role.second_type_item.index');
    }


    public function destroy($id)
    {
        $delete=SecondTypeOfItem::where('id',$id)->delete();
        return redirect()->route('storekeeper_role.second_type_item.index');
    }
}
