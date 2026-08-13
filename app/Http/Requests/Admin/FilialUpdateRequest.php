<?php

namespace App\Http\Requests\Admin;

use App\Models\FilialModel;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FilialUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin_manager', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        $routeFilial = $this->route('filial');
        $id = is_object($routeFilial)
            ? $routeFilial->getKey()
            : ($routeFilial ?: $this->route('id'));

        return [
            ...$this->profileRules(),
            'code' => ['required', 'string', 'max:255', 'unique:filial,code,' . $id],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['holiday_dates' => $this->normalizeHolidayDates($this->input('holiday_dates'))]);
    }

    protected function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'work_start_time' => ['nullable', 'date_format:H:i'],
            'work_end_time' => ['nullable', 'date_format:H:i', 'after:work_start_time'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['integer', 'between:1,7'],
            'holiday_dates' => ['nullable', 'array'],
            'holiday_dates.*' => ['date'],
            'monthly_expense' => ['nullable', 'numeric', 'min:0'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'commission_percent' => ['nullable', 'numeric', 'between:0,100'],
            'daily_capacity' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $managerId = $this->input('manager_id');

            if (! $managerId || $validator->errors()->has('manager_id')) {
                return;
            }

            $manager = User::query()->find((int) $managerId);

            if (! $manager || ! $manager->hasAnyRole(['admin_filial', 'admin_manager', 'super_admin'])) {
                $validator->errors()->add('manager_id', 'Filial menejeri uchun ruxsat etilgan rol tanlanishi kerak.');

                return;
            }

            $routeFilial = $this->route('filial');
            $filialId = $routeFilial instanceof FilialModel
                ? (int) $routeFilial->getKey()
                : (int) ($routeFilial ?: $this->route('id'));

            if ($manager->hasRole('admin_filial') && (int) $manager->filial_id !== $filialId) {
                $validator->errors()->add('manager_id', 'Filial administratori faqat o‘z filialiga menejer bo‘lishi mumkin.');
            }
        });
    }

    protected function normalizeHolidayDates(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[;,\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [],
        )));
    }
}
