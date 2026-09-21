<?php

namespace App\Services;

use App\Models\IntakeSession;
use App\Models\ServiceAddonModel;
use App\Models\ServicesModel;
use Illuminate\Support\Str;

class SmartIntakeService
{
    public function analyze(array $answers, ?IntakeSession $session = null): IntakeSession
    {
        $service = $this->resolveService($answers);
        $addons = $this->resolveAddons($service, $answers['addon_ids'] ?? []);
        $recommendedAddons = $addons->isNotEmpty()
            ? $addons
            : ($service?->addons()->orderBy('price')->limit(3)->get(['id', 'service_id', 'name', 'price', 'deadline']) ?: collect());
        $price = (float) ($service?->price ?: 0) + (float) $addons->sum('price');
        $deadline = (int) ($service?->deadline ?: 0) + (int) $addons->sum('deadline');
        $files = $this->requiredFiles($answers, $service);

        $record = $session ?: new IntakeSession(['token' => Str::random(80)]);
        $record->fill([
            'answers' => $answers,
            'recommended_service_id' => $service?->id,
            'estimated_price' => round($price, 2),
            'estimated_deadline_days' => $deadline ?: null,
            'required_files' => $files,
            'recommended_addons' => $this->addonData($recommendedAddons),
            'current_step' => 3,
            'status' => 'analyzed',
            'completed_at' => now(),
        ])->save();

        return $record->load(['recommendedService', 'filial']);
    }

    public function start(?string $token = null): IntakeSession
    {
        if ($token !== null) {
            return IntakeSession::query()->where('token', $token)->firstOrFail();
        }

        return IntakeSession::create([
            'token' => Str::random(80),
            'status' => 'started',
            'current_step' => 1,
        ]);
    }

    private function resolveService(array $answers): ?ServicesModel
    {
        if (! empty($answers['service_id'])) {
            return ServicesModel::query()->find((int) $answers['service_id']);
        }

        $query = trim((string) ($answers['query'] ?? ''));
        $serviceQuery = ServicesModel::query()->orderBy('price');
        if ($query !== '') {
            $serviceQuery->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', '%'.addcslashes($query, '%_').'%')
                    ->orWhere('description', 'like', '%'.addcslashes($query, '%_').'%');
            });
        }

        return $serviceQuery->first() ?: ServicesModel::query()->orderBy('id')->first();
    }

    private function resolveAddons(?ServicesModel $service, array $addonIds)
    {
        if (! $service || $addonIds === []) {
            return collect();
        }

        return ServiceAddonModel::query()
            ->where('service_id', $service->id)
            ->whereIn('id', array_map('intval', $addonIds))
            ->get(['id', 'service_id', 'name', 'price', 'deadline']);
    }

    private function requiredFiles(array $answers, ?ServicesModel $service): array
    {
        $requested = $answers['required_files'] ?? [];
        $requested = is_array($requested) ? array_values(array_filter(array_map('trim', $requested))) : [];
        $files = $requested ?: ['Mijoz identifikatsiyasi', 'Asosiy hujjat fayli'];

        if (! empty($answers['needs_original'])) {
            $files[] = 'Original hujjat';
        }
        if (! empty($answers['needs_translation'])) {
            $files[] = 'Tarjima qilinadigan manba fayl';
        }

        return array_values(array_unique($files));
    }

    private function addonData($addons): array
    {
        return $addons->map(fn (ServiceAddonModel $addon): array => [
            'id' => $addon->id,
            'name' => $addon->name,
            'price' => (float) $addon->price,
            'deadline' => (int) $addon->deadline,
        ])->values()->all();
    }
}
