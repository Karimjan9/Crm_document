<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentDirectionAdditionModel;
use PhpParser\Node\Scalar\MagicConst\Dir;

class DirectionAdditionController extends Controller
{
    public function index($id)
    {
        // dd($id);
        $directionTypes = DocumentDirectionAdditionModel::where('document_direction_id', '=', $id)->paginate();
        return view('admin.document_direction_addition.index', compact('directionTypes', 'id'));
    }

    public function create($id){
        // dd($id);
        return view('admin.document_direction_addition.create',compact('id'));
    }
      public function store($id, Request $request)
    {

        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
        ]);
        //  dd($request->document_type_id);
        DocumentDirectionAdditionModel::create([
            'document_type_id' => $request->document_type_id,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
        ]);

        return redirect()->route('superadmin.direction_addition.index',['direction_type'=>$request->document_direction_id])->with('success', 'Document Direction Additional created successfully.');
    }

    public function edit($id, $direction_addition_id){
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        // dd($type_addition_id);
        return view('admin.document_direction_addition.edit', compact('id', 'documentType'));
    }

    public function update($id, $direction_addition_id, Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
        ]);
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        $documentType->update([
            'document_type_id' => $request->document_type_id,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
        ]);
        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional updated successfully.');
    }

    public function destroy($id, $direction_addition_id){
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        $documentType->delete();
        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional deleted successfully.');
    }
}
