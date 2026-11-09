@extends('layouts.app')

@section('title', 'จัดการข้อมูลสถิติการรับบริการ')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-journal-medical text-primary me-2"></i> ข้อมูลสถิติการรับบริการ (Visit Statistics)
        </h4>
        <p class="text-muted small mb-0">รายการสถิติจำนวนครั้งการให้บริการแยกตามแผนกและช่วงเวลา</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('imports.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cloud-arrow-up me-1"></i> นำเข้าไฟล์
            </a>
            <a href="{{ route('visit-statistics.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> บันทึกข้อมูลใหม่
            </a>
        @endif
    </div>
</div>

<!-- Filter Box -->
<div class="card filter-card p-3 mb-4">
    <form method="GET" action="{{ route('visit-statistics.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">ตั้งแต่วันที่</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">ถึงวันที่</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">แผนก</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">-- ทุกแผนก --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($filters['department_id'] == $dept->id) ? 'selected' : '' }}>
                            {{ $dept->code }} - {{ $dept->name_th }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">ประเภทบริการ</label>
                <select name="service_type_id" class="form-select form-select-sm">
                    <option value="">-- ทุกประเภทบริการ --</option>
                    @foreach($serviceTypes as $service)
                        <option value="{{ $service->id }}" {{ ($filters['service_type_id'] == $service->id) ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">กรอง</button>
                <a href="{{ route('visit-statistics.index') }}" class="btn btn-sm btn-outline-secondary" title="ล้างตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card chart-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>รายการบันทึกสถิติ (พบทั้งหมด {{ number_format($statistics->total()) }} รายการ)</span>
        <span class="badge bg-success fs-6">รวมทั้งสิ้น: {{ number_format($totalVisits) }} ครั้ง</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 120px;">วันที่</th>
                        <th style="width: 140px;">ช่วงเวลา</th>
                        <th>แผนก</th>
                        <th>ประเภทบริการ</th>
                        <th class="text-end" style="width: 130px;">จำนวนครั้ง (Visits)</th>
                        <th>แหล่งข้อมูล</th>
                        <th>ผู้บันทึก</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                            <th class="text-end" style="width: 120px;">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistics as $item)
                        <tr>
                            <td>{{ $item->service_date->locale('th')->isoFormat('D MMM YYYY') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item->formatted_hour }}</span></td>
                            <td class="fw-semibold text-primary">
                                {{ $item->department?->code }} - {{ $item->department?->name_th }}
                            </td>
                            <td>{{ $item->serviceType?->name }}</td>
                            <td class="text-end fw-bold fs-6 text-dark">
                                {{ number_format($item->visit_count) }}
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $item->source_label }}
                                </span>
                                @if($item->batch)
                                    <small class="d-block text-muted" style="font-size: 0.75rem;">
                                        {{ $item->batch->batch_code }}
                                    </small>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $item->creator?->name ?? 'ระบบ' }}
                            </td>
                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <td class="text-end">
                                    <a href="{{ route('visit-statistics.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('visit-statistics.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบรายการสถิตินี้?')">
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
                            <td colspan="{{ (auth()->user()->isAdmin() || auth()->user()->isStaff()) ? 8 : 7 }}" class="text-center py-4 text-muted">
                                ไม่พบข้อมูลสถิติตามเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $statistics->links() }}
        </div>
    </div>
</div>
@endsection

