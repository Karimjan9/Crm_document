<?php

namespace App\Http\Controllers\Barn\User;

use App\Models\ItemsModel;
use App\Models\TypeOfItem;
use Illuminate\Http\Request;
use App\Models\UsersAskModel;
use App\Models\OrderToBarnModel;
use App\Models\SecondTypeOfItem;
use App\Http\Controllers\Controller;
use App\Models\DepartmentKafedraModel;
use App\Models\GiveItemModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function index()
    {

    $deps=DepartmentKafedraModel::where('res_person','=',Auth()->id())->paginate(15);
    // dd($deps);
    return view('barn.users.invertar.index',compact('deps'));

    }


  

  
  
    public function store_invertar($dep_id,$item_id)
    {
   
    //    dd(Auth()->id()); 
        $item=GiveItemModel::where('dep_id','=',$dep_id)
        ->where('status','=',1)
        ->where('item_id','=',$item_id)
        ->update(['status'=>2,'user_id'=>Auth()->id()]);
       
        return to_route('user_role.users_invertar.show',['users_invertar'=>$dep_id]);

    }
 
    public function show($id)
    {
    //     $departaments=GiveItemModel::where('item_id','=',$item_id)
    // ->selectRaw('(item_id) as item_id,count(item_id) as give_item,(give_item.status) as status')
    // ->groupBy('give_item.item_id','give_item.status')
    // ->paginate(40);
        $items=GiveItemModel::with('get_item')
        ->where('dep_id','=',$id)
        ->where('status','=',1)
        ->selectRaw('(item_id) as item_id,count(item_id) as give_item,(give_item.status) as status')
        ->groupBy('give_item.item_id','give_item.status')->paginate(15);
        // dd($items);
        return view('barn.users.invertar.show',compact('items','id'));
    }

   
   public function show_invertar_deps()
   {
    $deps=DepartmentKafedraModel::where('res_person','=',Auth()->id())->paginate(15);
        // dd(Auth()->id());
    return view('barn.users.invertar.show_invertar',compact('deps'));
   }

   
  public function show_invertar_room($room_id)
  {
    $items=GiveItemModel::with('get_item')
    ->where('status','=',2)
    ->where('repair_status','!=',1)
    ->where('dep_id','=',$room_id)
    ->selectRaw('id as id,(item_id) as item_id,count(item_id) as give_item,(give_item.status) as status')
    ->groupBy('give_item.item_id','give_item.status','id')
    ->paginate(40);
    // dd($items);
    return view('barn.users.invertar.accept_item_show',compact('items','room_id'));
  }
   

  public function get_to_repair($item_id,$room_id)
  {
    // dd($room_id);
    $item=GiveItemModel::where('id',$item_id)->first();
    // dd($item);
    $count=$item->repair_count+1;
    GiveItemModel::where('id','=',$item_id)
    ->update(['repair_count'=>$count,'repair_status'=>1]);
    return to_route('user_role.show_invertar_room',['room_id'=>$room_id]);
  }
   

  public function show_repair_items()
  {
    // dd();
    $repairs=GiveItemModel::with(['get_item','get_departament_belong'])->where('user_id',Auth()->id())->where('repair_status',1)->paginate(15);
    // dd($repairs);
    return view('barn.users.invertar.show_repair',compact('repairs'));
    
  }
 


    // public function index()
    // {
      
    //     $asks=UsersAskModel::where('user_id',Auth::id())->orderBy('created_at', 'desc')->paginate(10);

    //     //   dd($asks);

    //     return view('barn.users.ask.index',compact('asks'));

    // }


    // public function create()
    // {
    //     $firsts=TypeOfItem::all();
    //     $seconds=SecondTypeOfItem::all();

    //     // dd($firsts);
    
    //     return view('barn.users.ask.create',compact('seconds','firsts'));
    // }


    // public function list_of_petition(){
    //     $petitions = Session::get('petitions');
      
    //     if(Session::has('petitions')){
    //         $lists=[];
    //         $petitions = Session::get('petitions');
    //         foreach ($petitions as $key => $petition) {
    //             $obj=SecondTypeOfItem::whereIn('id',[$petition['second']])->get();
    //             array_push($lists,$obj);
              
    //         }

    //     }
    //     else {
    //         $petitions=[];
    //         $lists=[];
    //     }
      
    //     return view('barn.users.ask.index_list',compact('petitions','lists'));
     
    // }
    // public function store(Request $request)
    // {
       
    //     $petitions=[];
    //     $data=['type'=>$request->type,'second'=>$request->second,'number'=>$request->number,'description'=>$request->description];
     
    //     if(Session::has('petitions')){
         
    //         $old = Session::get('petitions');
           
    //         array_push($old,$data);
    //         session(['petitions' => $old]);
    //     }
    //     else {
    //         array_push($petitions,$data);
    //         session(['petitions' => $petitions]);
    //     }
    //     return redirect()->route('user_role.list_petition');
    // }

 
    // public function show($id)
    // {
    //     //
    // }


    // public function edit($id)
    // {
    //     $old = Session::get('petitions');
    //     $second=SecondTypeOfItem::where('id',$old[$id]['second'])->first();
    //     $edit_petition=$old[$id];
    //     $firsts=TypeOfItem::all();
        
    //     return view('barn.users.ask.edit',compact('edit_petition','firsts','id','second'));
    // }

    // public function update(Request $request, $id)
    // {
    //     $old = Session::get('petitions');
     
    //     $data=['type'=>$request->type,'second'=>$request->second,'number'=>$request->number,'description'=>$request->description];
    //     // dd($old[$id]);
    //     $old[$id]=$data;
        
    //     session(['petitions' => $old]);
    //     return to_route('user_role.list_petition');
    // }

  
    // public function destroy($id)
    // {
    //     $old = Session::get('petitions');
       
    //     unset($old[$id]);
    //     foreach ($old as $ii => $va){
    //         $ret[]=$old[$ii];
    //     }
    //     // dd($ret);
    //     session(['petitions' => $ret]);
    //     return to_route('user_role.list_petition');
    // }

    // public function get_second_types(Request $request){
    //     $responses=SecondTypeOfItem::where('type_of_item_id',$request->type)->get();

    //     return response()->json([
    //         'responses' => $responses,
    //     ]);
    // }

    // public function store_all(Request $request){
      
    //     // dd($items[1][0]->id);
    //     foreach (json_decode($request->petitions) as $key => $petition) {

    //         $create=UsersAskModel::create(['type_id'=>$petition->type,'second_id'=>$petition->second,'number'=>$petition->number,'description'=>$petition->description,'user_id'=>Auth::user()->id,'status'=>0]);
          
    //         // dd($get);
          
    //     }
    //     // dd(1);
    //     if(Session::has('petitions')){
    //         Session::forget('petitions');
    //         return to_route('user_role.users_petitions.index');
    //     }else{
    //         return to_route('user_role.users_petitions.index');
    //     }
      
    // }

    // public function show_devices(){
    //     $id=Auth::user()->id;
    //     $asks=GiveItemModel::where('user_id',$id)->where('status','=',1)->with(['get_item.get_second','get_user'])->paginate(10);
    //     // $order=OrderToBarnModel::where('status',0)->where('user_id',$id)->where('item_id',$asks[0]->item_id)->get();
    //     // dd($order);
    //     // $asks_2=GiveItemModel::where('user_id',$id)->where('status','=',2)->with(['get_item.get_second','get_user'])->paginate(10);
    //     // dd($asks);
    //     return view('barn.users.device.index',compact('asks'));
    // }
    
    // public function show_items_user(){
    //     $id=Auth::user()->id;
       
    //     $asks_2=GiveItemModel::where('user_id',$id)->where('status','=',2)->with(['get_item.get_second','get_user'])->paginate(10);
    //     // dd($asks_2);
    //     return view('barn.users.device.show',compact('asks_2'));
    // }
    // public function device_accept($id){
    //     // dd($id);
    //     $item=GiveItemModel::find($id);
    //     $item2=GiveItemModel::find($id)->update(['status'=>2]);
    //     $order=OrderToBarnModel::where('status',0)->where('user_id',$item->user_id)->where('item_id',$item->item_id)->first();
    //     $current=ItemsModel::where('id',$item->item_id)->first();
    //     ItemsModel::where('id',$item->item_id)->update(['absent'=>$current->absent+1]);
    //     if($order->number_of_item==$order->givet_item_num+1){
    //         OrderToBarnModel::find($order->id)->update(['status'=>2,'givet_item_num'=>$order->givet_item_num+1]);
    //     }
        
    //     return to_route('user_role.show.devices');
    // }

    // public function device_deny($id){

    //     $item=GiveItemModel::find($id);

    //     $order=OrderToBarnModel::find($item->order_id);

    //     OrderToBarnModel::find($item->order_id)->update(['give_status'=>0,'givet_item_num'=>$order->givet_item_num-1]);
        
    //     GiveItemModel::find($id)->update(['status'=>3]);
        
    //     return to_route('user_role.show.devices');
    // }
    
}
