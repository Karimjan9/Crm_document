<?php

namespace App\Http\Requests\Barn\Kadr\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                'full_name'=>['required'],
                // 'jshir'=>['required','digits:14'],

                'login'=>['required',],
                'password' => ['required','confirmed','min:4'],

                // 'surname'=>['required'],
                // 'other_name'=>['required'],
                // 'date'=>['required'],
                // 'birth_place'=> ['required'],
                // 'nation'=>['required'],
                // 'gender'=> ['required'],
                // 'phone_number'=> ['required','digits:12'],
                
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'Ism familiya kiritilmadi !',
            // 'jshir.required'=>"JSHIR kiritilmadi !",
            // 'jshir.digits'=>"JSHIR 14 ta raqam bo'lishi kerak !",
            // 'branch.required'=>"Bo'lim tanlanmadi kiritilmadi !",
            // 'career.required'=>"Mansab kiritilmadi !",
            'login.required'=>"Login kiritilmadi !", 
            'password.required'=>"Parol kiritilmadi !",
            'password.confirmed'=>"Parollar mos kelmadi  !",
            'password.min'=>"Parol 4 ta simvoldan kam bo'lishi mumkin emas !",

            // 'surname.required'=>'Familiya kiritilmadi!',
            // 'other_name.required'=>'Otasining ismi kiritilmadi!',
            // 'date.required'=>"Tug'ilgan sana kiritilmadi!",
            // 'nation.required'=>"Millati berilmadi!",
            // 'gender.required'=>"Jinsi belgilanmadi!",
            // 'phone_number.required'=>"Telefon raqam berilmadi!",
            // 'phone_number.digits'=>"Telefon raqam formati to'liq emas!",
            // 'birth_place.required'=>"Tug'ilgan joyi manzili kiritilmadis!"

            



        ];
    }
}
