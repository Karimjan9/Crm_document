<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\DocumentTypeModel;
use App\Http\Controllers\Controller;

class DocumentTypeController extends Controller
{
  
    public function index()
    {
        $documentTypes = DocumentTypeModel::all();
        return view('admin.document_types.index', compact('documentTypes'));
    }

   
    public function create()
    {
        return view('admin.document_types.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DocumentTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.document_type.index')->with('success', 'Document Type created successfully.');
    }

    
    public function show($id)
    {
        //
    }

  
    public function edit($id)
    {
        $documentType = DocumentTypeModel::findOrFail($id);
        return view('admin.document_types.edit', compact('documentType'));
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $documentType = DocumentTypeModel::findOrFail($id);
        $documentType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.document_type.index')->with('success', 'Document Type updated successfully.');
    }

   
    public function destroy($id)
    {
        $documentType = DocumentTypeModel::findOrFail($id);
        $documentType->delete();

        return redirect()->route('superadmin.document_type.index')->with('success', 'Document Type deleted successfully.');
    }
}
