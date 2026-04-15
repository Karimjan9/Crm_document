<?php

namespace App\Http\Controllers\Admin\FCalendar;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Support\WorkdayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Holiday::query();

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->has('month')) {
                $query->whereMonth('date', $request->month);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $sortBy = $request->get('sort_by', 'date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $holidays = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $holidays,
                'total' => $holidays->total(),
                'per_page' => $holidays->perPage(),
                'current_page' => $holidays->currentPage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching holidays: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Holiday list could not be loaded',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $holiday,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching holiday: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Holiday not found',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'date' => 'required|date',
                'type' => 'required|in:national,company,regional,religious,other',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'is_recurring' => 'boolean',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($response = $this->validateOffDayDate($request->date)) {
                return $response;
            }

            $holiday = Holiday::create([
                'title' => $request->title,
                'date' => $request->date,
                'type' => $request->type,
                'color' => $request->color,
                'description' => $request->description,
                'is_recurring' => $request->boolean('is_recurring', false),
                'is_active' => $request->boolean('is_active', true),
                'created_by' => auth()->id(),
            ]);

            WorkdayCalendar::clearCache();

            Log::info('Holiday created', ['id' => $holiday->id, 'title' => $holiday->title]);

            return response()->json([
                'success' => true,
                'message' => 'Off day created successfully',
                'data' => $holiday,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error creating holiday: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day could not be created',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'type' => 'sometimes|required|in:national,company,regional,religious,other',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string',
                'is_recurring' => 'boolean',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $dateToValidate = $request->input('date', $holiday->date->toDateString());
            if ($response = $this->validateOffDayDate($dateToValidate)) {
                return $response;
            }

            $holiday->update($request->all());

            WorkdayCalendar::clearCache();

            Log::info('Holiday updated', ['id' => $holiday->id]);

            return response()->json([
                'success' => true,
                'message' => 'Off day updated successfully',
                'data' => $holiday,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day could not be updated',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();

            WorkdayCalendar::clearCache();

            Log::info('Holiday deleted', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Off day deleted successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day could not be deleted',
            ], 500);
        }
    }

    public function upcoming(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);

            $holidays = Holiday::active()
                ->whereDate('date', '>=', now())
                ->orderBy('date')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $holidays,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching upcoming holidays: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Upcoming off days could not be loaded',
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $stats = [
                'total' => Holiday::active()->count(),
                'current_month' => Holiday::active()
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
                'national' => Holiday::active()->where('type', 'national')->count(),
                'company' => Holiday::active()->where('type', 'company')->count(),
                'regional' => Holiday::active()->where('type', 'regional')->count(),
                'religious' => Holiday::active()->where('type', 'religious')->count(),
                'other' => Holiday::active()->where('type', 'other')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching holiday stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day stats could not be loaded',
            ], 500);
        }
    }

    public function bulkImport(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'holidays' => 'required|array',
                'holidays.*.title' => 'required|string|max:255',
                'holidays.*.date' => 'required|date',
                'holidays.*.type' => 'required|in:national,company,regional,religious,other',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $imported = 0;
            $failed = 0;

            foreach ($request->holidays as $holidayData) {
                try {
                    if (Carbon::parse($holidayData['date'])->isSunday()) {
                        $failed++;
                        continue;
                    }

                    Holiday::create([
                        'title' => $holidayData['title'],
                        'date' => $holidayData['date'],
                        'type' => $holidayData['type'],
                        'color' => $holidayData['color'] ?? null,
                        'description' => $holidayData['description'] ?? null,
                        'is_recurring' => $holidayData['is_recurring'] ?? false,
                        'is_active' => $holidayData['is_active'] ?? true,
                        'created_by' => auth()->id(),
                    ]);

                    $imported++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Error importing holiday: ' . $e->getMessage());
                }
            }

            DB::commit();
            WorkdayCalendar::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Bulk import completed',
                'imported' => $imported,
                'failed' => $failed,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in bulk import: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed',
            ], 500);
        }
    }

    public function exportCsv()
    {
        try {
            $holidays = Holiday::all();

            $csv = "Title;Date;Type;Color;Description;Recurring;Active\n";

            foreach ($holidays as $holiday) {
                $csv .= sprintf(
                    '%s;%s;%s;%s;%s;%s;%s',
                    $holiday->title,
                    $holiday->date->format('Y-m-d'),
                    $holiday->getTypeName(),
                    $holiday->color ?? '',
                    str_replace(';', ',', $holiday->description ?? ''),
                    $holiday->is_recurring ? 'Yes' : 'No',
                    $holiday->is_active ? 'Yes' : 'No'
                ) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="holidays_' . date('Y-m-d') . '.csv"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error exporting holidays to CSV: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'CSV export failed',
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $holiday,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching holiday for edit: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day data could not be loaded',
            ], 404);
        }
    }

    public function copy($id)
    {
        try {
            $original = Holiday::findOrFail($id);

            $copy = $original->replicate();
            $copy->title = $original->title . ' (copy)';
            $copy->save();

            WorkdayCalendar::clearCache();

            Log::info('Holiday copied', [
                'original_id' => $original->id,
                'copy_id' => $copy->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Off day copied successfully',
                'data' => $copy,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error copying holiday: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Off day could not be copied',
            ], 500);
        }
    }

    private function validateOffDayDate(string $date)
    {
        if (!Carbon::parse($date)->isSunday()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Yakshanba avtomatik dam olish kuni. Uni alohida kiritish shart emas.',
        ], 422);
    }
}
