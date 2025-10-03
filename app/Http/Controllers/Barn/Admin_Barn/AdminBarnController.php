<?php

namespace App\Http\Controllers\Barn\Admin_Barn;

use Illuminate\Http\Request;
use App\Models\GiveItemModel;
use App\Models\CommandToManager;
use App\Models\OrderToBarnModel;
use App\Http\Controllers\Controller;

class AdminBarnController extends Controller
{

    public function index()
    {
     
      
        $orders=OrderToBarnModel::with('get_item_name.get_second')->where('status',0)->orderBy('created_at')->paginate(10);
        // dd($orders);
        return view('barn.admin_barn.index',compact('orders'));
    }

    public function item_for_order($id){
     
  
        $order=OrderToBarnModel::find($id);
        // dd($order->item_id);
        $items=GiveItemModel::with('get_item')->where('item_id',$order->item_id)->where(function ($query) {
            $query->where('status', '=', 0)
                  ->orWhere('status', '=', 3);})->paginate(10);
        // dd($items);
        return view('barn.admin_barn.create',compact('items','id'));
        
    }
    public function update(Request $request, $id)
    {
        // dd($id);

       $order=OrderToBarnModel::find($request->id); 
    //    dd($order);
    
       $order1=OrderToBarnModel::find($request->id)->update(['givet_item_num'=>$order->givet_item_num+1]);

    
       if($order->givet_item_num+1==$order->number_of_item){
        $order2=OrderToBarnModel::find($request->id)->update(['give_status'=>1]);
       }
       $item=GiveItemModel::where('id',$id)->update(['user_id'=>$order->user_id,'status'=>1,'order_id'=>$order->id]);
        
        return to_route('admin_barn_role.admin_actions.index');
    }

  
    
}
