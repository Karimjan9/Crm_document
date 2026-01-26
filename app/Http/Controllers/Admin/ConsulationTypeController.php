<?php

namespace App\Http\Controllers\Admin;

use App\Models\ConsulModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConsulationTypeModel;

class ConsulationTypeController extends Controller
{
   
    public function index()
    {
        $consulationTypes = ConsulationTypeModel::all();
        $main_consul = ConsulModel::first();
        return view('admin.consulation_types.index', compact('consulationTypes', 'main_consul'));
    }


 
    public function create()
    {
        return view('admin.consulation_types.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        ConsulationTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type created successfully.');
    }

  
    public function show($id)
    {
        //
    }

  
    public function edit($id)
    {
        $consulationType = ConsulationTypeModel::findOrFail($id);
        return view('admin.consulation_types.edit', compact('consulationType'));
    }

   
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        $consulationType = ConsulationTypeModel::findOrFail($id);
        $consulationType->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type updated successfully.');
    }

   
    public function destroy($id)
    {
        $consulationType = ConsulationTypeModel::findOrFail($id);
        $consulationType->delete();

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type deleted successfully.');
    }

    public function getMainConsulationType()
    {
        $mainConsulationType = ConsulModel::first();
        return response()->json($mainConsulationType);
    }

    public function update_main(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        $mainConsulationType = ConsulModel::first();
        $mainConsulationType->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);
       return response()->json(['message' => 'Main Consulation Type updated successfully.']);
}
}