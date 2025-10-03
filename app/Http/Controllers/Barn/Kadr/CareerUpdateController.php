<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Models\ItemsModel;
use App\Models\TypeOfItem;
use App\Models\PrixodModel;
use Illuminate\Http\Request;
use App\Models\CurrencyModel;
use App\Models\GiveItemModel;
use App\Models\SecondTypeOfItem;
use App\Models\CareerUpdatedModel;
use App\Http\Controllers\Controller;
use App\Models\DepartmentKafedraModel;
use App\Models\DepartamentUpdatedModel;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Barn\Rektor\RektorDepartamentGiveItemRequest;

class CareerUpdateController extends Controller
{
  
    public function index()
    {
        $departaments=DepartmentKafedraModel::with(['get_user','get_building'])->orderBy('created_at', 'desc')->withCount('get_give_item')->paginate(25);
       
        // dd($departament->get_give_item()->select('user_id','item_id','status')->get()->unique('item_id'));
        // dd($departaments );
        return view('barn.career_update.index',compact('departaments'));
    }

   
    // public function create()
    // {
    //     $departaments=DepartamentUpdatedModel::get();
    //     return view('barn.career_update.create',compact('departaments'));
    // }
    public function create($departaments=null)
    {
        // $tovars=SecondTypeOfItem::all();
        // dd(1);
        $types=TypeOfItem::all();
        // $currencys=CurrencyModel::get();
        // dd($currencys);
        return view('barn.rektor.dep_and_kafedra.create_for_give',compact('departaments','types',));
    }
    // public function store(Request $request)
    // {   
    //     // dd($request);
    //     $career=CareerUpdatedModel::create($request->all());
    //     return to_route('kadr_role.career_update.index');
    // }        
    

    
    public function show($id)
    {
        // dd($id);
        $departaments=GiveItemModel::with('get_item')
        ->where('dep_id','=',$id)
        ->selectRaw('(item_id) as item_id,count(item_id) as give_item,(give_item.status) as status')
        ->groupBy('give_item.item_id','give_item.status')
        ->paginate(40);
        // $dep_2=GiveItemModel::with('get_item')->where('dep_id',$id)->get();
        // dd($dep_2);
        // dd($departaments);

        return view('barn.rektor.dep_and_kafedra.show',compact('departaments','id'));

       
    }
   
    public function edit($id)
    {
      
    $old = Session::get('departaments');
    // dd($old[$id]);
    $edit_prixod=$old[$id];
    // $types=TypeOfItem::all();
    $items=ItemsModel::all();
    // dd($edit_prixod);
    // $seconds=SecondTypeOfItem::all();
    // $currencys=CurrencyModel::get();


    return view('barn.storekeep.prixod.edit',compact('edit_prixod','items','id'));

    }

   
    
    

   
    // public function update(Request $request, $id)
    // {
         
    //     $post = CareerUpdatedModel::find($id); 

    //     $post->fill($request->all())->save();
        
    //     return to_route('kadr_role.career_update.index');
    // }

  
    // public function destroy($id)
    // {
    //     $post = CareerUpdatedModel::find($id); 

    //     $post->delete();

    //     return to_route('kadr_role.career_update.index');
    // }

    public function update(Request $request, $id)
    {
        $old = Session::get('departaments');
        // $currency=CurrencyModel::where('value',$request->radio)->first();
        $data=['item'=>$request->item,'number'=>$request->number];
        $old[$id]=$data;
        session(['departaments' => $old]);
        return to_route('kadr_role.departament_list');
    }

  
    public function destroy(Request $request)
    {
        // return response()->json([
        //     'id'=>"ok",
        //    ]);
       $id=$request->id;
        $old = Session::get('departaments');
        $edit_prixod=$old[$id];
        unset($old[$id]);
        $new=[];
        foreach ($old as $key => $value) {
           $new[]=$value;
        }
        session(['departaments' => $new]);
        return response()->json([
            'id'=>"ok",
           ]);
        // return to_route('storekeeper_role.prixod_list');
    }

