<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin_manager', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|digits:9|unique:users,phone,' . $id,
            'login' => 'required|string|min:3|max:255|unique:users,login,' . $id,
            'role' => ['required', 'string', Rule::in($this->allowedRoles())],
            'filial_id' => 'required_if:role,employee,admin_filial,courier|nullable|integer|exists:filial,id',
            'password' => ['nullable', 'string', 'confirmed', Password::min(12)],
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
            'filial_id.required_if' => 'Employee, courier yoki admin filial uchun filial tanlanishi shart.',
            'filial_id.exists' => 'Tanlangan filial mavjud emas.',
            'password.confirmed' => 'Parol tasdiqlanishi kerak.',
            'password.min' => 'Parol kamida 12 ta belgidan iborat bolishi kerak.',
        ];
    }

    protected function allowedRoles(): array
    {
        if ($this->user()?->hasRole('super_admin')) {
            return ['employee', 'admin_filial', 'courier', 'admin_manager', 'super_admin'];
        }

        return ['employee', 'admin_filial', 'courier'];
    }
}
