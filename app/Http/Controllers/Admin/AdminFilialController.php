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
        $user = Auth::user()->filial_id;

        $users = User::role(['employee', 'courier'])
            ->select(['id', 'name', 'phone', 'filial_id'])
            ->where('filial_id', $user)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin_filial.index', compact('users'));
    }
}
