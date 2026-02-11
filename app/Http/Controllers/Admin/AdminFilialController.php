<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminFilialController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasRole('admin_filial')) {
            abort(403);
        }
        $user=Auth::user()->filial_id;

         $users = User::role(['employee', 'courier'])
        ->with('roles', 'filial')
        ->orderBy('id', 'desc')
        ->where('filial_id',$user)
        ->get();
        // $filial=FilialModel::
        // dd($users[0]->roles[0]->name);
        return view('admin_filial.index',compact('users'));
    }
}
