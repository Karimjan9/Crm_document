<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Telefon raqamni tozalash, lekin client o‘zgarmaydi
        if ($this->has('new_client_phone') && $this->input('new_client_phone')) {
            $phone = preg_replace('/\D+/', '', $this->input('new_client_phone'));
            if (preg_match('/^998(\d{9})$/', $phone, $m)) {
                $phone = $m[1];
            } elseif (preg_match('/^\+?998(\d{9})$/', $phone, $m)) {
                $phone = $m[1];
            }
            $this->merge(['new_client_phone' => $phone]);
        }
    }

    public function rules()
    {
        return [
            'service_id'       => 'required|exists:services,id',
            'addons'           => 'nullable|array',
            'addons.*'         => 'nullable|exists:service_addons,id',
            'discount'         => 'nullable|numeric|min:0|max:100',
            'final_price'      => 'required|numeric|min:0',
            'paid_amount'      => 'nullable|numeric|min:0',
            'payment_type'     => 'nullable|string|in:cash,card,online,admin_entry',
            'description'      => 'nullable|string|max:2000',
            'document_type_id' => 'required|exists:document_type,id',
            'direction_type_id'=> 'required|exists:direction_type,id',
            'consulate_type_id'=> 'required|exists:consulates_type,id',


        ];
    }

    public function messages()
    {
        return [
            'service_id.required'  => 'Xizmat tanlanishi shart.',
            'service_id.exists'    => 'Tanlangan xizmat mavjud emas.',
            'addons.array'         => 'Addons array bo‘lishi kerak.',
            'addons.*.exists'      => 'Tanlangan qo‘shimcha xizmat noto‘g‘ri.',
            'discount.numeric'     => 'Diskont raqam bo‘lishi kerak.',
            'discount.min'         => 'Diskont kamida 0 bo‘lishi kerak.',
            'discount.max'         => 'Diskont 100 dan oshmasligi kerak.',
            'final_price.required' => 'Final price kiritilishi shart.',
            'final_price.numeric'  => 'Final price raqam bo‘lishi kerak.',
            'paid_amount.numeric'  => 'Paid amount raqam bo‘lishi kerak.',
            'payment_type.in'      => 'To‘lov turi noto‘g‘ri.',
            'document_type_id.required'        => 'Document type tanlanishi shart.',
            'document_type_id.exists'          => 'Tanlangan document type mavjud emas.',
            'direction_type_id.required'       => 'Direction type tanlanishi shart.',
            'direction_type_id.exists'         => 'Tanlangan direction type mavjud emas.',
            'consulate_type_id.required'       => 'Consulate type tanlanishi shart.',
            'consulate_type_id.exists'         => 'Tanlangan consulate type mavjud emas.',
        ];
    }
}
