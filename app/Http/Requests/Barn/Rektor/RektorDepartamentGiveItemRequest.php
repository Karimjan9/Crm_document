<?php

namespace App\Http\Requests\Barn\Rektor;

use App\Models\ItemsModel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class RektorDepartamentGiveItemRequest extends FormRequest
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
    public function rules(Request $request)
    {
      
        $item=ItemsModel::where('id',$request->item)->first();
        $value=$item->extant-$item->absent;
        return [
            'number'=>['required','numeric','min:1',"max:$value"],
        ];
    }

    public function messages()
    {
        return [
            'number.numeric'=>'Son berilsin!',
            'number.required' => 'Soni kiritilmadi !',
            'number.min'=>" Jihoz soni berilmadi !",
            'number.max'=>'Jihoz soni oshib ketdi !',
        ];
    }
}
