<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Models\CareerModel;
use Illuminate\Http\Request;
use App\Models\DepartamentsModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Barn\Career\EditCareerRequest;
use App\Http\Requests\Barn\Career\CreateCareerRequest;

class CareerController extends Controller
{
    
    public function index()
    {
        $careers=CareerModel::with(['get_dep'])->paginate(10);
        // dd($careers );
        return view('barn.career.index',compact('careers'));
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
        return view('barn.career.create',compact('array'));
    }


    public function store(CreateCareerRequest $request)
    {
      
       CareerModel::create(['career_name'=>$request->career_name,'rate'=>$request->rate,'divide'=>$request->divide? 1:0,'departament_id'=>$request->branch]);
       return redirect()->route('kadr_role.career_employee.index');
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
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
        $career=CareerModel::where('id','=',$id)->first();
     return view('barn.career.edit',compact('id','career','array'));
    }

  
    public function update(EditCareerRequest $request, $id)
    {
        CareerModel::where('id','=',$id)->update(['career_name'=>$request->career_name,'rate'=>$request->rate,'divide'=>$request->divide? 1:0,'departament_id'=>$request->branch]);
        return redirect()->route('kadr_role.career_employee.index');
    }

   
    public function destroy($id)
    {
        CareerModel::where('id','=',$id)->delete();
        return redirect()->route('kadr_role.career_employee.index');
    }
}
