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
        //  dd($request->direction_type);

        $request->validate([
            'direction_type' => 'required|exists:direction_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'day'              => 'required|numeric',
        ]);
        DocumentDirectionAdditionModel::create([
            'document_direction_id' => $request->direction_type,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
            'day'              => $request->day,
        ]);

        return redirect()->route('superadmin.direction_addition.index',['direction_type'=>$request->direction_type])->with('success', 'Document Direction Additional created successfully.');
    }

    public function edit($id, $direction_addition_id){
        // dd($id);
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        // dd($type_addition_id);
        return view('admin.document_direction_addition.edit', compact('id', 'documentType'));
    }

    public function update($id, $direction_addition_id, Request $request)
    {
        // dd($request->day);
        $request->validate([
            'document_type_id' => 'required|exists:document_type,id',
            'amount'           => 'required|numeric',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'day'              => 'required|numeric',
        ]);
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        $documentType->update([
            'document_type_id' => $request->document_type_id,
            'name'             => $request->name,
            'amount'           => $request->amount,
            'description'      => $request->description,
            'day'              => $request->day,
        ]);
        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional updated successfully.');
    }

    public function destroy($id, $direction_addition_id){
        $documentType = DocumentDirectionAdditionModel::findOrFail($direction_addition_id);
        $documentType->delete();
        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional deleted successfully.');
    }
}
