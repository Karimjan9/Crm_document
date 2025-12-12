<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\DocumentTypeModel;
use App\Http\Controllers\Controller;
use App\Models\DocumentTypeAdditionModel;

class DocumentTypeController extends Controller
{
  
    public function index()
    {
        $documentTypes = DocumentTypeModel::with('additions')->paginate(10);
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

     public function store_type_additional(Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        DocumentTypeAdditionModel::create([
            'document_type_id' => $request->document_type_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Document Direction Additional created successfully.');
    }

   public function delete_type_additional($id){
      $documentType=DocumentTypeAdditionModel::firstOrFail($id);
    $documentType->delete();
    return redirect()->back()->with('success', 'Document Direction Additional deleted successfully.');
   }
}
