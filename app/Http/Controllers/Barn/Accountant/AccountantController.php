<?php

namespace App\Http\Controllers\Barn\Accountant;

use App\Models\ItemsModel;
use App\Models\TypeOfItem;
use Illuminate\Http\Request;
use App\Models\UsersAskModel;
use App\Models\BodilyTypeModel;
use App\Models\CommandToManager;
use App\Models\OrderToBarnModel;
use App\Models\SecondTypeOfItem;
use App\Http\Controllers\Controller;
use App\Models\GiveItemModel;
use Illuminate\Support\Facades\Session;

class AccountantController extends Controller
{
   
    public function index()
    {
      
        $asks=UsersAskModel::where('status',1)->paginate(10);
        // dd($asks);
        return view('barn.accountant.index',compact('asks'));
    }
                
    
    public function create(Request $request)
    {
        // dd($request->show_id);
        $user_ask=UsersAskModel::where('id',$request->show_id)->first()->number;
        if ($request->number>0 && $request->number!=null && $user_ask>=$request->number) {
            $item=ItemsModel::where('id',$request->item)->first();
            $little=[];
            $lists=[];
        if(Session::has('items')){
        
            $data = Session::get('items');
            $last = Session::get('last');

           $last=$last-$request->number;
    
            $item->get=$request->number;
            array_push($data,$item);

      
            session(['last' => $last]);
            session(['items' => $data]);
        }else {
            $then=$request->ask-$request->number;

            $item->get=$request->number;
            array_push($little,$item);
            session(['last' => $then]);
            session(['items' => $little]);
            
        }
        session()->flash('success',"Qabul qilindi!");
            return to_route('bugalter_role.accountant.show',['accountant'=>$request->show_id]);
        } else {
            session()->flash('warning',"Katta son berilgan");
            return to_route('bugalter_role.accountant.show',['accountant'=>$request->show_id]);
        }
        
       
       
    }


    public function store(Request $request)
    {
        $ask=UsersAskModel::where('id',$request->show_id)->first();
        $sum=OrderToBarnModel::where('ask_id',$ask->id)->sum('number_of_item');

        $data = Session::get('items');
        $count=0;
       
        // dd($sum);
        foreach ($data as $key => $item) {
        // dd(1);
          if ($item->get>0 && $ask->number>=$item->get+$sum) {
            $new=OrderToBarnModel::create(['item_id'=>$item->id,'user_id'=>$ask->user_id,'number_of_item'=>$item->get,'ask_id'=>$ask->id]);
            // GiveItemModel::where('item_id',$item->id)->where('status',0)->limit($item->get)->update(['user_id'=>$ask->user_id,'status'=>1]);
            $sum_2=OrderToBarnModel::where('ask_id',$ask->id)->sum('number_of_item');
            if ($ask->number==$sum_2) {
                UsersAskModel::where('id',$request->show_id)->update(['ordered'=>2]);
            } else {
                UsersAskModel::where('id',$request->show_id)->update(['ordered'=>1]);
            } 
            }else{
                return to_route('bugalter_role.accountant.index');
            }
          
       
        }
        
        Session::forget('items');
        Session::forget('last');
     
      
       
        return to_route('bugalter_role.accountant.index');
    }


    public function show($id)
    {
      

        $ask=UsersAskModel::where('id',$id)->first();
        $items=ItemsModel::where('second',$ask->second_id)->get();
        $sum=OrderToBarnModel::where('ask_id',$ask->id)->sum('number_of_item');
      
        
        if(Session::has('items')){

            $data = Session::get('items');
          
            $last=  Session::get('last');
            
        }else {
            
           $data=[];
           $last=$ask->number;
        }
        if($data==null){
            $data=[];
        }
        $last=$last-$sum;
       
        if ($last<0) {
            $last=0;
        } 
        
        // dd($ask);
        return view('barn.accountant.show',compact('items','ask','data','id','data','last'));

    }

    
    public function edit($id)
    {
        
    }


    public function update(Request $request, $id)
    {
        
    }


    public function destroy($id)
    {
        
    }

    public function to_manager($id){
        $create=CommandToManager::create(['ask_id'=>$id]);
        return to_route('bugalter_role.accountant.index');
    }

    public function show_accountant(){
        $all_items=ItemsModel::paginate(20);
        // dd($all_items);
        $firsts=TypeOfItem::get();
      
        $bodilys=BodilyTypeModel::get();
        return view('barn.accountant.item_show.index',compact('all_items','firsts','bodilys'));
    }

    public function ajax_get_without_bodily_accountant(Request $request){
        $items=ItemsModel::with(['get_first','get_second'])->where('first',$request->first_filter)->where('second',$request->second_filter)->get();
        return response()->json([
            'responses'=>$items,
        ]);
    }
     
     public function ajax_get_with_bodily_accountant(Request $request){
        $items=ItemsModel::with(['get_first','get_second'])->where('first',$request->first_filter)->where('second',$request->second_filter)->where('bodily',$request->bodily)->get();
        
        
        return response()->json([
            'responses'=>$items,
        ]);
     }
     public function ajax_get_second_types_accountant(Request $request ){
        $second=SecondTypeOfItem::where('type_of_item_id',$request->first_filter)->get();
        return response()->json([
            'responses'=>$second,
        ]);
     }
     public function ajax_acco_second_types(Request $request){
        $second=SecondTypeOfItem::where('type_of_item_id',$request->first_filter)->get();
        return response()->json([
            'responses'=>$second,
        ]);
     }
     public function show_orders(){
        $commands=CommandToManager::with(['get_ask'])->paginate(10);
        // dd($commands);
        
        return view('barn.accountant.show_orders.index',compact('commands'));
     }
}
