<?php

namespace App\Services;

use App\Models\Order;
use App\Models\DocumentsModel;
use App\Support\WorkdayCalendar;
use Carbon\Carbon;

class DeadlineRadarService
{
    public function assess(Order $order): array
    {
        $order->loadMissing(['documents.files', 'checklists', 'deliveries', 'statusHistories']);

        if (in_array($order->status, ['completed', 'cancelled', 'delivered'], true)) {
            return [
                'level' => 'green',
                'score' => 0,
                'summary' => 'Buyurtma yakunlangan.',
                'reasons' => [],
            ];
        }

        $dueAt = $order->promised_at?->copy();
        if (! $dueAt && $order->documents->isNotEmpty()) {
            $dueAt = $order->documents
                ->map(fn ($document) => $document->deadline_due_at)
                ->filter()
                ->sort()
                ->first();
        }

        if (! $dueAt) {
            return [
                'level' => 'green',
                'score' => 0,
                'summary' => 'Deadline belgilanmagan.',
                'reasons' => [],
            ];
        }

        $score = 0;
        $reasons = [];
        $now = now();
        $hoursLeft = $now->diffInHours($dueAt, false);

        if ($hoursLeft < 0) {
            $score += 70;
            $reasons[] = 'Deadline o‘tib ketgan';
        } elseif ($hoursLeft <= 24) {
            $score += 45;
            $reasons[] = 'Deadline 24 soat ichida';
        } elseif ($hoursLeft <= 72) {
            $score += 28;
            $reasons[] = 'Deadline 3 kun ichida';
        } elseif ($hoursLeft <= 168) {
            $score += 12;
        }

        $missingDocuments = $order->documents->filter(fn ($document) => $document->files->isEmpty())->count();
        $incomplete = $order->checklists->where('is_required', true)->where('is_completed', false)->count();
        if ($missingDocuments > 0 || $incomplete > 0) {
            $score += 25;
            $reasons[] = 'Yetishmayotgan fayl yoki checklist bor';
        }

        $delayedCourier = $order->deliveries->contains(fn ($delivery) =>
            $delivery->scheduled_at && $delivery->scheduled_at->isPast() && $delivery->status !== 'delivered'
        );
        if ($delayedCourier) {
            $score += 18;
            $reasons[] = 'Kuryer jadvali kechikmoqda';
        }

        $rework = $order->statusHistories->groupBy('to_status')->contains(fn ($events) => $events->count() > 1);
        if ($rework) {
            $score += 12;
            $reasons[] = 'Qayta ishlash signali mavjud';
        }

        $holidayCount = $this->holidaysBetween($now, $dueAt);
        if ($holidayCount > 0) {
            $score += min(10, $holidayCount * 4);
            $reasons[] = "Oraliqda {$holidayCount} ta dam olish kuni bor";
        }

        $activeBranchOrders = Order::query()
            ->where('filial_id', $order->filial_id)
            ->whereNotIn('status', ['completed', 'cancelled', 'delivered'])
            ->count();
        if ($activeBranchOrders >= 10) {
            $score += 15;
            $reasons[] = 'Filialdagi faol orderlar yuklamasi yuqori';
        } elseif ($activeBranchOrders >= 5) {
            $score += 7;
        }

        $employeeIds = $order->documents->pluck('user_id')->filter()->unique()->values();
        $employeeLoad = $employeeIds->isEmpty()
            ? 0
            : DocumentsModel::query()
                ->whereIn('user_id', $employeeIds)
                ->where('filial_id', $order->filial_id)
                ->whereNotIn('status_doc', ['finish', 'completed', 'delivered', 'cancelled', 'refunded'])
                ->count();
        if ($employeeLoad >= 10) {
            $score += 15;
            $reasons[] = 'Biriktirilgan xodimlar bandligi juda yuqori';
        } elseif ($employeeLoad >= 5) {
            $score += 7;
            $reasons[] = 'Biriktirilgan xodimlar bandligi oshgan';
        }

        $score = min(100, $score);
        $level = $score >= 70 ? 'red' : ($score >= 35 ? 'yellow' : 'green');
        $summary = match ($level) {
            'red' => 'Kechikish ehtimoli yuqori.',
            'yellow' => 'Xavf bor, nazorat kerak.',
            default => 'Buyurtma vaqtida tugashi kutilmoqda.',
        };

        return compact('level', 'score', 'summary', 'reasons');
    }

    private function holidaysBetween(Carbon $from, Carbon $to): int
    {
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        $count = 0;

        while ($cursor->lte($end) && $count < 31) {
            if (WorkdayCalendar::isNonWorkingDay($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }
}
