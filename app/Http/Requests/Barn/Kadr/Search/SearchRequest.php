<?php

namespace App\Http\Requests\Barn\Kadr\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'search'=>['required','digits:14'],
            
        ];
    }

    public function messages()
    {
        return [
            'search.required' => "JShIR maydoni  to'ldirilmadi !" ,
            'search.digits'=>"JShIR 14 symboldan kam bo'lmasligi kerak !" ,
           
        ];
    }

}
