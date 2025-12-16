<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\DirectionTypeModel;
use App\Http\Controllers\Controller;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentDirectionAdditionModel;

class DirectionTypeController extends Controller
{
   
    public function index()
    {
        $documentTypes = DirectionTypeModel::all();
        return view('admin.direction_types.index', compact('documentTypes'));
    }

  
    public function create()
    {
        return view('admin.direction_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DirectionTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.direction_type.index')->with('success', 'Direction Type created successfully.');
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        $directionType = DirectionTypeModel::findOrFail($id);
        return view('admin.direction_types.edit', compact('directionType'));
    }

   
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $directionType = DirectionTypeModel::findOrFail($id);
        $directionType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.direction_type.index')->with('success', 'Direction Type updated successfully.');
    }

   
    public function destroy($id)
    {
        $directionType = DirectionTypeModel::findOrFail($id);
        $directionType->delete();

        return redirect()->route('superadmin.direction_type.index')->with('success', 'Direction Type deleted successfully.');
    }

   

   
}
