@extends('layouts.app')

@section('title', 'แก้ไขแผนก')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> แก้ไขข้อมูลแผนก</span>
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('departments.update', $department) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสแผนก (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department->code) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อแผนกภาษาไทย <span class="text-danger">*</span></label>
                        <input type="text" name="name_th" class="form-control @error('name_th') is-invalid @enderror" value="{{ old('name_th', $department->name_th) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อแผนกภาษาอังกฤษ</label>
                        <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $department->name_en) }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">สถานที่ตั้งใน รพ.</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $department->location) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">เบอร์โทรศัพท์ภายใน</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $department->phone) }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ลำดับการแสดงผล</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $department->sort_order) }}" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActive">เปิดใช้งาน (Active)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">รายละเอียดเพิ่มเติม</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $department->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('departments.index') }}" class="btn btn-light border">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

