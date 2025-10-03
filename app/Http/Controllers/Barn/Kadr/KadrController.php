<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Models\UserJobsRateCount;
use Carbon\Carbon;
use App\Models\User;
use App\Models\FileModel;
use App\Models\CareerModel;
use App\Models\CommandModel;
use Illuminate\Http\Request;
use App\Models\CareerFileModel;
use App\Models\SecondUsersModel;
use App\Models\DepartamentsModel;
use App\Models\AcademicTitleModel;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicDegreeModel;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Barn\Kadr\File\FileRequest;
use App\Http\Requests\Barn\Kadr\User\UserRequest;
use App\Http\Requests\Barn\Kadr\Career\CareerFileRequest;
use App\Http\Requests\Barn\SecondDataFiles\SecondDataRequest;
use App\Models\CareerUpdatedModel;
use App\Models\DepartamentUpdatedModel;
use App\Models\GetJobsModel;
use App\Models\RateModel;
use App\Models\UserJobsModel;
use App\Models\UserLevel;

class KadrController extends Controller
{
  
    public function index()
    {
        $users=User::with(['user_job.user_career','user_job.user_dep'])->where('level_id','=',6)->orwhere('level_id','=',7)->orwhere('level_id','=',8)->orderByDesc('id')->paginate(20);
        // dd($users);
        // $search=0;
      
        return view('barn.kadr.index',compact('users'));
    }

    public function create()
    {
        $departaments=DepartamentUpdatedModel::get();
        // $array=array();
     
        // foreach ($departaments as $key => $departament) {
            
        //     $category = DepartamentsModel::findOrFail($departament->id);
        //     $children = $category->children()->get();
        //     if (count($children)>0) {
        //         $array[]=[$departament,$children];

        //     }
           
        // }
        // $careers=CareerModel::get();
        // dd($departaments);
        return view('barn.kadr.create',compact('departaments'));
    }


