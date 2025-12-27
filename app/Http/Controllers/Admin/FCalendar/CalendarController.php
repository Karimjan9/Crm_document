<?php

namespace App\Http\Controllers\Admin\FCalendar;

use Illuminate\Http\Request;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    /**
     * Получить все данные для календаря
     */
    public function getCalendarData(Request $request)
    {
        try {
            // Валидация параметров
            $validator = Validator::make($request->all(), [
                'start' => 'required|date',
                'end' => 'required|date'
            ]);

            if ($validator->fails()) {
                Log::error('Validation error in getCalendarData:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Неверные параметры даты',
                    'errors' => $validator->errors()
                ], 400);
            }

            $start = $request->input('start');
            $end = $request->input('end');

            // Убедимся, что даты корректны
            try {
                $startDate = Carbon::parse($start);
                $endDate = Carbon::parse($end);
            } catch (\Exception $e) {
                Log::error('Invalid date format:', ['start' => $start, 'end' => $end]);
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный формат даты'
                ], 400);
            }

            $events = [];

            // 1. Получаем праздники
            $holidays = $this->getHolidays($start, $end);
            $events = array_merge($events, $holidays);

            // 2. Добавляем выходные
            $weekends = $this->getWeekends($start, $end);
            $events = array_merge($events, $weekends);

            Log::info('Calendar data loaded successfully', [
                'start' => $start,
                'end' => $end,
                'total_events' => count($events)
            ]);

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error('Error loading calendar data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка загрузки данных календаря',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверить доступность даты
     */
    public function checkDateAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'available' => false,
                    'message' => 'Неверный формат даты',
                    'errors' => $validator->errors()
                ], 400);
            }

            $date = $request->input('date');
            $carbonDate = Carbon::parse($date);

            // Проверяем выходной
            if ($carbonDate->isWeekend()) {
                return response()->json([
                    'available' => false,
                    'message' => 'Выходной день',
                    'reason' => 'weekend',
                    'date' => $date
                ]);
            }

            // Проверяем праздник
            $holiday = Holiday::active()->whereDate('date', $date)->first();

            if ($holiday) {
                return response()->json([
                    'available' => false,
                    'message' => 'Праздничный день: ' . $holiday->title,
                    'reason' => 'holiday',
                    'holiday' => $holiday,
                    'date' => $date
                ]);
            }

            return response()->json([
                'available' => true,
                'message' => 'Дата доступна',
                'date' => $date
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking date availability: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Ошибка проверки даты',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить праздники на диапазон дат
     */
    private function getHolidays($start, $end)
    {
        $holidays = Holiday::active()
            ->whereBetween('date', [$start, $end])
            ->get();

        $events = [];

        foreach ($holidays as $holiday) {
            $events[] = [
                'id' => 'holiday_' . $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'end' => $holiday->date->format('Y-m-d'),
                'color' => $holiday->getColor(),
                'textColor' => $holiday->getTextColor(),
                'allDay' => true,
                'display' => 'background',
                'editable' => false,
                'extendedProps' => [
                    'type' => 'holiday',
                    'holiday_id' => $holiday->id,
                    'holiday_type' => $holiday->type,
                    'description' => $holiday->description,
                    'is_recurring' => $holiday->is_recurring
                ]
            ];

            // Также добавляем текстовую метку
            $events[] = [
                'id' => 'label_' . $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date->format('Y-m-d'),
                'display' => 'auto',
                'backgroundColor' => 'transparent',
                'textColor' => $holiday->getTextColor(),
                'borderColor' => 'transparent',
                'allDay' => true,
                'editable' => false,
                'extendedProps' => [
                    'type' => 'holiday_label',
                    'holiday_id' => $holiday->id,
                ]
            ];
        }

        return $events;
    }

    /**
     * Получить выходные дни
     */
    private function getWeekends($start, $end)
    {
        $events = [];
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        // Увеличиваем endDate на 1 день, чтобы включить последний день
        $endDate = $endDate->copy()->addDay();

        $weekendDays = [Carbon::SUNDAY];

        while ($startDate < $endDate) {
            if (in_array($startDate->dayOfWeek, $weekendDays)){
                $dayName = $this->getRussianDayName($startDate->dayOfWeek);

                $events[] = [
                    'id' => 'weekend_' . $startDate->format('Y-m-d'),
                    'title' => 'Выходной',
                    'start' => $startDate->format('Y-m-d'),
                    'display' => 'background',
                    'backgroundColor' => 'rgba(200, 200, 200, 0.3)',
                    'allDay' => true,
                    'editable' => false,
                    'extendedProps' => [
                        'type' => 'weekend',
                        'day_name' => $dayName
                    ]
                ];
            }

            $startDate->addDay();
        }

        return $events;
    }

    /**
     * Получить русское название дня недели
     */
    private function getRussianDayName($dayOfWeek)
    {
        $days = [
            0 => 'воскресенье',
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота'
        ];

        return $days[$dayOfWeek] ?? '';
    }
}
