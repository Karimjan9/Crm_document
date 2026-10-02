<?php

namespace App\Services;

use App\Models\FilialModel;

class BotBranchDirectory
{
    public const DEFAULT_START = '09:00';

    public const DEFAULT_END = '18:00';

    private const DAYS = [
        1 => 'Dushanba', 2 => 'Seshanba', 3 => 'Chorshanba', 4 => 'Payshanba',
        5 => 'Juma', 6 => 'Shanba', 7 => 'Yakshanba',
    ];

    public function content(): array
    {
        $today = now('Asia/Tashkent')->toDateString();
        $branches = FilialModel::query()->orderBy('name')->orderBy('id')->get([
            'id', 'name', 'address', 'phone', 'work_start_time', 'work_end_time',
            'working_days', 'holiday_dates',
        ])->map(function (FilialModel $filial) use ($today): array {
            $days = collect($filial->working_days ?: [1, 2, 3, 4, 5, 6])
                ->map(fn ($day) => (int) $day)->filter(fn ($day) => isset(self::DAYS[$day]))
                ->unique()->sort()->values()->all();
            $holidays = collect($filial->holiday_dates ?: [])->filter(function ($date) use ($today): bool {
                return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
                    && checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
                    && $date >= $today;
            })->unique()->sort()->values()->all();

            return [
                'id' => $filial->id,
                'name' => $filial->name,
                'address' => trim((string) $filial->address) ?: null,
                'phone' => trim((string) $filial->phone) ?: null,
                'work_start_time' => $this->time($filial->work_start_time, self::DEFAULT_START),
                'work_end_time' => $this->time($filial->work_end_time, self::DEFAULT_END),
                'working_days' => $days,
                'holiday_dates' => $holidays,
            ];
        })->all();
        $text = collect($branches)->map(function (array $branch): string {
            $lines = [
                '📍 '.$branch['name'],
                'Manzil: '.($branch['address'] ?: 'Operator orqali aniqlashtiring.'),
                '🕘 Ish vaqti: '.$branch['work_start_time'].'–'.$branch['work_end_time'],
            ];
            if ($branch['phone']) {
                $lines[] = '📞 Telefon: '.$branch['phone'];
            }
            if ($branch['working_days']) {
                $lines[] = '📅 Ish kunlari: '.implode(', ', array_map(fn ($day) => self::DAYS[$day], $branch['working_days']));
            }
            if ($branch['holiday_dates']) {
                $lines[] = 'Dam olish sanalari: '.implode(', ', array_map(
                    fn ($date) => substr($date, 8, 2).'.'.substr($date, 5, 2).'.'.substr($date, 0, 4),
                    $branch['holiday_dates'],
                ));
            }

            return implode("\n", $lines);
        })->implode("\n\n");

        return [
            'data' => $branches,
            'text' => $text ?: "Filial manzillari hozircha kiritilmagan.\nStandart ish vaqti: 09:00–18:00.\nManzilni operator orqali aniqlashtiring.",
        ];
    }

    private function time(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value) ? substr($value, 0, 5) : $fallback;
    }
}
