<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Foydalanuvchi ushbu so‘rovni yuborishga ruxsati borligini bildiradi.
     */
    public function authorize(): bool
    {
        // Admin panelda ishlayotgan bo‘lsa, ruxsat beramiz
        return true;
    }

    /**
     * Validatsiya qoidalari
     */
    public function rules(): array
    {
        $id = $this->route('id'); // update bo‘layotgan user id-sini olish

        return [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|digits:9|unique:users,phone,' . $id,
            'login' => 'required|string|min:3|max:255|unique:users,login,' . $id,
            'role' => 'required|string|exists:roles,name',
            'filial_id' => 'nullable|integer|exists:filials,id',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    /**
     * Foydalanuvchiga qulayroq xabarlar
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ism-sharif majburiy.',
            'phone.required' => 'Telefon raqam kiritilishi kerak.',
            'phone.unique' => 'Bu telefon raqam allaqachon ro‘yxatdan o‘tgan.',
            'login.required' => 'Login kiritilishi kerak.',
            'login.unique' => 'Bu login allaqachon mavjud.',
            'role.required' => 'Rol tanlanishi kerak.',
            'password.confirmed' => 'Parol tasdiqlanishi kerak.',
            'password.min' => 'Parol kamida 6 ta belgidan iborat bo‘lishi kerak.',
        ];
    }
}
