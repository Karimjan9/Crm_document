<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Document;
use App\Models\Deadline;

class AdminFilialController extends Controller
{
    public function index()
    {
        return view('admin_filial.index');
    }

    public function employees()
    {
        $employees = Employee::all();
        return view('admin_filial.employees', compact('employees'));
    }

    public function stats()
    {
        $stats = Employee::stats(); // yoki kerakli query
        return view('admin_filial.stats', compact('stats'));
    }

    public function documents()
    {
        $documents = Document::all();
        return view('admin_filial.documents', compact('documents'));
    }

    public function deadlines()
    {
        $deadlines = Deadline::all();
        return view('admin_filial.deadlines', compact('deadlines'));
    }
}
