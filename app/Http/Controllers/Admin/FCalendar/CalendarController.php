<?php

namespace App\Http\Controllers\Admin\FCalendar;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Support\WorkdayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function getCalendarData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start' => 'required|date',
                'end' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::error('Validation error in getCalendarData', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'message' => 'Date range is invalid',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $start = $request->input('start');
            $end = $request->input('end');

            try {
                Carbon::parse($start);
                Carbon::parse($end);
            } catch (\Throwable $e) {
                Log::error('Invalid date format', ['start' => $start, 'end' => $end]);

                return response()->json([
                    'success' => false,
                    'message' => 'Date format is invalid',
                ], 400);
            }

            $events = array_merge(
                $this->getHolidays($start, $end),
                $this->getWeekends($start, $end)
            );

            Log::info('Calendar data loaded successfully', [
                'start' => $start,
                'end' => $end,
                'total_events' => count($events),
            ]);

            return response()->json($events);
        } catch (\Throwable $e) {
            Log::error('Error loading calendar data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Calendar data could not be loaded',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkDateAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'available' => false,
                    'message' => 'Date format is invalid',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $date = $request->input('date');
            $carbonDate = Carbon::parse($date);

            if ($carbonDate->isSunday()) {
                return response()->json([
                    'available' => false,
                    'message' => 'Yakshanba avtomatik dam olish kuni',
                    'reason' => 'weekend',
                    'date' => $date,
                ]);
            }

            $holiday = WorkdayCalendar::findNamedOffDay($carbonDate);

            if ($holiday) {
                return response()->json([
                    'available' => false,
                    'message' => 'Bu kun allaqachon belgilangan: ' . $holiday->title,
                    'reason' => 'holiday',
                    'holiday' => $holiday,
                    'date' => $date,
                ]);
            }

            return response()->json([
                'available' => true,
                'message' => 'Date is available',
                'date' => $date,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error checking date availability: ' . $e->getMessage());

            return response()->json([
                'available' => false,
                'message' => 'Date check failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getHolidays(string $start, string $end): array
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();

        $holidays = Holiday::active()->get();
        $events = [];

        foreach ($holidays as $holiday) {
            $holidayDate = $holiday->date->copy()->startOfDay();

            if (!$holiday->is_recurring && !$holidayDate->between($startDate, $endDate)) {
                continue;
            }

            if ($holiday->is_recurring) {
                $occurrence = Carbon::create(
                    $startDate->year,
                    $holidayDate->month,
                    $holidayDate->day
                )->startOfDay();

                while ($occurrence->lessThan($startDate)) {
                    $occurrence->addYear();
                }

                if ($occurrence->greaterThan($endDate)) {
                    continue;
                }

                $eventDate = $occurrence;
            } else {
                $eventDate = $holidayDate;
            }

            $events[] = [
                'id' => 'holiday_' . $holiday->id . '_' . $eventDate->format('Ymd'),
                'title' => $holiday->title,
                'start' => $eventDate->format('Y-m-d'),
                'end' => $eventDate->format('Y-m-d'),
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
                    'is_recurring' => $holiday->is_recurring,
                ],
            ];

            $events[] = [
                'id' => 'label_' . $holiday->id . '_' . $eventDate->format('Ymd'),
                'title' => $holiday->title,
                'start' => $eventDate->format('Y-m-d'),
                'display' => 'auto',
                'backgroundColor' => 'transparent',
                'textColor' => $holiday->getTextColor(),
                'borderColor' => 'transparent',
                'allDay' => true,
                'editable' => false,
                'extendedProps' => [
                    'type' => 'holiday_label',
                    'holiday_id' => $holiday->id,
                ],
            ];
        }

        return $events;
    }

    private function getWeekends(string $start, string $end): array
    {
        $events = [];
        $cursor = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay()->addDay();

        while ($cursor->lessThan($endDate)) {
            if ($cursor->isSunday()) {
                $events[] = [
                    'id' => 'weekend_' . $cursor->format('Y-m-d'),
                    'title' => 'Yakshanba',
                    'start' => $cursor->format('Y-m-d'),
                    'display' => 'background',
                    'backgroundColor' => 'rgba(200, 200, 200, 0.3)',
                    'allDay' => true,
                    'editable' => false,
                    'extendedProps' => [
                        'type' => 'weekend',
                        'day_name' => 'yakshanba',
                    ],
                ];
            }

            $cursor->addDay();
        }

        return $events;
    }
}
