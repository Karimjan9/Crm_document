<?php

namespace App\Http\Controllers\Barn\TechCheker\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GiveItemModel;
use App\Http\Controllers\Controller;
use App\Models\UserApplicationModel;

class TechChekerUserController extends Controller
{

    public function index()
    {
        
        // $repairs=GiveItemModel::where('repair_status',1)->get();
        // dd($repairs);
        // dd(Auth()->user()->id);
        $user=User::where('level_id','=',7)->first();
        // dd($user);
        $user_id=$user->id;
        $repairs=GiveItemModel::with(['get_item','get_user','get_departament_belong'])->where(function ($query) {
            $query->whereHas('get_item', function ($query) {
                return $query->where('first', '=', 2)->orWhere('first', '=', 3);})
                ->where('repair_status','=',1)
                ->where('repair_allow_status', '=', 0);})
                ->orWhere(function ($query) use($user_id) {
                    $query->where('repair_status','=',1)
                    ->where('repair_allow_status', '=', 0)
                    ->where('repair_allow_user','=',$user_id);
                })->paginate(20);
        // dd($repairs);
        return view('barn.users.tech_cheker.index',compact('repairs','user_id'));
    }


   public function swap_to_other($swap_id){
        // dd($swap_id);
        $user=User::where('level_id','=',8)->first();
        // dd($user);
        $user_id=$user->id;
        // dd($user_id);
        $repair_swap=GiveItemModel::where('id','=',$swap_id)->update(  ['repair_allow_user'=>$user_id]);
        return to_route('tech_checker.tech_checker.index');

   }

   public function accept_to_repair($accept_id){
    dd( $accept_id);
 
    $repair_count=GiveItemModel::where('item_id',$accept_id)->first()->repair_count;
    $accept=GiveItemModel::where('item_id',$accept_id)->update(['repair_allow_status'=>1,'repair_status'=>1,'repair_count'=>$repair_count+1]);
    return to_route('tech_checker.tech_checker.index');
   }
}
