<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConsulationTypeModel;

class ConsulationTypeController extends Controller
{
   
    public function index()
    {
        $consulationTypes = ConsulationTypeModel::all();
        return view('admin.consulation_types.index', compact('consulationTypes'));
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
        ]);

        ConsulationTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
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
        ]);

        $consulationType = ConsulationTypeModel::findOrFail($id);
        $consulationType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type updated successfully.');
    }

   
    public function destroy($id)
    {
        $consulationType = ConsulationTypeModel::findOrFail($id);
        $consulationType->delete();

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type deleted successfully.');
    }
}
