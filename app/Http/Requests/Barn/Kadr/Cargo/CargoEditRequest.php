<?php

namespace App\Http\Requests\Barn\Kadr\Cargo;

use Illuminate\Foundation\Http\FormRequest;

class CargoEditRequest extends FormRequest
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
            'file_contract'=>['mimes:pdf,doc,docx','max:5120'],
            'file_faktura'=>['mimes:pdf,doc,docx','max:5120'],
        ];
    }

    public function messages()
    {
        return [
            'file_contract.mimes' => 'Shartnoma formati xato!',
            'file_contract.max'=>"Shartnoma hajmi oshib ketdi!",
            'file_faktura.mimes' => 'Shot Faktura formati xato!',
            'file_faktura.max'=>"Shot Faktura hajmi oshib ketdi!",
        ];
    }
}
