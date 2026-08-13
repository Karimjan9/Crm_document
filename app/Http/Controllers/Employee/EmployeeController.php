<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

class EmployeeController extends Controller
{

    public function index()
    {
        return redirect()->route('employee.document.index');
    }
}
