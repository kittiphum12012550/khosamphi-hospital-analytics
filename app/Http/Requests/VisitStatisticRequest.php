<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff());
    }

    public function rules(): array
    {
        $id = $this->route('visit_statistic')?->id ?? $this->route('visit_statistic');

        return [
            'service_date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'service_type_id' => 'required|exists:service_types,id',
            'service_hour' => 'required|integer|min:0|max:23',
            'visit_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->route('visit_statistic')?->id ?? $this->route('visit_statistic');
            $date = $this->input('service_date');
            $dept = $this->input('department_id');
            $service = $this->input('service_type_id');
            $hour = $this->input('service_hour');

            if ($date && $dept && $service && $hour !== null) {
                $query = \App\Models\VisitStatistic::where('service_date', $date)
                    ->where('department_id', $dept)
                    ->where('service_type_id', $service)
                    ->where('service_hour', $hour);

                if ($id) {
                    $query->where('id', '!=', $id);
                }

                if ($query->exists()) {
                    $validator->errors()->add('service_date', 'มีข้อมูลสถิติของแผนก บริการ วันที่ และช่วงเวลานี้อยู่ในระบบแล้ว (กรุณาแก้ไขข้อมูลเดิมแทน)');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'service_date.required' => 'กรุณาระบุวันที่ให้บริการ',
            'department_id.required' => 'กรุณาเลือกแผนก',
            'service_type_id.required' => 'กรุณาเลือกประเภทบริการ',
            'service_hour.required' => 'กรุณาระบุชั่วโมงที่ให้บริการ',
            'visit_count.required' => 'กรุณาระบุจำนวนครั้งการเข้ารับบริการ',
            'visit_count.min' => 'จำนวนครั้งต้องเป็นจำนวนเต็มไม่ติดลบ',
        ];
    }
}

