<?php

namespace App\Support;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WorkdayCalendar
{
    protected static ?Collection $activeOffDays = null;

    public static function resolveDueAt($createdAt, $deadlineDays, int $extraHours = 2): Carbon
    {
        $cursor = $createdAt instanceof CarbonInterface
            ? $createdAt->copy()
            : Carbon::now();

        $remainingDays = max((int) ($deadlineDays ?? 0), 0);

        while ($remainingDays > 0) {
            $cursor->addDay();

            if (static::isNonWorkingDay($cursor)) {
                continue;
            }

            $remainingDays--;
        }

        return $cursor->addHours($extraHours);
    }

    public static function isNonWorkingDay($date): bool
    {
        $carbon = static::asCarbon($date)->startOfDay();

        return $carbon->isSunday() || static::findNamedOffDay($carbon) !== null;
    }

    public static function findNamedOffDay($date): ?Holiday
    {
        $carbon = static::asCarbon($date)->startOfDay();
        $targetDate = $carbon->toDateString();
        $targetMonthDay = $carbon->format('m-d');

        return static::activeOffDays()->first(function (Holiday $holiday) use ($targetDate, $targetMonthDay) {
            if ($holiday->date?->toDateString() === $targetDate) {
                return true;
            }

            return (bool) $holiday->is_recurring
                && $holiday->date?->format('m-d') === $targetMonthDay;
        });
    }

    public static function formatRemaining(Carbon $dueAt, ?CarbonInterface $now = null): string
    {
        $now = $now instanceof CarbonInterface ? $now->copy() : Carbon::now();

        if ($now->greaterThan($dueAt)) {
            $diffHours = floor($dueAt->diffInSeconds($now) / 3600);

            if ($diffHours < 24) {
                return '-' . $diffHours . " soat o'tgan";
            }

            return '-' . floor($diffHours / 24) . " kun o'tgan";
        }

        $diffHours = floor($dueAt->diffInSeconds($now) / 3600);

        if ($diffHours >= 24) {
            return floor($diffHours / 24) . ' kun';
        }

        return $diffHours . ' soat';
    }

    public static function clearCache(): void
    {
        static::$activeOffDays = null;
    }

    protected static function activeOffDays(): Collection
    {
        if (static::$activeOffDays instanceof Collection) {
            return static::$activeOffDays;
        }

        try {
            static::$activeOffDays = Holiday::query()
                ->where('is_active', true)
                ->get();
        } catch (\Throwable $e) {
            static::$activeOffDays = collect();
        }

        return static::$activeOffDays;
    }

    protected static function asCarbon($date): Carbon
    {
        return $date instanceof CarbonInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);
    }
}
