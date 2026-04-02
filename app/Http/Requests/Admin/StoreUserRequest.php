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
            'filial_id' => 'required_if:role,employee,admin_filial|nullable|integer|exists:filial,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Foydalanuvchi ismi majburiy.',
            'name.string' => 'Ism faqat matndan iborat bolishi kerak.',
            'name.max' => 'Ism uzunligi 255 belgidan oshmasin.',

            'login.required' => 'Login kiritish majburiy.',
            'login.string' => 'Login matn korinishida bolishi kerak.',
            'login.unique' => 'Bu login allaqachon mavjud.',

            'phone.required' => 'Telefon raqami majburiy.',
            'phone.integer' => 'Telefon raqami faqat raqamlardan iborat bolishi kerak.',
            'phone.digits' => 'Telefon raqami 9 xonali bolishi kerak.',
            'phone.unique' => 'Bu telefon raqami tizimda allaqachon mavjud.',

            'password.required' => 'Parol kiritish majburiy.',
            'password.confirmed' => 'Parollar bir-biriga mos emas.',
            'password.min' => 'Parol kamida 6 belgidan iborat bolishi kerak.',

            'role.required' => 'Foydalanuvchi roli tanlanishi shart.',
            'role.string' => 'Rol nomi matn korinishida bolishi kerak.',

            'filial_id.required_if' => 'Employee yoki admin filial uchun filial tanlanishi shart.',
            'filial_id.integer' => 'Filial identifikatori notogri formatda.',
            'filial_id.exists' => 'Tanlangan filial mavjud emas.',
        ];
    }
}
