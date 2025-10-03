<?php

namespace App\Http\Requests\Barn\SecondDataFiles;

use Illuminate\Foundation\Http\FormRequest;

class SecondDataRequest extends FormRequest
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
        $a=3*1024;
        $b=2*1024;
        return [
            'obektivka'=>['max:'."$a",'mimes:pdf'],
            'diplom'=>['max:'."$a",'mimes:pdf'],
            // 'divide'=>['required'],
            'image'=>['max:'."$b"],

        ];
    }
    public function messages()
    {
        return [
            'obektivka.max' => 'Obektivka 3 mb dan oshmasligi kerak!',
            'obektivka.mimes'=>"Obektiva faqat pdf bo'lishi kerak!",
            "diplom.max"=> "Diplom ilovasi 3 mb dan oshmasligi kerak!",
            'diplom.mimes'=>"Diplom ilovasi faqat pdf bo'lishi kerak !",
            'image.max'=>"3X4 rasm 2 mb dan oshmasligi kerak!",

        ];
    }
}
