@extends('layouts.app')

@section('title', 'จัดการแผนก')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-building text-primary me-2"></i> จัดการแผนก (Departments)
        </h4>
        <p class="text-muted small mb-0">โครงสร้างหน่วยงานและแผนกบริการ โรงพยาบาลโกสัมพีนคร</p>
    </div>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มแผนกใหม่
        </a>
    @endif
</div>

<div class="card chart-card">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('departments.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาด้วยรหัส หรือชื่อแผนก..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>เปิดใช้งาน (Active)</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>ปิดใช้งาน (Inactive)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">ค้นหา</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-outline-secondary w-100">ล้าง</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">ลำดับ</th>
                        <th style="width: 120px;">รหัสแผนก</th>
                        <th>ชื่อแผนก (ไทย)</th>
                        <th>ชื่อแผนก (อังกฤษ)</th>
                        <th>สถานที่ตั้ง</th>
                        <th>เบอร์โทร</th>
                        <th class="text-center">สถิติสะสม</th>
                        <th class="text-center">สถานะ</th>
                        @if(auth()->user()->isAdmin())
                            <th class="text-end" style="width: 140px;">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td>{{ $dept->sort_order }}</td>
                            <td><span class="badge bg-light text-primary border fw-bold">{{ $dept->code }}</span></td>
                            <td class="fw-semibold">{{ $dept->name_th }}</td>
                            <td class="text-muted small">{{ $dept->name_en ?? '-' }}</td>
                            <td><small><i class="bi bi-geo-alt text-danger me-1"></i>{{ $dept->location ?? '-' }}</small></td>
                            <td><small><i class="bi bi-telephone text-success me-1"></i>{{ $dept->phone ?? '-' }}</small></td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info border">
                                    {{ number_format($dept->visit_statistics_count) }} รายการ
                                </span>
                            </td>
                            <td class="text-center">
                                @if($dept->is_active)
                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="text-end">
                                    <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบแผนก {{ $dept->name_th }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="text-center py-4 text-muted">
                                ไม่พบข้อมูลแผนก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $departments->links() }}
        </div>
    </div>
</div>
@endsection

