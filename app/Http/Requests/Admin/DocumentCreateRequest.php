<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DocumentCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare data for validation:
     * - convert empty client_id to null
     * - strip non-digits from phone
     * - convert +998901234567 or 998901234567 => 901234567
     */
    protected function prepareForValidation()
    {
        // Agar client_id bo‘sh string bo‘lsa null qilamiz
        if ($this->has('client_id') && $this->input('client_id') === '') {
            $this->merge(['client_id' => null]);
        }

        // Telefon raqamni tozalash
        if ($this->has('new_client_phone') && $this->input('new_client_phone')) {
            $phone = preg_replace('/\D+/', '', $this->input('new_client_phone'));
            if (preg_match('/^998(\d{9})$/', $phone, $m)) {
                $phone = $m[1];
            } elseif (preg_match('/^\+?998(\d{9})$/', $phone, $m)) {
                $phone = $m[1];
            }
            $this->merge(['new_client_phone' => $phone]);
        }
        //   dd($this->all());
    }
  
    public function rules()
{
    $rules = [
        'client_id'    => 'nullable|exists:clients,id',
        'new_client_name'  => 'nullable|string|max:255',
        'new_client_phone' => ['nullable','regex:/^\d{9}$/'],
        'new_client_desc'  => 'nullable|string|max:500',
        'service_id'       => 'required|exists:services,id',
        'addons'           => 'nullable|array',
        'addons.*'         => 'nullable|exists:service_addons,id',
        'selected_addons'  => 'nullable|json',
        'discount'         => 'nullable|numeric|min:0',
        'final_price'      => 'nullable|numeric|min:0',
        'paid_amount'      => 'nullable|numeric|min:0',
        'payment_type'     => 'nullable|string|in:cash,card,online,admin_entry,transfer',
        'description'      => 'nullable|string|max:2000',
        'document_type_id' => 'required|exists:document_type,id',
        'direction_type_id'=> 'required_if:process_mode,apostil|nullable|exists:direction_type,id',
        'consulate_type_id'=> 'required_if:process_mode,consul|nullable|exists:consulates_type,id',
        'process_mode'     => 'nullable|in:apostil,consul',
        'apostil_group1_id'=> 'required_if:process_mode,apostil|nullable|exists:apostil_static,id',
        'apostil_group2_id'=> 'required_if:process_mode,apostil|nullable|exists:apostil_static,id',
        'consul_id'        => 'required_if:process_mode,consul|nullable|exists:consul,id',
    ];

    // Agar client_id bo‘sh bo‘lsa, yangi mijoz uchun name va phone majburiy
    if (!$this->input('client_id')) {
        $rules['new_client_name']  = 'required|string|max:255';
        $rules['new_client_phone'] = ['required','regex:/^\d{9}$/'];
    }

    return $rules;
}

    public function messages()
    {
        return [
            'new_client_name.required_if'      => 'New client name qator bo‘lishi kerak.',
            'new_client_phone.required_if'     => 'New client phone kerak (yoki mavjud mijozni tanlang).',
            'new_client_phone.regex'           => 'Phone number must be exactly 9 digits (e.g., 901234567).',
            'service_id.required'              => 'Xizmat tanlanishi shart.',
            'service_id.exists'                => 'Tanlangan xizmat mavjud emas.',
            'discount.numeric'                 => 'Diskont raqam bo‘lishi kerak.',
            'discount.min'                     => 'Diskont kamida 0 boʻlishi kerak.',
            'discount.max'                     => 'Diskont 100 dan oshmasligi kerak.',
            'addons.array'                     => 'Addons array bo‘lishi kerak.',
            'addons.*.exists'                  => 'Tanlangan qo‘shimcha xizmat noto‘g‘ri.',
            'final_price.required'             => 'Final price kiritilishi shart.',
            'final_price.numeric'              => 'Final price raqam bo‘lishi kerak.',
            'paid_amount.numeric'              => 'Paid amount raqam bo‘lishi kerak.',
            'payment_type.in'                  => 'To‘lov turi noto‘g‘ri.',
            'document_type_id.required'        => 'Document type tanlanishi shart.',
            'document_type_id.exists'          => 'Tanlangan document type mavjud emas.',
            'direction_type_id.required'       => 'Direction type tanlanishi shart.',
            'direction_type_id.exists'         => 'Tanlangan direction type mavjud emas.',
            'consulate_type_id.required'       => 'Consulate type tanlanishi shart.',
            'consulate_type_id.exists'         => 'Tanlangan consulate type mavjud emas.',
        ];
    }
}
