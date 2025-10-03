<?php

namespace App\Http\Controllers\Barn\GetJob;

use App\Models\User;
use App\Models\CareerModel;
use App\Models\TypeJobModel;
use App\Models\UserJobsRateCount;
use Illuminate\Http\Request;
use App\Models\DepartamentsModel;
use App\Http\Controllers\Controller;
use App\Models\GetJobsModel;
use App\Models\RateModel;

class GetJobContoller extends Controller
{
    public function index($user_id){
        // dd($user_id);
        $departaments=DepartamentsModel::all();
        $type_of_jobs=TypeJobModel::all();
        // dd($type_of_jobs);
        $user=User::find($user_id);
        $get_jobs=GetJobsModel::where("user_id",$user_id)->get();
        $main=0;
        $out= 0;
        foreach ($get_jobs as $key => $value) {
            $rate=RateModel::where('id', $value->rate_job)->first()->rate;
            // dd($rate);
            if ($value->type_job==1 or $value->type_job== 2) {
                $main=$main+$rate;
            } else {
                $out=$out+$rate;
            }
            
        }
       
        return view('barn.kadr.get_job.index',compact('user_id','departaments','type_of_jobs','user','get_jobs','main','out'));

    }

    public function filter_departament(Request $request){
        $departaments=CareerModel::where('departament_id','=',$request->departament_id)->where('divide_status','<',1)->get();
        
        return response()->json([
            'departaments' => $departaments,
           
        ]);
    }

    public function store(Request $request,$id){    
        $check_inside=GetJobsModel::where('user_id','=',$id)->where('type_job','=',2)->count();
        $check_out=GetJobsModel::where('user_id','=',$id)->where('type_job','=',3)->count();
        $rate=RateModel::where('id',$request->rate_id)->first()->rate;
        // dd($rate);
        $user_check=UserJobsRateCount::where('user_id',$id)->first();
        if ($check_inside>=2 || $check_out>=2) {
            if (($user_check)!=null) {
            
                $user_counter=UserJobsRateCount::where('user_id',$id)->update([
                    'status'=> 1,
                    ]
                );
            }
            $get_job=GetJobsModel::create([
                "user_id"=>$id,
                "dep_id"=>$request->departament_id,
                "career_id"=>$request->degree,
                'rate_job'=>$request->rate_id,
                'type_job'=>$request->type_id
            ]);
        } else {
            $get_job=GetJobsModel::create([
                "user_id"=>$id,
                "dep_id"=>$request->departament_id,
                "career_id"=>$request->degree,
                'rate_job'=>$request->rate_id,
                'type_job'=>$request->type_id
            ]);
        }
        
      
       
      
        $user_check=UserJobsRateCount::where('user_id',$id)->first();
        if (($user_check)!=null) {
            
            $user_counter=UserJobsRateCount::where('user_id',$id)->update([
                'user_id'=>$id,
                'main_type'=>$request->type_id==1? $user_check->main_type+$rate:$user_check->main_type,
                'inside_type'=>$request->type_id==2? $user_check->inside_type+$rate :$user_check->inside_type,
                'out_type'=>$request->type_id==3? $user_check->out_type+$rate :$user_check->out_type,
                'status'=> ($user_check->out_type+$rate==0.75) || ($user_check->main_type+$user_check->inside_type+$rate==1.5)  ? 1:$user_check->status,
                ]
            );
        } else {
            $user_counter=UserJobsRateCount::create([
                'user_id'=>$id,
                'main_type'=>$request->type_id==1?  $rate:0,
                'inside_type'=>$request->type_id==2? $rate :0,
                'out_type'=>$request->type_id==3? $rate :0,
                'status'=>$request->type_id==3 && $request->rate_id==3 ? 1:0,
                ]
            );
        }
        $rate=RateModel::where('id','=',$request->rate_id)->first();
        $career=CareerModel::where('id','=',$request->degree)->first();
        if ($career->divide==0) {
            $career=CareerModel::where('id','=',$request->degree)->update([
                'divide_status'=>1,
                'sum_rate'=>$career->sum_rate+$rate->rate,
            ]);
        } else {
            if ($career->sum_rate+$rate->rate==$career->rate) {
                $career=CareerModel::where('id','=',$request->degree)->update([
                    'divide_status'=>1,
                    'sum_rate'=>$career->sum_rate+$rate->rate,
                ]);
            } else {
                $career=CareerModel::where('id','=',$request->degree)->update([
                    'sum_rate'=>$career->sum_rate+$rate->rate,
                ]);
            }
            
           
        }
        
     
        return redirect()->route('kadr_role.user.data_give',['user'=>$id]);
    }

