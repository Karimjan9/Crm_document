<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reduced_motion' => $this->boolean('reduced_motion'),
        ]);
    }

    public function rules(): array
    {
        return [
            'weather_city' => 'required|string|min:2|max:100',
            'reduced_motion' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'weather_city.required' => 'Ob-havo uchun shahar nomini kiriting.',
            'weather_city.min' => 'Shahar nomi kamida 2 belgidan iborat bolishi kerak.',
        ];
    }
}
