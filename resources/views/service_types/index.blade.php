@extends('layouts.app')

@section('title', 'จัดการประเภทบริการ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-tags text-primary me-2"></i> จัดการประเภทบริการ (Service Types)
        </h4>
        <p class="text-muted small mb-0">ประเภทการตรวจรักษาและการให้บริการทางการแพทย์</p>
    </div>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('service-types.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มประเภทบริการ
        </a>
    @endif
</div>

<div class="card chart-card">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('service-types.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาด้วยรหัส หรือชื่อบริการ..." value="{{ request('search') }}">
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
                <a href="{{ route('service-types.index') }}" class="btn btn-sm btn-outline-secondary w-100">ล้าง</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 140px;">รหัสบริการ</th>
                        <th>ชื่อประเภทบริการ</th>
                        <th>หมวดหมู่</th>
                        <th>คำอธิบาย</th>
                        <th class="text-center">สถิติสะสม</th>
                        <th class="text-center">สถานะ</th>
                        @if(auth()->user()->isAdmin())
                            <th class="text-end" style="width: 140px;">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceTypes as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td><span class="badge bg-light text-info border fw-bold">{{ $service->code }}</span></td>
                            <td class="fw-semibold">{{ $service->name }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $service->category ?? '-' }}</span></td>
                            <td class="text-muted small">{{ $service->description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary border">
                                    {{ number_format($service->visit_statistics_count) }} รายการ
                                </span>
                            </td>
                            <td class="text-center">
                                @if($service->is_active)
                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="text-end">
                                    <a href="{{ route('service-types.edit', $service) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('service-types.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบประเภทบริการ {{ $service->name }} ?')">
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
                            <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="text-center py-4 text-muted">
                                ไม่พบข้อมูลประเภทบริการ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $serviceTypes->links() }}
        </div>
    </div>
</div>
@endsection

