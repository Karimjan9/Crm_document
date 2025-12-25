<?php

namespace App\Http\Controllers\Admin\FCalendar;

use Illuminate\Http\Request;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class HolidayController extends Controller
{
    /**
     * Получить список праздников
     */
    public function index(Request $request)
    {
        try {
            $query = Holiday::query();

            // Фильтрация по типу
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Фильтрация по году
            if ($request->has('year')) {
                $query->whereYear('date', $request->year);
            }

            // Фильтрация по месяцу
            if ($request->has('month')) {
                $query->whereMonth('date', $request->month);
            }

            // Фильтрация по статусу
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Сортировка
            $sortBy = $request->get('sort_by', 'date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Пагинация
            $perPage = $request->get('per_page', 15);
            $holidays = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $holidays,
                'total' => $holidays->total(),
                'per_page' => $holidays->perPage(),
                'current_page' => $holidays->currentPage()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching holidays: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения списка праздников'
            ], 500);
        }
    }

    /**
     * Получить информацию о празднике
     */
    public function show($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $holiday
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Праздник не найден'
            ], 404);
        }
    }

    /**
     * Создать новый праздник
     */
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
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибки валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $holiday = Holiday::create([
                'title' => $request->title,
                'date' => $request->date,
                'type' => $request->type,
                'color' => $request->color,
                'description' => $request->description,
                'is_recurring' => $request->boolean('is_recurring', false),
                'is_active' => $request->boolean('is_active', true),
                'created_by' => auth()->id()
            ]);

            Log::info('Holiday created', ['id' => $holiday->id, 'title' => $holiday->title]);

            return response()->json([
                'success' => true,
                'message' => 'Праздник успешно создан',
                'data' => $holiday
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания праздника'
            ], 500);
        }
    }

    /**
     * Обновить праздник
     */
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
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибки валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $holiday->update($request->all());

            Log::info('Holiday updated', ['id' => $holiday->id]);

            return response()->json([
                'success' => true,
                'message' => 'Праздник успешно обновлен',
                'data' => $holiday
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка обновления праздника'
            ], 500);
        }
    }

    /**
     * Удалить праздник
     */
    public function destroy($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();

            Log::info('Holiday deleted', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Праздник успешно удален'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления праздника'
            ], 500);
        }
    }

    /**
     * Получить ближайшие праздники
     */
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
                'data' => $holidays
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching upcoming holidays: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения ближайших праздников'
            ], 500);
        }
    }

    /**
     * Получить статистику
     */
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
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching holiday stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения статистики'
            ], 500);
        }
    }

    /**
     * Массовый импорт праздников
     */
    public function bulkImport(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'holidays' => 'required|array',
                'holidays.*.title' => 'required|string|max:255',
                'holidays.*.date' => 'required|date',
                'holidays.*.type' => 'required|in:national,company,regional,religious,other'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибки валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $imported = 0;
            $failed = 0;

            foreach ($request->holidays as $holidayData) {
                try {
                    Holiday::create([
                        'title' => $holidayData['title'],
                        'date' => $holidayData['date'],
                        'type' => $holidayData['type'],
                        'color' => $holidayData['color'] ?? null,
                        'description' => $holidayData['description'] ?? null,
                        'is_recurring' => $holidayData['is_recurring'] ?? false,
                        'is_active' => $holidayData['is_active'] ?? true,
                        'created_by' => auth()->id()
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Error importing holiday: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Импорт завершен',
                'imported' => $imported,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка импорта праздников'
            ], 500);
        }
    }

    /**
     * Экспорт праздников в CSV
     */
    public function exportCsv()
    {
        try {
            $holidays = Holiday::all();

            $csv = "Название;Дата;Тип;Цвет;Описание;Повторяющийся;Активный\n";

            foreach ($holidays as $holiday) {
                $csv .= sprintf(
                    '%s;%s;%s;%s;%s;%s;%s',
                    $holiday->title,
                    $holiday->date->format('Y-m-d'),
                    $holiday->getTypeName(),
                    $holiday->color ?? '',
                    str_replace(';', ',', $holiday->description ?? ''),
                    $holiday->is_recurring ? 'Да' : 'Нет',
                    $holiday->is_active ? 'Да' : 'Нет'
                ) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="holidays_' . date('Y-m-d') . '.csv"'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting holidays to CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка экспорта'
            ], 500);
        }
    }

    /**
     * Получить данные для редактирования
     */
    public function edit($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $holiday
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching holiday for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения данных для редактирования'
            ], 404);
        }
    }

    /**
     * Копировать праздник
     */
    public function copy($id)
    {
        try {
            $original = Holiday::findOrFail($id);

            $copy = $original->replicate();
            $copy->title = $original->title . ' (копия)';
            $copy->save();

            Log::info('Holiday copied', [
                'original_id' => $original->id,
                'copy_id' => $copy->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Праздник успешно скопирован',
                'data' => $copy
            ]);

        } catch (\Exception $e) {
            Log::error('Error copying holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка копирования праздника'
            ], 500);
        }
    }
}
