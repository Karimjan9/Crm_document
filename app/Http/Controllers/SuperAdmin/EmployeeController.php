<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Criteria;
use App\Models\Departament;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('super.employee.index', [
        //     'criterias' => Criteria::all(),
        //     'departamets' => Departament::all(), 
        //     'teachers' => User::where("level_id", "=", 3)->get() 
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::withCount(
            'announcement', 'announcement_accept', 'announcement_reject',
            'statement', 'statement_accept', 'statement_reject',
        )->where("id", "=", $id)->first();

        // return view('super.employee.show', [
        //     'criterias' => Criteria::all(),
        //     'user' => $user
        // ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function index_vue()
    {
        // return view('super.employee.index_vue', [
        //     'criterias' => Criteria::all(),
        //     'departamets' => Departament::all(), 
        //     'teachers' => User::where("level_id", "=", 3)->get() 
        // ]);
    }
}
