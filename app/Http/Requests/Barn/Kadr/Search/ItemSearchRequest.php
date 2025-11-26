<?php

namespace App\Http\Requests\Barn\Kadr\Search;

use Illuminate\Foundation\Http\FormRequest;

class ItemSearchRequest extends FormRequest
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
      
                'search'=>['required','min:4'],
               
        ];
    }

    public function messages()
    {
        return [
            'search.required' => "Qidiruv maydoni  to'ldirilmadi !" ,
            'search.min'=>"Qidiruv  4 symboldan kam bo'lmasligi kerak !" ,
           
        ];
    }
}
