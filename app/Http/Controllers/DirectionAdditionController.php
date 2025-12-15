<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentDirectionAdditionModel;

class DirectionAdditionController extends Controller
{
     public function store(Request $request)
    {
        $request->validate([
            'document_direction_id' => 'required|exists:direction_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DocumentDirectionAdditionModel::create([
            'document_direction_id' => $request->document_direction_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.type_addition.index',['document_type'=>$request->document_direction_id])->with('success', 'Document Direction Additional created successfully.');
    }
}
