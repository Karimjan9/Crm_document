<?php

namespace App\Http\Controllers\SuperAdmin\SMS;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PlayMobileModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\PlayMobile\PlayMobileRequest;

class PlayMobileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
    
        $messages=PlayMobileModel::where('sender_id','=',$id)->get();
        $teachers=User::where('level_id','=',3)->get();
 
        return view('super.sms.index',compact('messages','id','teachers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {      
        $users=User::where('level_id','=',2)->Orwhere('level_id','=',3)->get();
        return view('super.sms.create',compact('users','id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function getHeader():array
    {
        $login=config('app.sms_login');
        $password=config('app.sms_password');
        return [
            'Authorization' => 'Basic '.base64_encode("$login:$password"),
            'Content-Type' => 'application/json'
        ];
    }
    public function store(PlayMobileRequest $request,$id)
    {
       $number_phone=User::find($request->user)->number_phone;
       if ( $number_phone==null) {
          
        session()->flash('warning',"Nomer Telefon topilmadi");
        return redirect()->route('superadmin.sms_message.index',['admin_id'=>$id]);
       }
        $statement = DB::select("SHOW TABLE STATUS LIKE 'play_mobile'");
        $nextId = $statement[0]->Auto_increment;

      
        $message_id="OXU"."000"."$nextId";
       

        $login=config('app.sms_login');
        $password=config('app.sms_password');
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode("$login:$password"),
            'Content-Type' => 'application/json'
        ])->post('https://send.smsxabar.uz/broker-api/send', [
            
              'messages'=>[
                "recipient"=>"$number_phone",
                "message-id"=>"$message_id",
              ],
              "sms"=>[
                "originator"=> "3700",
              "content"=>[
               "text"=> "$request->message_body"
              ]
              ]
               
        ]);
           
        if ($response->getStatusCode() >= 300) {
            PlayMobileModel::create(['sender_id'=>$id,'sms_body'=>$request->message_body,'taker_id'=>$request->user,'message_id'=>$message_id,'status_sms'=>0,'type_sms'=>$request->sms_filter]);
            session()->flash('warning',"Xabar jo'natilmadi");
            return redirect()->route('superadmin.sms_message.index',['admin_id'=>$id]);
         } else {
            PlayMobileModel::create(['sender_id'=>$id,'sms_body'=>$request->message_body,'taker_id'=>$request->user,'message_id'=>$message_id,'status_sms'=>1,'type_sms'=>$request->sms_filter]);
                session()->flash('success',"Xabar jo'natildi");
                return redirect()->route('superadmin.sms_message.index',['admin_id'=>$id]);
         }
        }

        // $client = new Client();

        // $body = '{
        //     "messages": [
        //       {
        //         "recipient": "' . 998998917785 . '",
        //         "message-id": "oxu0001' . 1 . '",
        //         "sms": {
        //           "originator": "3700",
        //           "content": {
        //             "text": "Security code:' . 777 . '"
        //           }
        //         }
        //       }
        //     ]
        // }';
        // try {
        //     $request = new GuzlRequest('POST', 'https://send.smsxabar.uz/broker-api/send', $this->getHeader(), $body);
        //     $client->sendAsync($request)->wait();
          
        //     return true;
        // } catch (\Throwable $th) {
        //     return false;
        // }
        
       
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    
    public function get_sms_filter(Request $request,$id)
    {
       
     
       if ($request->input('sms_filter')<3) {
        $messages=PlayMobileModel::with(['user_sender', 'user_taker'])->where('sender_id','=',$id)->whereMonth('created_at',$request->input('month_filter'))->where('type_sms','=', $request->input('sms_filter'))->get();
       } else {
        $messages=PlayMobileModel::with(['user_sender', 'user_taker'])->where('sender_id','=',$id)->whereMonth('created_at',$request->input('month_filter'))->get();
       }
       

        return response()->json([
            'messages' => $messages,
           
        ]);
    }

    public function get_sms_filter_by_teacher(Request $request)
    {
       
        $messages=PlayMobileModel::with(['user_sender', 'user_taker'])->where('taker_id','=',$request->input('teacher_id'))->get();
       

        return response()->json([
            'messages' => $messages,
           
        ]);
    }

}
