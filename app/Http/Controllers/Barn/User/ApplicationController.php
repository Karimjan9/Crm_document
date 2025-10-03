<?php

namespace App\Http\Controllers\Barn\User;

use App\Http\Controllers\Controller;
use App\Models\UserApplicationModel;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
   
    public function index()
    {
        $user_id=Auth()->user()->id;
        $applications=UserApplicationModel::where('user_id',$user_id)->orderBy('created_at', 'DESC')->paginate(20);
        return view('barn.users.user_application.index',compact('applications'));
    }

    public function create()
    {
        // $user=['user'];
        // if(count($user))
        // dd(is_array($user));
        return view('barn.users.user_application.create');
    }

   
    public function store(Request $request)
    {
        // dd($request->name_item);
        $data=$request->all();
        $data['user_id']=Auth()->user()->id;
        $application=UserApplicationModel::create($data);
        return to_route('user_role.application.index');
    }

   
    public function show($id)
    {
        //
    }

    
    public function edit($id)
    {
        $application=UserApplicationModel::find($id);
        // dd($application);
        return view('barn.users.user_application.edit',compact('id','application'));
    }

    public function update(Request $request, $id)
    {
        $data=$request->all();
        $data['user_id']=Auth()->user()->id;
        $application=UserApplicationModel::find($id)->update($data);
        return to_route('user_role.application.index');
    }

    public function destroy($id)
    {
       $application=UserApplicationModel::find($id)->delete();
       return to_route('user_role.application.index');
    }

}
