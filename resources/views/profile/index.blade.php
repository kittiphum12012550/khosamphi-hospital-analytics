@extends('layouts.app')

@section('title', 'ข้อมูลส่วนตัว (Profile)')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-person-gear text-primary me-2"></i> จัดการข้อมูลส่วนตัวและรหัสผ่าน</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">กลุ่มงาน / แผนก</label>
                            <input type="text" name="department_name" class="form-control" value="{{ old('department_name', $user->department_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">เบอร์โทรศัพท์ติดต่อ</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key me-1"></i> เปลี่ยนรหัสผ่าน (เว้นว่างหากไม่ต้องการเปลี่ยน)</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสผ่านปัจจุบัน</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••">
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="อย่างน้อย 6 ตัวอักษร">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="พิมพ์ซ้ำอีกครั้ง">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <!-- Account Overview Card -->
        <div class="card chart-card mb-4">
            <div class="card-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle text-primary mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-person fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div>
                    <span class="badge {{ $user->role_badge_class }} px-3 py-2">
                        <i class="bi bi-shield-lock me-1"></i> {{ $user->role_label }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-top text-start small text-muted">
                    <div class="mb-1"><i class="bi bi-building me-2"></i> แผนก: {{ $user->department_name ?? '-' }}</div>
                    <div class="mb-1"><i class="bi bi-telephone me-2"></i> โทรศัพท์: {{ $user->phone ?? '-' }}</div>
                    <div><i class="bi bi-box-arrow-in-right me-2"></i> เข้าสู่ระบบล่าสุด: {{ $user->last_login_at?->format('d/m/Y H:i น.') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Recent User Activity -->
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold small text-muted"><i class="bi bi-activity me-1"></i> ประวัติกิจกรรมล่าสุดของคุณ</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($recentLogs as $log)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-auto">
                                <div class="fw-semibold">{{ $log->action_label }} ({{ $log->module_label }})</div>
                                <div class="text-muted text-truncate" style="max-width: 260px;">{{ $log->description }}</div>
                            </div>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ $log->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">ยังไม่มีบันทึกกิจกรรม</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

