<?php

namespace App\Http\Controllers;

use App\Models\DirectionTypeModel;
use App\Models\DocumentDirectionAdditionModel;
use Illuminate\Http\Request;
use App\Services\PricingService;

class DirectionAdditionController extends Controller
{
    public function index($id)
    {
        DirectionTypeModel::query()->findOrFail($id);
        // dd($id);
        $directionTypes = DocumentDirectionAdditionModel::where('document_direction_id', '=', $id)->paginate();

        return view('admin.document_direction_addition.index', compact('directionTypes', 'id'));
    }

    public function create($id)
    {
        DirectionTypeModel::query()->findOrFail($id);

        // dd($id);
        return view('admin.document_direction_addition.create', compact('id'));
    }

    public function store($id, Request $request)
    {
        DirectionTypeModel::query()->findOrFail($id);
        //  dd($request->direction_type);

        $request->validate([
            'amount' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|numeric',
        ]);
        $addition = DocumentDirectionAdditionModel::create([
            'document_direction_id' => $id,
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'addon',
            (int) $addition->id,
            'direction_addon:' . $addition->id,
            (string) $addition->name,
            (float) $addition->amount,
            (int) ($addition->day ?? 0),
        );

        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional created successfully.');
    }

    public function edit($id, $direction_addition_id)
    {
        DirectionTypeModel::query()->findOrFail($id);
        // dd($id);
        $documentType = DocumentDirectionAdditionModel::query()
            ->where('document_direction_id', $id)
            ->findOrFail($direction_addition_id);

        // dd($type_addition_id);
        return view('admin.document_direction_addition.edit', compact('id', 'documentType'));
    }

    public function update($id, $direction_addition_id, Request $request)
    {
        DirectionTypeModel::query()->findOrFail($id);
        // dd($request->day);
        $request->validate([
            'amount' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|numeric',
        ]);
        $documentType = DocumentDirectionAdditionModel::query()
            ->where('document_direction_id', $id)
            ->findOrFail($direction_addition_id);
        $documentType->update([
            'document_direction_id' => $id,
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'addon',
            (int) $documentType->id,
            'direction_addon:' . $documentType->id,
            (string) $documentType->name,
            (float) $documentType->amount,
            (int) ($documentType->day ?? 0),
        );

        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional updated successfully.');
    }

    public function destroy($id, $direction_addition_id)
    {
        DirectionTypeModel::query()->findOrFail($id);
        $documentType = DocumentDirectionAdditionModel::query()
            ->where('document_direction_id', $id)
            ->findOrFail($direction_addition_id);
        $documentType->delete();

        return redirect()->route('superadmin.direction_addition.index', ['direction_type' => $id])->with('success', 'Document Direction Additional deleted successfully.');
    }
}
