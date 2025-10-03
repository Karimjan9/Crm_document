<?php

namespace App\Http\Controllers\Barn\OtherCheker;

use App\Http\Controllers\Controller;
use App\Models\UserApplicationModel;
use Illuminate\Http\Request;

class OtherChekerController extends Controller
{
   
    public function index()
    {
        $applications=UserApplicationModel::where('confirmed','=',1)->orderBy('id','desc')->paginate(20);
        // dd($application);
        return view('barn.users.other_cheker.index',compact('applications'));
        
    }

   
    public function create()
    {
        
        
    }

   
    public function store(Request $request)
    {
        

    }

    public function show($id)
    {
        

    }

    public function edit($id)
    {
       
        
    }

   
    public function update(Request $request, $id)
    {
        

    }

    public function destroy($id)
    {
        
        
    }
}
