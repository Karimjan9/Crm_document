<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->input('phone', ''));

        if (str_starts_with($digits, '998')) {
            $digits = substr($digits, 3);
        }

        $this->merge([
            'phone' => substr($digits, 0, 9),
        ]);
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|digits:9|unique:users,phone,' . $userId,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ism-sharif kiritilishi kerak.',
            'phone.required' => 'Telefon raqam kiritilishi kerak.',
            'phone.digits' => 'Telefon raqam 9 xonali bolishi kerak.',
            'phone.unique' => 'Bu telefon raqam boshqa foydalanuvchiga biriktirilgan.',
            'avatar.image' => 'Avatar fayli rasm bolishi kerak.',
            'avatar.mimes' => 'Avatar JPG, PNG yoki WEBP formatda bolishi kerak.',
            'avatar.max' => 'Avatar hajmi 3 MB dan oshmasligi kerak.',
        ];
    }
}
