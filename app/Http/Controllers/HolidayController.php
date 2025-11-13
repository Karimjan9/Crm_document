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

   public function store (Request $request)

   {
    $request->validate(['date' => 'required|date']);
    Holiday::firstOrCreate(['date' => $request->date]); 
    return response()->json(['success' => true ]);

   }

   public function destroy ($date)
   {
      Holiday::where('date', $date)->delete();
        return response()->json(['success' => true]);
   }



}
