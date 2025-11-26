<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        // dd('Calendar index');
        return view('calendar.index');
    }

        public function create()
    {
        return view('admin.calendar.create');
    }
}
