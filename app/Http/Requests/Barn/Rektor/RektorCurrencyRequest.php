<?php

namespace App\Http\Requests\Barn\Rektor;

use Illuminate\Foundation\Http\FormRequest;

class RektorCurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'=>['required'],
            'value'=>['required','numeric']
        ];
    }

    public function messages()
{
    return [
        'name.required' => 'Valyuta nomi berilmadi !',
        'value.required'=>"Valyuta qiymati berilmadi !",
        'value.numeric'=>'Valyuta qiymati sonda berilsin !',
    ];
}
}

