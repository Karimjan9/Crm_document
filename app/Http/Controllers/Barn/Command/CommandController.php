<?php

namespace App\Http\Controllers\Barn\Command;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CommandModel;

class CommandController extends Controller
{
  
    public function index()
    {
        // if ($order_id==1) {
        //     $commands=CommandModel::where('order_id','=',$order_id)->paginate(10);
        //     return view('barn.kadr.command.index_for_dep',compact('commands'));
        // } else {
        //     $commands=CommandModel::with(['get_old_career','get_new_career','get_com_career_file'])->where('order_id','=',$order_id)->paginate(10);
        //     // dd($commands);
        //     return view('barn.kadr.command.index_for_career',compact('commands'));
        // }
        $commands=CommandModel::with(['get_old_job','get_new_job'])->paginate(10);
        // dd($commands);
        return view('barn.kadr.command.index_for_career',compact('commands'));
        // $commands=CommandModel::where('order_id','=',1)->paginate(10);
        // return view('barn.kadr.command.index',compact('commands'));
    }

    public function create()
    {
        //
    }

 
    public function store(Request $request)
    {
        //
    }

  
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

  
    public function update(Request $request, $id)
    {
        //
    }

   
    public function destroy($id)
    {
        //
    }
}
