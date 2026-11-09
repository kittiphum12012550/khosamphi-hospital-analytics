<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $deptId = $this->route('department')?->id ?? $this->route('department');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')->ignore($deptId),
            ],
            'name_th' => 'required|string|max:150',
            'name_en' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'กรุณากรอกรหัสแผนก',
            'code.unique' => 'รหัสแผนกนี้มีอยู่ในระบบแล้ว',
            'name_th.required' => 'กรุณากรอกชื่อแผนก (ภาษาไทย)',
        ];
    }
}

