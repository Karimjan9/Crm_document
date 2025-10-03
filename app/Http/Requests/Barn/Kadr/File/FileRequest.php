<?php

namespace App\Http\Requests\Barn\Kadr\File;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
            'file'=>['required','max:10240'],
            'branch'=>['required'],
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Rasm yuklanmadi !',
            'branch.required'=>"Bo'lim tanlanmadi"
        ];
    }
}
