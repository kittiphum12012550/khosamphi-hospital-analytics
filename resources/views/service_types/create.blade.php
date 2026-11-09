@extends('layouts.app')

@section('title', 'เพิ่มประเภทบริการ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-tag text-primary me-2"></i> เพิ่มประเภทบริการใหม่</span>
                <a href="{{ route('service-types.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('service-types.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสบริการ (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="เช่น GEN_EXAM, EMERGENCY" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อประเภทบริการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="เช่น ตรวจรักษาโรคทั่วไป" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">หมวดหมู่บริการ</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="เช่น รักษาพยาบาล, ทันตกรรม, ส่งเสริมและป้องกัน">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">คำอธิบาย</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="รายละเอียดบริการ">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">เปิดใช้งาน (Active)</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('service-types.index') }}" class="btn btn-light border">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

