<?php

namespace App\Http\Controllers\Barn\Admin_Barn;

use Illuminate\Http\Request;
use App\Models\OrderToBarnModel;
use App\Http\Controllers\Controller;
use App\Models\GiveItemModel;

class AcceptedEquipmentController extends Controller
{
    
    public function index()
    {
        $orders=GiveItemModel::with(['get_item.get_second','get_user'])->where('status','=',2)->orderBy('created_at')->paginate(10);
        // dd($orders);
     
        return view('barn.admin_accept.index',compact('orders'));
    }

   

  
    public function update(Request $request, $id)
    {
        
    }

  

}
