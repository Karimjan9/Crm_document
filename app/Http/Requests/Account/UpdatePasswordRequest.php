<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => 'required|string|min:6|confirmed|different:current_password',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Joriy parolni kiriting.',
            'current_password.current_password' => 'Joriy parol notogri.',
            'password.required' => 'Yangi parolni kiriting.',
            'password.min' => 'Yangi parol kamida 6 belgidan iborat bolishi kerak.',
            'password.confirmed' => 'Yangi parol tasdiqlanishi kerak.',
            'password.different' => 'Yangi parol joriy paroldan farq qilishi kerak.',
        ];
    }
}
