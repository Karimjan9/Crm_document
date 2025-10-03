<?php

namespace App\Http\Controllers\Barn\Kadr;

use App\Http\Controllers\Controller;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Psy\Readline\Userland;

class RankNameController extends Controller
{
   
    public function index()
    {
        $user_levels=UserLevel::paginate(10);
        // dd($user_levels);
        return view('barn.kadr.rank.index',compact('user_levels'));
    }

    
    public function create()
    {
       return view('barn.kadr.rank.create');
    }

   
    public function store(Request $request)
    {
        $data=$request->all();
        $level=UserLevel::create($data);
        return to_route('storekeeper_role.rank.index');
        
    }

   
    public function show($id)
    {
        //
    }

       public function edit($id)
    {
       $user_level=UserLevel::where('id',$id)->first();
       return view('barn.kadr.rank.edit',compact('user_level','id'));
    }

   
    public function update(Request $request, $id)
    {
        $data=$request->all();
        $level=UserLevel::find($id)->update($data);
        return to_route('storekeeper_role.rank.index');

    }

    
    public function destroy($id)
    {
        // dd($id);
        $level=UserLevel::find($id)->delete();
        return to_route('storekeeper_role.rank.index');
    }
}
