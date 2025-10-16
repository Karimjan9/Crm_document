<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'login' => 'required|string|unique:users,login',
            'phone' => 'required|digits:9|integer|unique:users,phone',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|string',
            'filial_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Foydalanuvchi ismi majburiy.',
            'name.string' => 'Ism faqat matndan iborat bo‘lishi kerak.',
            'name.max' => 'Ism uzunligi 255 belgidan oshmasin.',

            'login.required' => 'Login kiritish majburiy.',
            'login.string' => 'Login matn ko‘rinishida bo‘lishi kerak.',
            'login.unique' => 'Bu login allaqachon mavjud.',

            'phone.required' => 'Telefon raqami majburiy.',
            'phone.integer' => 'Telefon raqami faqat raqamlardan iborat bo‘lishi kerak.',
            'phone.digits' => 'Telefon raqami 9 xonali bo‘lishi kerak.',
            'phone.unique' => 'Bu telefon raqami tizimda allaqachon mavjud.',

            'password.required' => 'Parol kiritish majburiy.',
            'password.confirmed' => 'Parollar bir-biriga mos emas.',
            'password.min' => 'Parol kamida 6 belgidan iborat bo‘lishi kerak.',

            'role.required' => 'Foydalanuvchi roli tanlanishi shart.',
            'role.string' => 'Rol nomi matn ko‘rinishida bo‘lishi kerak.',

            'filial_id.integer' => 'Filial identifikatori noto‘g‘ri formatda.',
        ];
    }
}
