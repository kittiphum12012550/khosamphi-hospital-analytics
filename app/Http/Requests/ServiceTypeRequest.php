<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('service_type')?->id ?? $this->route('service_type');

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('service_types', 'code')->ignore($id),
            ],
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'กรุณากรอกรหัสประเภทบริการ',
            'code.unique' => 'รหัสประเภทบริการนี้มีอยู่ในระบบแล้ว',
            'name.required' => 'กรุณากรอกชื่อประเภทบริการ',
        ];
    }
}

