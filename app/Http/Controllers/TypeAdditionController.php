<?php

namespace App\Http\Controllers;

use App\Models\DocumentTypeAdditionModel;
use App\Models\DocumentTypeModel;
use Illuminate\Http\Request;
use App\Services\PricingService;

class TypeAdditionController extends Controller
{
    public function index($id)
    {
        DocumentTypeModel::query()->findOrFail($id);

        $documentTypes = DocumentTypeAdditionModel::where('document_type_id', '=', $id)->paginate();

        return view('admin.document_type_addition.index', compact('documentTypes', 'id'));
    }

    public function create($id)
    {
        DocumentTypeModel::query()->findOrFail($id);

        return view('admin.document_type_addition.create', compact('id'));
    }

    public function store($id, Request $request)
    {
        DocumentTypeModel::query()->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|numeric',
        ]);
        //  dd($request->document_type_id);
        $addition = DocumentTypeAdditionModel::create([
            'document_type_id' => $id,
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'addon',
            (int) $addition->id,
            'document_addon:' . $addition->id,
            (string) $addition->name,
            (float) $addition->amount,
            (int) ($addition->day ?? 0),
        );

        return redirect()->route('superadmin.type_addition.index', ['document_type' => $id])->with('success', 'Document Direction Additional created successfully.');

    }

    public function edit($id, $type_addition_id)
    {
        DocumentTypeModel::query()->findOrFail($id);
        $documentType = DocumentTypeAdditionModel::query()
            ->where('document_type_id', $id)
            ->findOrFail($type_addition_id);

        // dd($type_addition_id);
        return view('admin.document_type_addition.edit', compact('id', 'documentType'));
    }

    public function update($id, $type_addition_id, Request $request)
    {
        DocumentTypeModel::query()->findOrFail($id);
        $request->validate([
            'amount' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|numeric',
        ]);
        // dd(123);

        $documentType = DocumentTypeAdditionModel::query()
            ->where('document_type_id', $id)
            ->findOrFail($type_addition_id);
        // dd($documentType);
        $documentType->update([
            'document_type_id' => $id,
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'addon',
            (int) $documentType->id,
            'document_addon:' . $documentType->id,
            (string) $documentType->name,
            (float) $documentType->amount,
            (int) ($documentType->day ?? 0),
        );

        return redirect()->route('superadmin.type_addition.index', ['document_type' => $id])->with('success', 'Document Direction Additional updated successfully.');
    }

    public function destroy($id, $type_addition_id)
    {
        DocumentTypeModel::query()->findOrFail($id);
        $documentType = DocumentTypeAdditionModel::query()
            ->where('document_type_id', $id)
            ->findOrFail($type_addition_id);
        $documentType->delete();

        return redirect()->route('superadmin.type_addition.index', ['document_type' => $id])->with('success', 'Document Direction Additional deleted successfully.');
    }
}
