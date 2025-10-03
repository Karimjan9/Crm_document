<?php

namespace App\Http\Controllers\Barn\Storekeeper\Prixod;

use App\Http\Controllers\Barn\Storekeeper\Items\ItemsController;
use App\Models\CargoModel;
use App\Models\ItemsModel;
use App\Models\TypeOfItem;
use App\Models\PrixodModel;
use Illuminate\Http\Request;
use App\Models\ProviderModel;
use App\Models\SecondTypeOfItem;
use App\Models\SecondUsersModel;
use App\Http\Controllers\Controller;
use App\Models\CurrencyModel;
use App\Models\GiveItemModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PrixodController extends Controller
{

    public function index()
    {
        $cargos=CargoModel::with(['child'])->orderBy('id', 'DESC')->paginate(30);
            // dd($cargos);
            $all_inf=[];
            foreach ($cargos as $key => $cargo) {
                $prixod_num=0;
                $prixod_cost=0;
                $prixod_currency_cost_all=0;
                $inf=[];
                $prixod_curer=[];
                // $prixods=PrixodModel::with(['get_provider'])->where('cargo_id',$cargo->id)->get();
                foreach ($cargo->child as $key => $prixod) {
                      $prixod_num+=$prixod->count_of_item; 
                      $prixod_cost+=$prixod->cost_of_per*$prixod->count_of_item*$prixod->currency_value;
                      $prixod_currency_cost=$prixod->cost_of_per*$prixod->count_of_item;
                      $prixod_currency_cost_all=$prixod_currency_cost_all+$prixod_currency_cost;
                      $prixod_curer=$prixod->get_provider->name?? "kiritilmagan";
                 
                }
                $inf[]=(int)$prixod_cost;
                $inf[]=$prixod_num;
                $inf[]=$prixod_curer;
                $inf[]=$prixod_currency_cost_all??0;

                $all_inf[]=$inf;
            }
      
        return view('barn.storekeep.prixod.index',compact('cargos','all_inf'));
    }

   public function list_prixod()
   {
   
    $prixods = Session::get('prixods');
    // dd(Session::has('prixods'));   
    if(Session::has('prixods')){
        $lists=[];
        $prixods = Session::get('prixods');
        // dd($prixods);
      
        foreach ($prixods as $key => $prixod) {
            $obj=ItemsModel::with(['get_prixod.get_currency'])->whereIn('id',[$prixod['item']])->get();
            array_push($lists,$obj);
            // dd($obj);
        }
       

    }
    else {
        $prixods=[];
        $lists=[];
    }
    $curers=ProviderModel::all();
    $cargos=CargoModel::where('active_status','=',1)->get();
    // dd($lists);
    return view('barn.storekeep.prixod.index_for_prixod',compact('prixods','curers','lists','cargos'));
   }

 


    public function create($prixods=null)
    {
        // $tovars=SecondTypeOfItem::all();
        // dd(1);
        $types=TypeOfItem::all();
        $currencys=CurrencyModel::get();
        // dd($currencys);
        return view('barn.storekeep.prixod.create',compact('prixods','types','currencys'));
    }

  
    public function store(Request $request)
    {
        // dd($request->cost);
        $prixods=[];
       
        $currency=CurrencyModel::where('value',$request->radio)->first();
        // dd($currency);
        $data=['second'=>$request->second,'type'=>$request->type,'item'=>$request->item,'number'=>$request->number,'cost'=>$request->cost,'currency_id'=>$currency->id,'currency_value'=>$request->radio,'currency_name'=>$currency->name];
     
        if(Session::has('prixods')){
         
            $old = Session::get('prixods');
           
            array_push($old,$data);
            session(['prixods' => $old]);
        }
        else {
            array_push($prixods,$data);
            session(['prixods' => $prixods]);
        }

        return redirect()->route('storekeeper_role.prixod_list');
    }

  
    public function show($id)
    {
       
        $prixods=PrixodModel::with('get_currency')->where('cargo_id',$id)->paginate(40);
        // dd(1);
        return view('barn.storekeep.prixod.show',compact('prixods'));
        
    }

   
    public function edit($id)
    {
      
    $old = Session::get('prixods');
    // dd($old[$id]);
    $edit_prixod=$old[$id];
    $types=TypeOfItem::all();
    $seconds=SecondTypeOfItem::all();
    $items=ItemsModel::all();
    $currencys=CurrencyModel::get();
    // dd($edit_prixod);
    return view('barn.storekeep.prixod.edit',compact('edit_prixod','types','seconds','items','id','currencys'));

    }

   
    public function update(Request $request, $id)
    {
        $old = Session::get('prixods');
        $currency=CurrencyModel::where('value',$request->radio)->first();
        $data=['second'=>$request->second,'type'=>$request->type,'item'=>$request->item,'number'=>$request->number,'cost'=>$request->cost,'currency_id'=>$currency->id,'currency_value'=>$request->radio];
        $old[$id]=$data;
        session(['prixods' => $old]);
        return to_route('storekeeper_role.prixod_list');
    }

  
    public function destroy(Request $request)
    {
        // return response()->json([
        //     'id'=>"ok",
        //    ]);
       $id=$request->id;
        $old = Session::get('prixods');
        $edit_prixod=$old[$id];
        unset($old[$id]);
        $new=[];
        foreach ($old as $key => $value) {
           $new[]=$value;
        }
        session(['prixods' => $new]);
        return response()->json([
            'id'=>"ok",
           ]);
        // return to_route('storekeeper_role.prixod_list');
    }

    public function ajax_get_second_type(Request $request){
        $responses=SecondTypeOfItem::where('type_of_item_id',$request->type)->get();

        return response()->json([
            'responses' => $responses,
        ]);
    }
    
    public function ajax_get_item(Request $request){
        $responses=ItemsModel::where('second',$request->second)->get();

        return response()->json([
            'responses' =>  $responses,
        ]);
    }

    public function store_all(Request $request){
        // dd(1);
        $items=json_decode($request->lists);
        // ini_set('max_execution_time', 120 ) ;
        foreach (json_decode($request->prixods) as $key => $prixod) {
            // dd($prixod);
            $create=PrixodModel::create(['item_id'=>$prixod->item,
            'cargo_id'=>$request->cargo,
            'count_of_item'=>$prixod->number,
            'cost_of_per'=>$prixod->cost,
            'curer_id'=>$request->curer,
            'currency_id'=>$prixod->currency_id,
            'currency_value'=>$prixod->currency_value,
        ]);
            $get=ItemsModel::where('id',$items[$key][0]->id)->first();
          
            $update=ItemsModel::where('id',$items[$key][0]->id)->update(['extant'=>$get->extant+$prixod->number]);
            if ($get->bodily==1) {
                for ($i=0; $i < $prixod->number ; $i++) { 
                    GiveItemModel::create([ 'item_id'=>$prixod->item]);
                    if($i==3000){
                        echo("created 3000K");
                    }elseif ($i==6000) {
                        echo("created 6000K");
                    }elseif ($i==9000) {
                        echo("created 9000K");
                    }elseif ($i==12000) {
                        echo("created 12000K");
                    }
                
                }
            }
           
        }
        // dd(1);
        if(Session::has('prixods')){
            Session::forget('prixods');
            return to_route('storekeeper_role.prixod.index');
        }else{
            return to_route('storekeeper_role.prixod.index');
        }
      
    }
    public function clear_session(){
        if(Session::has('prixods')){
            Session::forget('prixods');}


    
    return redirect()->route('login');
    }
}