    // UserRequest
    public function store(UserRequest $request)
    {
       
        $password=Hash::make($request->password);
        $user=User::create(['full_name'=>$request->full_name,'login'=>$request->login,'level_id'=>6,'password'=>$password,]);

        // departament
        // dd($request->departament);
        // $job=UserJobsModel::create(['user_id'=>$user->id,'dep_id'=>$request->departament,'career_id'=>$request->career]);
        return redirect()->route('kadr_role.actions.index');
    }

  
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $user=User::with(['user_job.user_career','user_job.user_dep'])->where('id','=',$id)->first();
        // dd($user);
        $levels=UserLevel::get();
        $departaments=DepartamentUpdatedModel::get();
        $careers=CareerUpdatedModel::get();
        // dd($levels);
        return view('barn.kadr.edit',compact('user','departaments','careers','levels'));
    }

 
    public function update(Request $request, $id)
    {
        $password=Hash::make($request->password);
        // $department_id=User::where('id','=',$id)->first()->departament_id;
        
        // dd($request->user_level);
        
        User::where('id','=',$id)->update(['full_name'=>$request->name,'login'=>$request->login,'password'=>$password,'level_id'=>$request->user_level]);
        // $job=UserJobsModel::where('user_id','=',$id)->update(['dep_id'=>$request->departament,'career_id'=>$request->career]);
        return redirect()->route('kadr_role.actions.index');
    }


    public function destroy($id)
    {
        //
    }

    public function change_departament(Request $request,$id)
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
        $users=User::where('id','=',$id)->first();

        return view('barn.kadr.change',compact('array','users'));
    }
    public function change_store(FileRequest $request,$id)
    {
       
        $extension = $request->file('file')->getClientOriginalExtension();

        $statement = DB::select("SHOW TABLE STATUS LIKE 'file'");
        $nextId = $statement[0]->Auto_increment;
       
        $data = $request->all();
    
        $data['user_id'] = $id;
       
        $filenameWithExt = $request->file('file')->getClientOriginalName();
      
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
       
        $data['new_name']="$filename"."."."$extension";
        $data['old_name']='File_'."$nextId";
        $day=Carbon::now();
     
        $data['path_of_file']="/"."$day->year"."/"."$day->month"."/"."$day->day";
    
        $data['exp']="."."$extension";
       
        $file_id=FileModel::create($data);

       
      
       
       
        $fileNameToStore = $data['old_name'].'.'.$extension;
       
        $put="public/files".$data['path_of_file'];
       
        $path = $request->file('file')->storeAs($put,$fileNameToStore);

        $old_name_id=User::where('id','=',$id)->first()->departament_id;
        $command=CommandModel::create(['user_id'=>$id,'old_dep'=>$old_name_id,'new_dep'=>$request->branch,'file_id'=>$file_id->id,'order_id'=>1]);
        $update=User::where('id','=',$id)->update(['departament_id'=>$request->branch]);
        return redirect()->route('kadr_role.actions.index');
    }
    
    public function change_career(Request $request,$id,$get_job){
    
        $users=User::where('id','=',$id)->first();
        $departaments=DepartamentsModel::get();
        $get_job_id=$get_job;
        $get_job=GetJobsModel::where('id','=',$get_job)->first();

        $careers=CareerModel::where('id','=',$get_job->career_id)->get();
        $get_count=UserJobsRateCount::where('user_id','=',$id)->first();
        $get_rat=RateModel::where('id','=',$get_job->rate_job)->first()->rate;
        $except=$get_rat/0.25;
        if ($get_job->type_job==1 && $get_count->inside_type==0.75) {
            $main_value=0.75;
         } elseif($get_job->type_job==1 && $get_count->inside_type!=0.75) {
             $main_value=1;
         }
        if ($get_job->type_job==3) {
            $sum=$get_count->out_type;
            $standart=(0.75-$sum+$get_rat)/0.25;
        }elseif ($get_job->type_job==2) {
            $sum=$get_count->inside_type;
            $standart=(0.75-$sum+$get_rat)/0.25;
        }elseif($get_job->type_job=1) {
            $sum=$get_count->main_type;
            $standart=($main_value-$sum+$get_rat)/0.25;
        }
       $get_rates=RateModel::where('id','<=',$standart)->where('id','!=',$except)->get();
   
       $get_type_job=$get_job->type_job;
      
        return view('barn.kadr.career.create',compact('users','careers','departaments','get_job','get_rates','get_job_id','get_type_job'));
    }

    public function change_store_career(CareerFileRequest $request,$id){

        $old_job=GetJobsModel::where('id','=', $request->get_job)->withTrashed()->first();
        $type=$old_job->type_job;
        $old_rate=RateModel::where('id',$old_job->rate_job)->first()->rate;
      
        $old_job->delete();
        $extension = $request->file('file')->getClientOriginalExtension();
        $statement = DB::select("SHOW TABLE STATUS LIKE 'file'");
        $nextId = $statement[0]->Auto_increment;
        $data = $request->all();
        $data['user_id'] = $id;
        $filenameWithExt = $request->file('file')->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $data['new_name']="$filename"."."."$extension";
        $data['old_name']='File_'."$nextId";
        $day=Carbon::now();
        $data['path_of_file']="/"."$day->year"."/"."$day->month"."/"."$day->day";
        $data['exp']="."."$extension";
        $file_id=FileModel::create($data);
        $fileNameToStore = $data['old_name'].'.'.$extension;
        $put="public/files".$data['path_of_file'];
        $path = $request->file('file')->storeAs($put,$fileNameToStore);
        $rate=RateModel::where('id',$request->rate_id)->first()->rate;
        $new_job = GetJobsModel::create(['user_id'=>$id,'dep_id'=>$request->departament_id,'career_id'=>$request->career, 'rate_job'=>$request->rate_id,'type_job'=>$type]);
        $current_rate=UserJobsRateCount::where('user_id',$id)->first();
        $count_of_jobs=GetJobsModel::where('user_id',$id)->count();
      
        $current_career_rate=CareerModel::where('id','=',$request->career)->first();
        // dd($current_career_rate);
        $final_rate_career=$current_career_rate->rate-$old_rate+$rate;
       

        $final_rate_main=$current_rate->main_type-$old_rate+$rate;
        $final_rate_inside=$current_rate->inside_type-$old_rate+$rate;
        $final_rate_out=$current_rate->out_type-$old_rate+$rate;
      
        if ($type==1) {
            if ($current_rate->inside_type+$final_rate_main==1.5) {
              
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['main_type'=>$final_rate_main,'status'=>1]);
            }
            elseif ($count_of_jobs==4) {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['inside_type'=>$final_rate_main,'status'=> 1]);
            }
             else {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['main_type'=>$final_rate_main,'status'=>0]);
            }

        }
      
        elseif ($type==2) {
            if ($current_rate->main_type+$final_rate_inside==1.5) {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['inside_type'=>$final_rate_inside,'status'=> 1]);
            }elseif ($count_of_jobs==4) {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['inside_type'=>$final_rate_inside,'status'=> 1]);
            } else {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['inside_type'=>$final_rate_inside,'status'=> 0]);
            }
            
          
        }
        elseif ($type==3) {
            if ($final_rate_out==0.75) {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['out_type'=>$final_rate_out,'status'=>1]);
            } else {
                $update_counter=UserJobsRateCount::where('user_id',$id)->update(['out_type'=>$final_rate_out,'status'=>0]);
            }
            
           
        }
        $career_update=CareerModel::where('id','=',$request->career)->update(['sum_rate'=>$final_rate_career]);
        $command=CommandModel::create(['user_id'=>$id,'file_id'=>$file_id->id,'old_job'=>$request->get_job,'new_job'=>$new_job->id]);

        return redirect()->route('kadr_role.user.data_give',['user'=>$id]);
    }

    public function second_data($id){
        $academic_title=AcademicTitleModel::get();
        $academic_degree=AcademicDegreeModel::get();
        $second=SecondUsersModel::where('user_id','=',$id)->first();
        return view('barn.kadr.second_data_index',compact('id','academic_title','academic_degree','second'));
    }

    public function second_data_store(SecondDataRequest $request ,$id){
        $check=SecondUsersModel::where('user_id','=',$id)->first();
        $user=User::find($id);
        if($check==null){
           
           
            $a=0;
            if ($request->file('image')!=null) {
                $extension_img = $request->file('image');
                $image_put="public/files/image/";
                $filename_img=( date("s-i-H-d-m-Y",$extension_img->getATime()))."-"."$user->full_name".".".$extension_img->getClientOriginalExtension();
                $path_img = $request->file('image')->storeAs($image_put,$filename_img);
                $a=$a+1;
            }
            if ($request->file('diplom')!=null) {
                $extension_diplom= $request->file('diplom');
                $diplom_put="public/files/diplom/";
                $filename_diplom=( date("s-i-H-d-m-Y",$extension_diplom->getATime()))."-"."$user->full_name".".".$extension_diplom->getClientOriginalExtension();
                $path_diplom = $request->file('diplom')->storeAs($diplom_put,$filename_diplom);
                $a=$a+1;
            }
            if ($request->file('obektivka')!=null) {
                $extension_obektivka=$request->file('obektivka');
                $obektivka_put="public/files/obektivka/";
                $filename_obektivka=( date("s-i-H-d-m-Y",$extension_obektivka->getATime()))."-"."$user->full_name".".".$extension_obektivka->getClientOriginalExtension();
                $path_img = $request->file('obektivka')->storeAs($obektivka_put,$filename_obektivka);
                $a=$a+1;
            }
            $percent=40+($a*20);
         
           
            $second=SecondUsersModel::create([ "user_id"=>$id,
            "obektivka"=> $filename_obektivka??null,
            "diplom"=> $filename_diplom??null,
            "user_img"=>$filename_img??null,
            "academic_title_id"=>$request->title,
            "academic_degree_id"=>$request->degree,
            "percent"=>$percent]);
         return redirect()->route("kadr_role.actions.index");
        }else{
            $a=0;
            if ($request->file('image')!=null) {
                if($check->user_img==null){
                    $a=$a+1;
                }
                $extension_img = $request->file('image');
                $image_put="public/files/image/";
                $filename_img=( date("s-i-H-d-m-Y",$extension_img->getATime()))."-"."$user->full_name".".".$extension_img->getClientOriginalExtension();
                $path_img = $request->file('image')->storeAs($image_put,$filename_img);
               
            }
            if ($request->file('diplom')!=null) {
                if($check->diplom==null){
                    $a=$a+1;
                }
                $extension_diplom= $request->file('diplom');
                $diplom_put="public/files/diplom/";
                $filename_diplom=( date("s-i-H-d-m-Y",$extension_diplom->getATime()))."-"."$user->full_name".".".$extension_diplom->getClientOriginalExtension();
                $path_diplom = $request->file('diplom')->storeAs($diplom_put,$filename_diplom);
               
            }
            if ($request->file('obektivka')!=null) {
                if($check->obektivka==null){
                    $a=$a+1;
                }
                $extension_obektivka=$request->file('obektivka');
                $obektivka_put="public/files/obektivka/";
                $filename_obektivka=( date("s-i-H-d-m-Y",$extension_obektivka->getATime()))."-"."$user->full_name".".".$extension_obektivka->getClientOriginalExtension();
                $path_img = $request->file('obektivka')->storeAs($obektivka_put,$filename_obektivka);
               
            }
            $percent=$check->percent+($a*20);
            $second=SecondUsersModel::where('user_id','=',$id)->update([
            "obektivka"=> $filename_obektivka??$check->obektivka??null,
            "diplom"=> $filename_diplom??$check->diplom??null,
            "user_img"=>$filename_img??$check->user_img??null,
            "academic_title_id"=>$request->title,
            "academic_degree_id"=>$request->degree,
            "percent"=>$percent]);
         return redirect()->route("kadr_role.actions.index");
        }
       
    }
    public function data_give($id){
        $user=User::with(['user_second'])->find($id);
        $get_jobs=GetJobsModel::with(['get_departament','get_rate','get_type','get_career'])->where('user_id','=',$id)->get();
        if ($user->user_second->user_img==null) {
            $user_image="user_null.webp";
        }else {
            $user_image=$user->user_second->user_img;
        }
       
    
        return view('barn.kadr.enter_data.index',compact('id','user','user_image','get_jobs'));
    }

   
     public function  filter_departament(Request $request){
        $second=CareerUpdatedModel::where('departament_id',$request->dep_id)->get();
        return response()->json([
            'responses'=>$second,
        ]);
     }
}
