@extends('layouts.auth')

@section('content')
<div class="login-card bg-white mx-3">
    <div class="login-header">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 mb-3 text-primary shadow-sm" style="width: 72px; height: 72px;">
            <i class="bi bi-hospital fs-1" style="color: #0aa2a3;"></i>
        </div>
        <h4 class="fw-bold mb-1 text-dark">{{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</h4>
        <div class="text-muted small">ระบบสารสนเทศวิเคราะห์สถิติการรับบริการ (Analytics Dashboard)</div>
    </div>

    <div class="p-4 pt-2">
        @include('components.alerts')

        <form method="POST" action="{{ route('login') }}" class="mt-2">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small text-secondary">อีเมลผู้ใช้งาน (Email)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@khosamphi.go.th') }}" required autocomplete="email" autofocus placeholder="name@khosamphi.go.th">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold small text-secondary">รหัสผ่าน (Password)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" id="password" name="password" value="admin1234" required placeholder="••••••••">
                </div>
            </div>

            <div class="mb-3 form-check d-flex justify-content-between align-items-center">
                <div>
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label small text-secondary" for="remember">จดจำฉันในระบบ</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" style="background-color: #0aa2a3; border-color: #0aa2a3;">
                <i class="bi bi-box-arrow-in-right me-2"></i> เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <div class="text-muted small fw-semibold mb-2 text-center">บัญชีทดสอบระบบ (Click เพื่อกรอกอัตโนมัติ):</div>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-danger demo-pill" onclick="fillCredentials('admin@khosamphi.go.th', 'admin1234')">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary demo-pill" onclick="fillCredentials('staff@khosamphi.go.th', 'staff1234')">
                    <i class="bi bi-pencil-square me-1"></i> Staff
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-pill" onclick="fillCredentials('viewer@khosamphi.go.th', 'viewer1234')">
                    <i class="bi bi-eye me-1"></i> Viewer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endpush

