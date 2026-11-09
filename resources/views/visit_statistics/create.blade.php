@extends('layouts.app')

@section('title', 'บันทึกสถิติการรับบริการ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-file-earmark-plus text-primary me-2"></i> บันทึกข้อมูลสถิติการรับบริการ</span>
                <a href="{{ route('visit-statistics.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('visit-statistics.store') }}" method="POST">
                    @csrf

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">วันที่ให้บริการ <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control @error('service_date') is-invalid @enderror" value="{{ old('service_date', $defaultDate) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ชั่วโมงที่ให้บริการ <span class="text-danger">*</span></label>
                            <select name="service_hour" class="form-select @error('service_hour') is-invalid @enderror" required>
                                @for($h = 0; $h <= 23; $h++)
                                    <option value="{{ $h }}" {{ old('service_hour', $defaultHour) == $h ? 'selected' : '' }}>
                                        {{ sprintf('%02d:00 - %02d:59 น.', $h, $h) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">แผนกผู้ให้บริการ <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">-- กรุณาเลือกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->code }} - {{ $dept->name_th }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ประเภทบริการ <span class="text-danger">*</span></label>
                        <select name="service_type_id" class="form-select @error('service_type_id') is-invalid @enderror" required>
                            <option value="">-- กรุณาเลือกประเภทบริการ --</option>
                            @foreach($serviceTypes as $service)
                                <option value="{{ $service->id }}" {{ old('service_type_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ $service->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">จำนวนครั้งการเข้ารับบริการ (Visits) <span class="text-danger">*</span></label>
                        <input type="number" name="visit_count" class="form-control fs-5 @error('visit_count') is-invalid @enderror" value="{{ old('visit_count', 1) }}" min="0" required>
                        <small class="text-muted">ให้นับเป็นจำนวนครั้งของการเข้ารับบริการ (Visits) ในช่วงชั่วโมงนั้น</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">หมายเหตุ</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="เช่น ช่วงเวลาคลินิกพิเศษ หรือผู้ป่วยอุบัติเหตุกลุ่ม">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('visit-statistics.index') }}" class="btn btn-light border">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูลสถิติ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

