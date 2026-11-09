@extends('layouts.app')

@section('title', 'แก้ไขประเภทบริการ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> แก้ไขประเภทบริการ</span>
                <a href="{{ route('service-types.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('service-types.update', $serviceType) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสบริการ (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $serviceType->code) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อประเภทบริการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $serviceType->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">หมวดหมู่บริการ</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $serviceType->category) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">คำอธิบาย</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $serviceType->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $serviceType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">เปิดใช้งาน (Active)</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('service-types.index') }}" class="btn btn-light border">ยกเลิก</a>
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

