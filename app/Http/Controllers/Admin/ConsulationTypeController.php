<?php

namespace App\Http\Controllers\Admin;

use App\Models\ConsulModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConsulationTypeModel;
use App\Models\PriceTariff;
use App\Services\PricingService;

class ConsulationTypeController extends Controller
{
   
    public function index()
    {
        $consulationTypes = ConsulationTypeModel::all();
        $main_consul = ConsulModel::first();
        return view('admin.consulation_types.index', compact('consulationTypes', 'main_consul'));
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
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        $consulationType = ConsulationTypeModel::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'consulate',
            (int) $consulationType->id,
            'consulate:' . $consulationType->id,
            (string) $consulationType->name,
            (float) $consulationType->amount,
            (int) $consulationType->day,
        );

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type created successfully.');
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
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        $consulationType = ConsulationTypeModel::findOrFail($id);
        $consulationType->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'consulate',
            (int) $consulationType->id,
            'consulate:' . $consulationType->id,
            (string) $consulationType->name,
            (float) $consulationType->amount,
            (int) $consulationType->day,
        );

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type updated successfully.');
    }

   
    public function destroy($id)
    {
        $consulationType = ConsulationTypeModel::findOrFail($id);
        if (PriceTariff::query()->where('line_type', 'consulate')->where('source_id', $consulationType->id)->exists()) {
            return back()->with('error', 'Bu consulate tarixi mavjud, o‘chirib bo‘lmaydi.');
        }
        $consulationType->delete();

        return redirect()->route('superadmin.consulation.index')->with('success', 'Consulation Type deleted successfully.');
    }

    public function getMainConsulationType()
    {
        $mainConsulationType = ConsulModel::first();
        return response()->json($mainConsulationType);
    }

    public function update_main(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'day' => 'required|integer',
        ]);

        $mainConsulationType = ConsulModel::first();
        if (!$mainConsulationType) {
            return response()->json(['message' => 'Main consulation type topilmadi.'], 404);
        }
        $mainConsulationType->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'day' => $request->day,
        ]);
        app(PricingService::class)->publishFixedPrice(
            'consulate',
            (int) $mainConsulationType->id,
            'consul:' . $mainConsulationType->id,
            (string) $mainConsulationType->name,
            (float) $mainConsulationType->amount,
            (int) $mainConsulationType->day,
        );
       return response()->json(['message' => 'Main Consulation Type updated successfully.']);
}

    public function static()
    {
        // dd(123);
        $statics  = ConsulModel::all();
        return view('admin.consulation_types.static', compact('statics'));
    }

   public function update_static($id,Request $request)
{
    // dd(123);
    $request->validate([
        'name'   => 'required|string|max:255',
        'amount' => 'required|numeric',
        'day'    => 'required|integer',
    ]);

    $consul = ConsulModel::findOrFail($id);

    $consul->update([
        'name'   => $request->name,
        'amount' => $request->amount,
        'day'    => $request->day,
    ]);
    app(PricingService::class)->publishFixedPrice(
        'consulate',
        (int) $consul->id,
        'consul:' . $consul->id,
        (string) $consul->name,
        (float) $consul->amount,
        (int) $consul->day,
    );

    return redirect()->back()->with(
        'success',
        'Consulation static muvaffaqiyatli yangilandi.'
    );
}
    public function destroy_static($id)
    {
        // dd(123);
        $consul = ConsulModel::findOrFail($id);
        if (PriceTariff::query()->where('line_type', 'consulate')->where('source_id', $consul->id)->exists()) {
            return back()->with('error', 'Bu consulate tarixi mavjud, o‘chirib bo‘lmaydi.');
        }
        $consul->delete();
        return redirect()->back()->with('success', 'Consulation static muvaffaqiyatli o\'chirildi.');
    }
}
