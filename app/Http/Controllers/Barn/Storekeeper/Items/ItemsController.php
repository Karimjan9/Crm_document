<?php

namespace App\Http\Controllers\Barn\Storekeeper\Items;

use App\Models\ItemsModel;
use App\Models\TypeOfItem;
use Illuminate\Http\Request;
use App\Models\ItemUnityModel;
use App\Models\BodilyTypeModel;
use App\Models\SecondTypeOfItem;
use App\Http\Controllers\Controller;
use App\Imports\ImportItems;
use Maatwebsite\Excel\Facades\Excel;

class ItemsController extends Controller
{
   
    public function index()
    {
        
        $items=ItemsModel::with(['get_bodily','get_second','get_unity'])->paginate(40);
        // dd($items);
        return view('barn.storekeep.items_actions.index',compact('items'));
    }


    public function create()
    {
        $firsts_types=TypeOfItem::all();
        $bodilys=BodilyTypeModel::all();

        // $second_types=SecondTypeOfItem::all();
        $unitys=ItemUnityModel::all();
        // dd($second_types);
        return view('barn.storekeep.items_actions.create',compact('bodilys','firsts_types','unitys'));
    }

   
    public function store(Request $request)
    {
        $item_create=ItemsModel::create($request->all());

        return redirect()->route('storekeeper_role.items.index');
    }

  
    public function show($id)
    {
        
    }


    public function edit($id)
    {
        $item=ItemsModel::where('id',$id)->first();
        $bodilys=BodilyTypeModel::all();

        $second_types=SecondTypeOfItem::all();

        $unitys=ItemUnityModel::all();

        $firsts_types=TypeOfItem::all();

        return view('barn.storekeep.items_actions.edit',compact('bodilys','item','second_types','unitys','firsts_types'));
    }

   
    public function update(Request $request,ItemsModel $item)
    {
        // dd($id);    
        $input = $request->all();
        $item->fill($input)->save();

        return redirect()->route('storekeeper_role.items.index');
    
    }

   
    public function destroy($id)
    {
        $item=ItemsModel::findOrFail($id)->delete();
        return to_route('storekeeper_role.items.index');

    }

    public function get_second_types(Request $request){
        $responses=SecondTypeOfItem::where('type_of_item_id',$request->first_type)->get();

        return response()->json([
            'responses' => $responses,
        ]);
    }

    public function command_input(){
        return view('barn.command.index');
    }

    public function command_create(Request $request){
        $path1 = $request->file('file_xlsx')->store('temp');

        $path = storage_path('app').'/'.$path1;
        // $excel=Excel::import(new ImportItems, 
        //               $request->file('file_xlsx'));
        $rows = Excel::toArray(new ImportItems, $request->file('file_xlsx')); 
        // ProcessPodcast::dispatch($path);
        // dd($rows[0]);
        foreach($rows[1] as $key => $value){
        //    dd($value);
            $key=$key+1;
            if (  $key==894 ) {
                // dd(($value));
                if($value[14]=='дона'){
                    // dd(2);
                    ItemsModel::create(["name"=>("$value[13]"),"bodily"=>1,"first"=>7,"second"=>23,"unity_id"=>2,"description"=>"$value[13]"]);
                }elseif ($value[14]=='комп') {
                    // dd(3);
                    ItemsModel::create(["name"=>("$value[13]"),"bodily"=>1,"first"=>7,"second"=>23,"unity_id"=>1,"description"=>"$value[13]"]);
                }
            }
        //     if ($key>=21 && $key<=41) {
        //         // dd($value);
         
        //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>2,"unity_id"=>2,"description"=>$value[10]]);

        //     }


        //     if ($key>=43 && $key<=77) {
        //         // dd($value);
        
        //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>2,"description"=>$value[10]]);

        //     }
        //     if ($key>=79 && $key<=106) {
     
        //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>2,"description"=>$value[10]]);

     
        //     }
        //         if ($key>=108 && $key<=119) {

        //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>5,"unity_id"=>2,"description"=>$value[10]]);
    
        
        //         // dd($value);
        //         }

        //         if ($key>=121 && $key<=125) {

        //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>2,"description"=>$value[10]]);

    
        //     // dd($value);
        //     }


        //         if ($key>=127 && $key<=135) {
          
        //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>7,"unity_id"=>2,"description"=>$value[10]]);
    

        //         }

        //         if ($key>=137 && $key<=146) {
        
        //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>8,"unity_id"=>2,"description"=>$value[10]]);
    
                
        //         }

        //             if ($key>=148 && $key<=152) {
            
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>9,"unity_id"=>2,"description"=>$value[10]]);
        
          
               
        //             }

        //             if ($key>=154 && $key<=163) {
           
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>10,"unity_id"=>2,"description"=>$value[10]]);
        
             
               
        //             }
        //             if ($key>=165 && $key<=186) {
                  
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>11,"unity_id"=>2,"description"=>$value[10]]);
        
                
               
        //             }
        //             if ($key>=188 && $key<=251) {
                    
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>12,"unity_id"=>2,"description"=>$value[10]]);
        
               
               
        //             }
        //             if ($key>=253 && $key<=263) {
              
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>13,"unity_id"=>2,"description"=>$value[10]]);
        
               
               
        //             }
        //             if ($key>=265 && $key<=278) {
                  
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>14,"unity_id"=>2,"description"=>$value[10]]);
        
             
               
        //             }

        //             if ($key>=280 && $key<=312) {
               
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>4,"second"=>16,"unity_id"=>2,"description"=>$value[10]]);
        
            
               
        //             }

        //             if ($key>=314 && $key<=320) {
                     
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>15,"unity_id"=>2,"description"=>$value[10]]);
        
              
               
        //             }

        //             if ($key>=322 && $key<=366) {
                  
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>17,"unity_id"=>2,"description"=>$value[10]]);
        
                
               
        //             }

           
        //             if ($key>=368 && $key<=397) {
         
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>2,"description"=>$value[10]]);
        
           
               
        //             }

                   

        //             if ($key>=399 && $key<=420) {
                  
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>19,"unity_id"=>2,"description"=>$value[10]]);
        
                 
               
        //             }
                    

        //             if ($key>=422 && $key<=431) {
                     
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>20,"unity_id"=>2,"description"=>$value[10]]);
        
               
        //             }

        //             if ($key>=433 && $key<=444) {
                  
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>21,"unity_id"=>2,"description"=>$value[10]]);
        
                
               
        //             }

        //             if ($key>=464 && $key<=474) {
                    
        //                     ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>7,"second"=>22,"unity_id"=>2,"description"=>$value[10]]);
        
               
        //             }
                  


           }
            
                
        
    



        return redirect()->route('storekeeper_role.command_input');
    }
}
