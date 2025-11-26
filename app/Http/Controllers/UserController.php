<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserRequestCreate;
use App\Models\Departament;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
       return redirect()->route('login');
    }

 
    public function create()
    {
       
    }

  
    public function store(UserRequestCreate $request)
    {
      
    }


    public function show($id)
    {
        //
    }

 
    public function edit($id)
    {
      
    }

 
    public function update(Request $request, $id)
    {

    }

 
    public function destroy($id)
    {
        //
    }
}
