<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequestCreate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()?->hasAnyRole(['admin_manager', 'super_admin']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'full_name' => ['required'],
            'level_id' => ['required'],
            'login' => ['required', 'unique:users,login'],
            'departament_id' => ['nullable'],
            'role' => ['nullable'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)]

        ];
    }
}
