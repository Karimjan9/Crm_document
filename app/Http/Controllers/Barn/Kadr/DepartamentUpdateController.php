<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Models\User;
use App\Models\ItemsModel;
use Illuminate\Http\Request;
use App\Models\BuildingModel;
use App\Http\Controllers\Controller;
use App\Models\DepartmentKafedraModel;
use App\Models\DepartamentUpdatedModel;
use Illuminate\Support\Facades\Session;

class DepartamentUpdateController extends Controller
{
   
    public function index()
    {
        $departaments=DepartmentKafedraModel::with(['get_user','get_building'])->orderBy('created_at', 'desc')->paginate(50 );
        // dd($departaments);
        return view('barn.departament_update.index',compact('departaments'));
    }

    
    public function create()
    {
        $users=User::where('level_id',6)->get();
        // dd($users);
        $buildings=BuildingModel::get();
        // dd($buildings);
        return view('barn.departament_update.create',compact('users','buildings'));
    }

    public function store(Request $request)
    {
        DepartmentKafedraModel::create($request->all());

        return to_route('kadr_role.department_update.index');
   
    }

    public function show($id)
    {
        
    }

  
    public function edit($id)
    {
        $departament=DepartmentKafedraModel::where('id','=',$id)->first();
        // dd($departament);
        $users=User::where('level_id',6)->orderByDesc('id')->get();
        // dd($users);
        $buildings=BuildingModel::get();
        return view('barn.departament_update.edit',compact('departament','users','buildings'));
    }

 
    public function update(Request $request, $id)
    {
       
        $post = DepartmentKafedraModel::find($id); 

        $post->fill($request->all())->save();
        
        return to_route('kadr_role.department_update.index');
        
    }

 
    public function destroy($id)
    {
        $post = DepartamentUpdatedModel::find($id); 

        $post->delete();

        return to_route('kadr_role.department_update.index');

    }
    
    public function switch_departament(Request $request)
    {
       $dep=DepartmentKafedraModel::where('id','=',$request->id)->first();
       if ($dep->active_status==1) {
        $dep=DepartmentKafedraModel::find($request->id)->update(['active_status'=>0]);
       } else {
        $dep=DepartmentKafedraModel::find($request->id)->update(['active_status'=>1]);
       }
        
       return response()->json([
        'responses' => $dep,
    ]);
    }

    
}
