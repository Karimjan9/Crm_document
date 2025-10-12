<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FilialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id'); // yoki $this->filial — route parametrlashga qarab

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', "unique:filial,code,{$id}"],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
