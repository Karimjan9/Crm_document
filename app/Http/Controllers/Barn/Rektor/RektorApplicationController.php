<?php

namespace App\Http\Controllers\Barn\Rektor;

use App\Http\Controllers\Controller;
use App\Models\UserApplicationModel;
use Illuminate\Http\Request;

class RektorApplicationController extends Controller
{
    public function index()
    {
        $applications=UserApplicationModel::orderBy('id','desc')->paginate(20);
        return view('barn.rektor.application.index',compact('applications'));
    }

   
   public function accept_application($id){
        $update=UserApplicationModel::where('id',$id)->update(['confirmed'=>1]);
        return to_route('rektor_role.application_index');
   }

   public function denide_application($id){
    $update=UserApplicationModel::where('id',$id)->update(['confirmed'=>-1]);
    return to_route('rektor_role.application_index');
   }
}
