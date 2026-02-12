<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index()
    {
        return redirect()->route('courier.documents.index');
    }
}

