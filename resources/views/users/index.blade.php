@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งานระบบ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-people text-primary me-2"></i> จัดการผู้ใช้งานระบบ (User Management)
        </h4>
        <p class="text-muted small mb-0">กำหนดบทบาทและสิทธิ์การเข้าใช้งานระบบ (Admin, Staff, Viewer)</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้ใช้งานใหม่
    </a>
</div>

<div class="card chart-card">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ อีเมล หรือฝ่ายงาน..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- ทุกระดับสิทธิ์ --</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin (ผู้ดูแลระบบ)</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff (เจ้าหน้าที่)</option>
                    <option value="viewer" {{ request('role') == 'viewer' ? 'selected' : '' }}>Viewer (ผู้ดูรายงาน)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">ค้นหา</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary w-100">ล้าง</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>กลุ่มงาน/ฝ่าย</th>
                        <th class="text-center" style="width: 140px;">ระดับสิทธิ์ (Role)</th>
                        <th class="text-center" style="width: 120px;">สถานะ</th>
                        <th>เข้าสู่ระบบล่าสุด</th>
                        <th class="text-end" style="width: 130px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $user->phone ?? '-' }}</small>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department_name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $user->role_badge_class }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary">ปิดระงับ</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i น.') : 'ยังไม่เคยเข้าสู่ระบบ' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบบัญชีผู้ใช้ {{ $user->name }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                ไม่พบบัญชีผู้ใช้งานตามเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

