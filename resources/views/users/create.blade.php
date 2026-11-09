@extends('layouts.app')

@section('title', 'เพิ่มผู้ใช้งานใหม่')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-person-plus text-primary me-2"></i> เพิ่มบัญชีผู้ใช้งานใหม่</span>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="เช่น นพ.สมศักดิ์ มุ่งมั่น" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">อีเมล (ใช้เป็นบัญชีเข้าสู่ระบบ) <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="name@khosamphi.go.th" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="อย่างน้อย 6 ตัวอักษร" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="พิมพ์ซ้ำอีกครั้ง" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ระดับสิทธิ์ (Role) <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- เลือกระดับสิทธิ์ --</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (ผู้ดูแลระบบ: เข้าถึงได้ทุกฟังก์ชัน)</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff (เจ้าหน้าที่: บันทึกข้อมูลและนำเข้าไฟล์)</option>
                            <option value="viewer" {{ old('role', 'viewer') === 'viewer' ? 'selected' : '' }}>Viewer (ผู้ดูรายงาน: ดูสถิติและส่งออกรายงาน)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">กลุ่มงาน / แผนก</label>
                            <input type="text" name="department_name" class="form-control" value="{{ old('department_name') }}" placeholder="เช่น งานเวชระเบียน">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="เช่น 055-741-690">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">เปิดใช้งานบัญชี (Active)</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-light border">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> บันทึกผู้ใช้งาน
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

