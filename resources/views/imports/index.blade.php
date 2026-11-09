@extends('layouts.app')

@section('title', 'นำเข้าข้อมูล CSV / Excel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-cloud-arrow-up text-primary me-2"></i> นำเข้าข้อมูลสถิติ (Import CSV / Excel)
        </h4>
        <p class="text-muted small mb-0">อัปโหลดไฟล์ชุดสถิติการรับบริการเข้าสู่ฐานข้อมูล พร้อมระบบตรวจสอบความถูกต้องล่วงหน้า</p>
    </div>
    <div>
        <a href="{{ route('imports.batches') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i> ประวัติการนำเข้า
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Upload Box -->
    <div class="col-12 col-lg-7">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-upload text-primary me-2"></i> ขั้นตอนที่ 1: เลือกไฟล์เพื่อตรวจสอบ (Preview)</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('imports.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4 text-center p-4 border border-2 border-dashed rounded-3 bg-light">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">เลือกไฟล์ CSV หรือ Excel</h6>
                        <p class="text-muted small mb-3">รองรับนามสกุล .csv, .xlsx, .xls ขนาดไฟล์ไม่เกิน 10 MB</p>
                        <input type="file" name="file" id="importFile" class="form-control @error('file') is-invalid @enderror" accept=".csv, .xlsx, .xls, .txt" required>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('imports.template') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> ดาวน์โหลดเทมเพลตตัวอย่าง (CSV)
                        </a>

                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-eye me-1"></i> ตรวจสอบข้อมูลก่อนนำเข้า (Preview)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Instructions & Guideline -->
    <div class="col-12 col-lg-5">
        <div class="card chart-card">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-info-circle text-info me-2"></i> ข้อกำหนดและรูปแบบไฟล์</span>
            </div>
            <div class="card-body small">
                <h6 class="fw-bold text-dark">หัวตารางที่ต้องมีในไฟล์ (Headers):</h6>
                <ul class="mb-3 ps-3">
                    <li><code>service_date</code>: วันที่ (เช่น 2024-11-01 หรือ 01/11/2024)</li>
                    <li><code>department_code</code>: รหัสแผนก (เช่น OPD, ER, DENT, PT, TTM, LAB, PHARM)</li>
                    <li><code>service_type_code</code>: รหัสประเภทบริการ (เช่น GEN_EXAM, EMERGENCY, DENTAL_CARE)</li>
                    <li><code>service_hour</code>: ชั่วโมงที่ให้บริการ (ตัวเลข 0 - 23)</li>
                    <li><code>visit_count</code>: จำนวนครั้งการเข้ารับบริการ (จำนวนเต็มไม่ติดลบ)</li>
                    <li><code>notes</code>: หมายเหตุ (มีหรือไม่มีก็ได้)</li>
                </ul>

                <h6 class="fw-bold text-dark">ระบบความปลอดภัยของข้อมูล:</h6>
                <ul class="mb-0 ps-3 text-secondary">
                    <li><strong>SHA-256 File Hash:</strong> ป้องกันการอัปโหลดไฟล์เดิมซ้ำ</li>
                    <li><strong>Data Validation:</strong> ตรวจสอบแผนก บริการ วันที่ และความซ้ำซ้อน</li>
                    <li><strong>Database Transaction:</strong> มั่นใจได้ว่าข้อมูลจะไม่ขาดหายกึ่งกลาง</li>
                    <li><strong>Rollback Policy:</strong> สามารถย้อนกลับและลบชุดข้อมูลได้หากพบข้อผิดพลาดภายหลัง</li>
                </ul>
            </div>
        </div>

        <!-- Recent Batches Quick Card -->
        @if(count($recentBatches) > 0)
            <div class="card chart-card mt-3">
                <div class="card-header">
                    <span class="fw-bold small text-muted">การนำเข้าล่าสุด</span>
                    <a href="{{ route('imports.batches') }}" class="small text-decoration-none">ดูทั้งหมด</a>
                </div>
                <ul class="list-group list-group-flush small">
                    @foreach($recentBatches as $b)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold text-truncate" style="max-width: 180px;">{{ $b->original_filename }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $b->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="badge {{ $b->status_badge_class }}">{{ $b->status_label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection

