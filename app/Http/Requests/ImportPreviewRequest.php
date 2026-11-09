<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff());
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'กรุณาเลือกไฟล์ที่ต้องการนำเข้า',
            'file.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'file.mimes' => 'รองรับเฉพาะไฟล์ประเภท CSV (.csv), Excel (.xlsx, .xls) เท่านั้น',
            'file.max' => 'ขนาดไฟล์ต้องไม่เกิน 10 MB',
        ];
    }
}

