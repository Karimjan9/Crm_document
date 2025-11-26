<?php

namespace App\Http\Requests\Barn\Career;

use Illuminate\Foundation\Http\FormRequest;

class CreateCareerRequest extends FormRequest
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
            'career_name'=>['required'],
            'rate'=>['required','min:0.25'],
            // 'divide'=>['required'],
            'branch'=>['required'],

        ];
    }
    public function messages()
    {
        return [
            'career_name.required' => 'Lavozim nomi berilmadi!',
            'rate.required'=>"Mansab stavkasi berilmadi !",
            "rate.min"=> "Lavozim minimum 0.25 dan kam bo'lmasligi kerak !",
            // 'divide'=>"Lavozim bo'linish yo bo'linmasligi berilmadi!",
            'branch'=>"Bo'lim tanlanmadi !",
        ];
    }
}
