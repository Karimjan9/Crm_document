<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Models\User;
use App\Models\GetJobsModel;
use Illuminate\Http\Request;
use App\Models\DepartamentsModel;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
   
    public function index()
    {
        // $departaments=DepartamentsModel::where('parent_level','!=',0)->get();
        // dd($departaments);
        $departaments=DepartamentsModel::get();
        $array=array();
        $single=array();
        foreach ($departaments as $key => $departament) {
            
            $category = DepartamentsModel::findOrFail($departament->id);
            $children = $category->children()->get();
            if (count($children)>0) {
                $array[]=[$departament,$children];

            }else {
                $single[]=$departament;
            }
           
        }
        return view('barn.department.index',compact('array','single'));
    }

   
    public function create()
    {
        $departaments=DepartamentsModel::get();
        $array=array();
     
        foreach ($departaments as $key => $departament) {
            
            $category = DepartamentsModel::findOrFail($departament->id);
            $children = $category->children()->get();
            if (count($children)>0) {
                $array[]=[$departament,$children];

            }
           
        }
       
        
      
        return view('barn.department.create',compact('array'));
    }

  
    public function store(Request $request)
    {
        $branch=DepartamentsModel::where('id','=',$request->branch)->first();
        DepartamentsModel::create(['name'=>$request->department_name,'parent_id'=>$branch->id,'parent_level'=>$branch->parent_level+1]);
        return redirect()->route('kadr_role.department_employee.index');
    }

    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        $departament=DepartamentsModel::where('id','=',$id)->first();
        return view('barn.department.edit',compact('departament'));
    }

  
    public function update(Request $request, $id)
    {
       
        DepartamentsModel::where('id','=',$id)->update(['name'=>$request->department_name]);
        return redirect()->route('kadr_role.department_employee.index');
    }

    public function destroy($id)
    {
        // dd($id);
        $count=GetJobsModel::where('dep_id',$id)->count();
        // dd($count);
        if ($count==0) {
            DepartamentsModel::where('id','=',$id)->delete();
            session()->flash('success',"Bo'lim  o'chirildi!");
        } else {
            session()->flash('warning',"Bo'lim o'chirilmadi!");
           
        }
        
      
       return redirect()->route('kadr_role.department_employee.index');
    }
}
