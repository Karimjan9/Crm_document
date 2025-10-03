<?php

namespace App\Http\Controllers\Barn\Rektor;

use App\Models\CargoModel;
use App\Models\PrixodModel;
use Illuminate\Http\Request;
use App\Models\ProviderModel;
use App\Http\Controllers\Controller;

class RektorPrixodController extends Controller
{
  
    public function index()
    {
        $cargos=CargoModel::with(['child'])->orderBy('id', 'DESC')->paginate(30);
            // dd($cargos);
            $all_inf=[];
            foreach ($cargos as $key => $cargo) {
                $prixod_num=0;
                $prixod_cost=0;
                $prixod_currency_cost_all=0;
                $inf=[];
                $prixod_curer=[];
                // $prixods=PrixodModel::with(['get_provider'])->where('cargo_id',$cargo->id)->get();
                foreach ($cargo->child as $key => $prixod) {
                      $prixod_num+=$prixod->count_of_item; 
                      $prixod_cost+=$prixod->cost_of_per*$prixod->count_of_item*$prixod->currency_value;
                      $prixod_currency_cost=$prixod->cost_of_per*$prixod->count_of_item;
                      $prixod_currency_cost_all=$prixod_currency_cost_all+$prixod_currency_cost;
                      $prixod_curer=$prixod->get_provider->name?? "kiritilmagan";
                 
                }
                $inf[]=(int)$prixod_cost;
                $inf[]=$prixod_num;
                $inf[]=$prixod_curer;
                $inf[]=$prixod_currency_cost_all??0;

                $all_inf[]=$inf;
            }
            // dd(1);
        return view('barn.rektor.prixods.index',compact('cargos','all_inf'));
    }

 
  
 
    public function show($id)
    {
        $prixods=PrixodModel::with(['get_currency','child'])->where('cargo_id',$id)->paginate(40);
        // dd($prixods[0]);
        return view('barn.rektor.prixods.show',compact('prixods'));
    }
//     <tr  data-toggle="collapse" id="table1" data-target=".table1">
//     <td>{{ $key22+1 }}</td>
//     <td>{{ $item->get_item_name->name }}</td>  
//     <td>{{ number_format($item->cost_of_per ,2,","," ")}} {{ $item->get_currency->name }}</td>
//     <td>{{ $item->get_cargo_name->name }}</td>
//     <td>{{ $item->created_at->format('d-m-Y') }}</td>

   
// </tr>

  
    
}
