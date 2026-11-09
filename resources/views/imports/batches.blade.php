@extends('layouts.app')

@section('title', 'ประวัติชุดการนำเข้าข้อมูล')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-clock-history text-primary me-2"></i> ประวัติชุดการนำเข้าข้อมูล (Import Batches)
        </h4>
        <p class="text-muted small mb-0">ตรวจสอบสถานะการนำเข้าย้อนหลัง และยกเลิกชุดข้อมูล (Rollback)</p>
    </div>
    <a href="{{ route('imports.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-cloud-arrow-up me-1"></i> นำเข้าไฟล์ใหม่
    </a>
</div>

<div class="card chart-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 170px;">รหัสชุดนำเข้า</th>
                        <th>ชื่อไฟล์ต้นฉบับ</th>
                        <th class="text-center" style="width: 100px;">แถวทั้งหมด</th>
                        <th class="text-center" style="width: 100px;">สำเร็จ</th>
                        <th class="text-center" style="width: 100px;">ข้อผิดพลาด</th>
                        <th class="text-center" style="width: 140px;">สถานะ</th>
                        <th>ผู้นำเข้า</th>
                        <th style="width: 140px;">วันที่ทำรายการ</th>
                        <th class="text-end" style="width: 130px;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td>
                                <code class="fw-bold text-primary">{{ $batch->batch_code }}</code>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $batch->original_filename }}</div>
                                <small class="text-muted">ขนาด: {{ number_format($batch->file_size_bytes / 1024, 1) }} KB | Type: {{ strtoupper($batch->file_type) }}</small>
                            </td>
                            <td class="text-center">{{ number_format($batch->total_rows) }}</td>
                            <td class="text-center text-success fw-bold">{{ number_format($batch->success_rows) }}</td>
                            <td class="text-center {{ $batch->error_rows > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ number_format($batch->error_rows) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $batch->status_badge_class }}">
                                    {{ $batch->status_label }}
                                </span>
                                @if($batch->status === 'rolled_back')
                                    <small class="d-block text-muted" style="font-size: 0.72rem;">
                                        โดย {{ $batch->rollbackUser?->name }} ({{ $batch->rolled_back_at?->format('d/m/Y H:i') }})
                                    </small>
                                @endif
                            </td>
                            <td class="small">{{ $batch->user?->name ?? 'ระบบ' }}</td>
                            <td class="small text-muted">{{ $batch->created_at->format('d/m/Y H:i น.') }}</td>
                            <td class="text-end">
                                @if($batch->canRollback())
                                    <form action="{{ route('imports.rollback', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('คำเตือน: คุณต้องการยกเลิกและลบข้อมูลสถิติทั้งหมด {{ number_format($batch->success_rows) }} รายการที่นำเข้าโดยชุดข้อมูล {{ $batch->batch_code }} ใช่หรือไม่?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ยกเลิกชุดข้อมูล (Rollback)">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                ยังไม่มีประวัติการนำเข้าไฟล์
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $batches->links() }}
        </div>
    </div>
</div>
@endsection