    public function departament_list()
   {
    $departaments = Session::get('departaments');
    // dd($departaments);
    if(Session::has('departaments')){
        $lists=[];
        $departaments = Session::get('departaments');
        foreach ($departaments as $key => $departament) {
            $obj=ItemsModel::with(['get_prixod.get_currency'])->whereIn('id',[$departament['item']])->get();
            array_push($lists,$obj);
        }
    }
    else {
        $departaments=[];
        $lists=[];
    }
    $dep_kafs=DepartmentKafedraModel::where('active_status','=',1)->get();
    return view('barn.rektor.dep_and_kafedra.index_for_give',compact('departaments','lists','dep_kafs'));
   }

   public function store(RektorDepartamentGiveItemRequest $request)
    {
        // dd($request->number);
        $prixods=[];
       
        // $currency=CurrencyModel::where('value',$request->radio)->first();
        // dd($currency);
        $data=['item'=>$request->item,'number'=>$request->number];
     
        if(Session::has('departaments')){
         
            $old = Session::get('departaments');
           
            array_push($old,$data);
            session(['departaments' => $old]);
        }
        else {
            array_push($prixods,$data);
            session(['departaments' => $prixods]);
        }

        return redirect()->route('kadr_role.departament_list');
}

public function store_all_items(Request $request){
    // dd(1);
    $items=json_decode($request->prixods);
    // dd($items);
    // ini_set('max_execution_time', 120 ) ;
    foreach (json_decode($request->prixods) as $key => $prixod) {
        // dd($prixod);

        $give=GiveItemModel::where('item_id',$prixod->item)
        ->where('status',0)
        ->where('dep_id',0)
        ->take($prixod->number)
        ->update(['status'=>1,'dep_id'=>$request->dep_kaf]);
    //     $create=PrixodModel::create(['item_id'=>$prixod->item,
    //     'cargo_id'=>$request->cargo,
    //     'count_of_item'=>$prixod->number,
    //     'cost_of_per'=>$prixod->cost,
    //     'curer_id'=>$request->curer,
    //     'currency_id'=>$prixod->currency_id,
    //     'currency_value'=>$prixod->currency_value,
    // ]);
        $get=ItemsModel::where('id',$prixod->item)->first();
      
        $update=ItemsModel::where('id',$prixod->item)->update(['absent'=>$get->absent+$prixod->number]);
        
       
    }
    // dd(1);
    if(Session::has('departaments')){
        Session::forget('departaments');
        return to_route('kadr_role.career_update.index');
    }else{
        return to_route('kadr_role.career_update.index');
    }
  
}
public function items_give_to_barn(Request $request,$id){
    // dd($request->number);
    $item=ItemsModel::where('id',$request->item)->first();
    $item_give=GiveItemModel::where('item_id',$request->item)->where('dep_id',$id)->where('status',1)->update(['status'=>0,'dep_id'=>0]);
  
    $num=$item->absent-$request->number;
    $item_change=ItemsModel::where('id',$request->item)->update(['absent'=>$num]);
    // dd($item);
    return to_route('kadr_role.career_update.index');
}

public function reset_for_dep($id){

    $departaments=GiveItemModel::where('dep_id','=',$id)
    ->selectRaw('(item_id) as item_id,count(item_id) as give_item,(give_item.status) as status')
    ->groupBy('give_item.item_id','give_item.status')
    ->get();
    // dd($departaments);
    foreach ($departaments as $key => $departament) {
        $item=ItemsModel::where('id',$departament->item_id)->first();
        $item_give=GiveItemModel::where('item_id',$departament->item_id)->where('dep_id',$id)->where('status',1)->update(['status'=>0,'dep_id'=>0]);
        // dd($departament);
        $num=$item->absent-$departament->give_item;
        $item_change=ItemsModel::where('id',$departament->item_id)->update(['absent'=>$num]);
    }
    return to_route('kadr_role.career_update.show',['career_update'=>$id]);
}
}
