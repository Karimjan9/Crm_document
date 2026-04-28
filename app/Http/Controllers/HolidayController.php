<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
   public function index()
   {
        return response()->json(Holiday::pluck('date'));

   }

   public function store(Request $request)
   {
      $validated = $request->validate([
         'date' => 'required|date',
         'title' => 'nullable|string|max:255',
         'type' => 'nullable|in:national,company,regional,religious,other',
         'color' => 'nullable|string|max:7',
         'description' => 'nullable|string',
         'is_recurring' => 'nullable|boolean',
         'is_active' => 'nullable|boolean',
      ]);

      $holiday = Holiday::firstOrCreate(
         ['date' => $validated['date']],
         [
            'title' => $validated['title'] ?? 'Dam olish kuni',
            'type' => $validated['type'] ?? 'national',
            'color' => $validated['color'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_recurring' => $request->boolean('is_recurring', false),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
         ]
      );

      return response()->json(['success' => true, 'data' => $holiday]);
   }

   public function destroy ($date)
   {
      Holiday::where('date', $date)->delete();
        return response()->json(['success' => true]);
   }



}
