@extends('layouts.app')

@section('title', 'ประวัติการทำรายการ (Audit Logs)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-shield-check text-primary me-2"></i> บันทึกประวัติการทำงาน (Audit Logs)
        </h4>
        <p class="text-muted small mb-0">ติดตามและตรวจสอบกิจกรรมของผู้ใช้งานในระบบเพื่อความปลอดภัยและความโปร่งใส</p>
    </div>
</div>

<!-- Filter Card -->
<div class="card filter-card p-3 mb-4">
    <form method="GET" action="{{ route('audit-logs.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">ค้นหาข้อความ</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ค้นหารายละเอียด หรือ IP..." value="{{ $filters['search'] }}">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">ผู้ดำเนินการ</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- ทุกผู้ใช้ --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ ($filters['user_id'] == $u->id) ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">ประเภทการทำรายการ</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">-- ทุก Action --</option>
                    @foreach($actions as $actKey => $actLabel)
                        <option value="{{ $actKey }}" {{ ($filters['action'] == $actKey) ? 'selected' : '' }}>
                            {{ $actLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">โมดูล</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">-- ทุก Module --</option>
                    @foreach($modules as $modKey => $modLabel)
                        <option value="{{ $modKey }}" {{ ($filters['module'] == $modKey) ? 'selected' : '' }}>
                            {{ $modLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">กรองประวัติ</button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary" title="ล้างตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Logs Table Card -->
<div class="card chart-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 170px;">วัน-เวลา</th>
                        <th style="width: 150px;">ผู้ดำเนินการ</th>
                        <th style="width: 130px;">Action</th>
                        <th style="width: 140px;">Module</th>
                        <th>รายละเอียดกิจกรรม</th>
                        <th style="width: 130px;">IP Address</th>
                        <th class="text-center" style="width: 80px;">ข้อมูล</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->user?->name ?? $log->user_name }}</div>
                                <span class="badge {{ $log->user?->role_badge_class ?? 'bg-secondary' }}" style="font-size: 0.65rem;">
                                    {{ $log->user?->role_label ?? 'ระบบ' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $log->action_badge_class }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $log->module_label }}
                                </span>
                            </td>
                            <td class="small">{{ $log->description }}</td>
                            <td class="small text-muted font-monospace">{{ $log->ip_address ?? '-' }}</td>
                            <td class="text-center">
                                @if($log->old_values || $log->new_values)
                                    <button type="button" class="btn btn-sm btn-outline-info p-1" onclick="viewLogPayload({{ json_encode($log) }})" title="ดูการเปลี่ยนแปลง">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                ไม่พบบันทึกประวัติการทำรายการ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Modal for Viewing JSON Payload Diff -->
<div class="modal fade" id="payloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="payloadModalTitle">
                    <i class="bi bi-file-diff me-2 text-primary"></i> ข้อมูลการเปลี่ยนแปลง (Payload Diff)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-danger"><i class="bi bi-dash-circle me-1"></i> ค่าเดิมก่อนแก้ไข (Old Values)</h6>
                        <pre id="oldValuesPre" class="p-3 bg-light border rounded small" style="max-height: 300px; overflow-y: auto;">-</pre>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-success"><i class="bi bi-plus-circle me-1"></i> ค่าใหม่หลังแก้ไข (New Values)</h6>
                        <pre id="newValuesPre" class="p-3 bg-light border rounded small" style="max-height: 300px; overflow-y: auto;">-</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewLogPayload(log) {
        document.getElementById('payloadModalTitle').innerHTML = `
            <i class="bi bi-file-diff me-2 text-primary"></i> รายละเอียดบันทึก #${log.id} (${log.action_label})
        `;

        document.getElementById('oldValuesPre').innerText = log.old_values ? JSON.stringify(log.old_values, null, 2) : 'ไม่มีข้อมูลเดิม';
        document.getElementById('newValuesPre').innerText = log.new_values ? JSON.stringify(log.new_values, null, 2) : 'ไม่มีข้อมูลใหม่';

        const modal = new bootstrap.Modal(document.getElementById('payloadModal'));
        modal.show();
    }
</script>
@endpush

