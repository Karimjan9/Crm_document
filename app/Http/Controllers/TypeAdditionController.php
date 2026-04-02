<?php
namespace App\Http\Controllers;

use App\Models\DocumentTypeAdditionModel;
use Illuminate\Http\Request;

class TypeAdditionController extends Controller
{
    public function index($id)
    {

        $documentTypes = DocumentTypeAdditionModel::where('document_type_id', '=', $id)->paginate();
        return view('admin.document_type_addition.index', compact('documentTypes', 'id'));
    }

    public function create($id)
    {
        return view('admin.document_type_addition.create', compact('id'));
    }
    public function store($id, Request $request)
    {

        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'day'              => 'required|numeric',
        ]);
        //  dd($request->document_type_id);
        DocumentTypeAdditionModel::create([
            'document_type_id' => $request->document_type_id,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
            'day'              => $request->day,
        ]);

        return redirect()->route('superadmin.type_addition.index', ['document_type' => $request->document_type_id])->with('success', 'Document Direction Additional created successfully.');

    }

    public function edit($id, $type_addition_id)
    {

        $documentType = DocumentTypeAdditionModel::findOrFail($type_addition_id);
        // dd($type_addition_id);
        return view('admin.document_type_addition.edit', compact('id', 'documentType'));
    }

    public function update($id, $type_addition_id, Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'day'              => 'required|numeric',
        ]);
        // dd(123);

        $documentType = DocumentTypeAdditionModel::findOrFail($type_addition_id);
        // dd($documentType);
        $documentType->update([
            'document_type_id' => $request->document_type_id,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
            'day'              => $request->day,
        ]);
        return redirect()->route('superadmin.type_addition.index', ['document_type' => $id])->with('success', 'Document Direction Additional updated successfully.');
    }
    public function destroy($id, $type_addition_id)
    {
        $documentType = DocumentTypeAdditionModel::findOrFail($id);
        $documentType->delete();
        return redirect()->route('superadmin.type_addition.index', ['document_type' => $id])->with('success', 'Document Direction Additional deleted successfully.');
    }
}
