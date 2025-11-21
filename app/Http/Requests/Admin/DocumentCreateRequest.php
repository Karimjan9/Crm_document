<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DocumentCreateRequest extends FormRequest
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
        'service_id' => 'required|exists:services,id',
        'description' => 'nullable|string',
        'discount' => 'nullable|numeric|min:0',
        'addons' => 'nullable|array',
        'addons.*' => 'exists:services_addons,id',
        'client_id' => 'nullable|exists:clients,id',
        'new_client_name' => 'required_without:client_id|string|max:255',
        'new_client_phone' => 'required_without:client_id|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'service_id.required' => 'Service is required.',
            'service_id.exists' => 'Selected service does not exist.',
            'description.string' => 'Description must be a string.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount must be at least 0.',
            'addons.array' => 'Addons must be an array.',
            'addons.*.exists' => 'One or more selected addons do not exist.',
            'client_id.exists' => 'Selected client does not exist.',
            'new_client_name.required_without' => 'New client name is required when no existing client is selected.',
            'new_client_name.string' => 'New client name must be a string.',
            'new_client_name.max' => 'New client name may not be greater than 255 characters.',
            'new_client_phone.required_without' => 'New client phone is required when no existing client is selected.',
            'new_client_phone.string' => 'New client phone must be a string.',
            'new_client_phone.max' => 'New client phone may not be greater than 20 characters.',
            
        ];
    }
}