    public function filter_rate(Request $request){
        $out_rate=UserJobsRateCount::where('user_id',$request->user)->first();
        $rate=CareerModel::where('id','=', $request->career)->first();
        $rate_actual=$rate->rate-$rate->sum_rate;
        if ($request->type_id== 1) {
            if ($rate_actual>1) {
                $type_of_jobs=RateModel::where('rate','<=',1)->get();
            } else {
                $type_of_jobs=RateModel::where('rate','<=',$rate_actual)->get();
            }
            
            
        }
        elseif ($request->type_id== 2) {
           
            if (isset($out_rate)) {
                
                $rate_kof=(1.5-($out_rate->main_type+$out_rate->inside_type))/0.25;
                $rate_actual_2=($rate_actual-(fmod($rate_actual, 0.25)))/(0.25);
                if ($rate_actual_2>$rate_kof) {
                    if ($rate_kof>3) {
                        $type_of_jobs=RateModel::where('id','<=',3)->get();
                    } else {
                        $type_of_jobs=RateModel::where('id','<=',$rate_kof)->get();
                    }
 
                } else {
                    if ($rate_actual_2>3) {
                        $type_of_jobs=RateModel::where('id','<=',3)->get();
                    } else {
                        $type_of_jobs=RateModel::where('id','<=',$rate_actual_2)->get();
                    }
                   
                }
 
             } else {
                if ($rate_actual>0.75) {
                    $type_of_jobs=RateModel::where('rate','<=',0.75)->get();
                } else {
                    $type_of_jobs=RateModel::where('rate','<=',$rate_actual)->get();
                }
 
             }
        }
         elseif($request->type_id== 3) {
           
            if (isset($out_rate)) {
                
               $rate_kof=(0.75-$out_rate->out_type)/0.25;
               $rate_actual_1=($rate_actual-(fmod($rate_actual, 0.25)))/0.25;
               if ($rate_actual_1>$rate_kof) {
                    if ($rate_kof>3) {
                        $type_of_jobs=RateModel::where('id','<=',3)->get();
                    } else {
                        $type_of_jobs=RateModel::where('id','<=',$rate_kof)->get();
                    }

               } else {
               
                if ($rate_actual_1>3) {
                    $type_of_jobs=RateModel::where('id','<=',3)->get();
                } else {
                    $type_of_jobs=RateModel::where('id','<=',$rate_actual_1)->get();
                }
                
               }

            } else {
               
                if ($rate_actual>0.75) {
                        $type_of_jobs=RateModel::where('rate','<=',0.75)->get();
                   } else {
                        $type_of_jobs=RateModel::where('rate','<=',$rate_actual)->get();
                   }
            }

        }

        return response()->json([
            'rates' => $type_of_jobs,
           
        ]);
    }
    public function filter_rate_for_change(Request $request){

        $get_job=GetJobsModel::where('id','=',$request->get_job)->first();
        $get_count=UserJobsRateCount::where('user_id','=',$request->users)->first();
        $get_rat=RateModel::where('id','=',$get_job->rate_job)->first()->rate;
        $except=$get_rat/0.25;
        if ($get_job->type_job=1 && $get_count->inside_type==0.75) {
           $main_value=0.75;
        } elseif($get_job->type_job=1 && $get_count->inside_type!=0.75) {
            $main_value=1;
        }
        if ($request->get_type_job==3) {
          
            $standart=(0.75-$get_count->out_type+$get_rat)/0.25;
        }elseif ($request->get_type_job==2) {
            $standart=(0.75-$get_count->inside_type+$get_rat)/0.25;
        }elseif($request->get_type_job==1) {
           
            $standart=($main_value-$get_count->main_type+$get_rat)/0.25;
        }
        $standart_carerr=CareerModel::where('id','=', $request->career)->first();
        $stan=($standart_carerr->rate-$standart_carerr->sum_rate)/0.25;
        if ($standart<$stan) {
            if ($request->departament_id==$get_job->dep_id && $request->career==$get_job->career_id) {
                $get_rates=RateModel::where('id','<=',$standart)->where('id','!=',$except)->get();
            } else {
                $get_rates=RateModel::where('id','<=',$standart)->get();
            }
 
        } else {
            if ($request->departament_id==$get_job->dep_id && $request->career==$get_job->career_id) {
                $get_rates=RateModel::where('id','<=',$stan)->where('id','!=',$except)->get();
            } else {
                $get_rates=RateModel::where('id','<=',$stan)->get();
            }
          
        }

        return response()->json([
            'rates' => $get_rates,
           
        ]);
    }
}
