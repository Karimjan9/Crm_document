<?php

namespace App\Http\Controllers;

use App\Models\CommandToManager;
use Illuminate\Http\Request;

class MissingEquipmentController extends Controller
{

    public function index()
    {
        $commands=CommandToManager::with(['get_ask.get_second_type',])->paginate(10);
       
        // dd($commands);
        return view('barn.admin_missing.index',compact('commands'));
    }


    public function create()
    {
        
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
