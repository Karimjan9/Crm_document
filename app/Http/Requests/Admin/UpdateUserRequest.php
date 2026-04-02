<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|digits:9|unique:users,phone,' . $id,
            'login' => 'required|string|min:3|max:255|unique:users,login,' . $id,
            'role' => 'required|string|exists:roles,name',
            'filial_id' => 'required_if:role,employee|nullable|integer|exists:filial,id',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ism-sharif majburiy.',
            'phone.required' => 'Telefon raqam kiritilishi kerak.',
            'phone.unique' => 'Bu telefon raqam allaqachon royxatdan otgan.',
            'login.required' => 'Login kiritilishi kerak.',
            'login.unique' => 'Bu login allaqachon mavjud.',
            'role.required' => 'Rol tanlanishi kerak.',
            'filial_id.required_if' => 'Employee uchun filial tanlanishi shart.',
            'filial_id.exists' => 'Tanlangan filial mavjud emas.',
            'password.confirmed' => 'Parol tasdiqlanishi kerak.',
            'password.min' => 'Parol kamida 6 ta belgidan iborat bolishi kerak.',
        ];
    }
}
