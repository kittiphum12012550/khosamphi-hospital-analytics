@extends('layouts.app')

@section('title', 'ตรวจสอบข้อมูลก่อนนำเข้า (Preview)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-check text-success me-2"></i> ผลการตรวจสอบข้อมูล (Import Preview)
        </h4>
        <p class="text-muted small mb-0">ตรวจสอบความถูกต้องของข้อมูลและข้อผิดพลาดก่อนบันทึกจริงลงฐานข้อมูล</p>
    </div>
    <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg me-1"></i> ยกเลิก
    </a>
</div>

@if($previewData['is_duplicate_file'])
    <div class="alert alert-warning alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
        <div>
            <strong>คำเตือน: ตรวจพบไฟล์ที่อาจซ้ำซ้อน!</strong>
            <div>ไฟล์ที่มีเนื้อหาและแฮชตรงกับไฟล์นี้เคยถูกนำเข้าแล้วในชุดข้อมูล <code>{{ $previewData['existing_batch_code'] }}</code> หากนำเข้าซ้ำ ข้อมูลในช่วงเวลาเดียวกันจะถูกบันทึกทับ (Upsert)</div>
        </div>
    </div>
@endif

<!-- Counters Row -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card stats-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">จำนวนแถวทั้งหมดในไฟล์</div>
                    <div class="stats-value text-dark">{{ number_format($previewData['total_rows']) }}</div>
                    <small class="text-muted">ชื่อไฟล์: {{ $previewData['original_filename'] }}</small>
                </div>
                <div class="stats-icon-bg bg-light text-dark">
                    <i class="bi bi-list-ol"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card stats-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">แถวที่ถูกต้อง พร้อมนำเข้า</div>
                    <div class="stats-value text-success">{{ number_format($previewData['valid_count']) }}</div>
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i> ผ่านเกณฑ์การตรวจสอบ</small>
                </div>
                <div class="stats-icon-bg bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card stats-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">แถวที่มีข้อผิดพลาด (จะถูกข้าม)</div>
                    <div class="stats-value {{ $previewData['error_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ number_format($previewData['error_count']) }}
                    </div>
                    <small class="{{ $previewData['error_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                        <i class="bi bi-exclamation-octagon me-1"></i> {{ $previewData['error_count'] > 0 ? 'พบข้อผิดพลาดตามรายละเอียดด้านล่าง' : 'ไม่มีข้อผิดพลาด' }}
                    </small>
                </div>
                <div class="stats-icon-bg bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Summary Accordion if errors exist -->
@if($previewData['error_count'] > 0)
    <div class="card chart-card border-danger border-opacity-25 mb-4">
        <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold d-flex align-items-center justify-content-between">
            <span><i class="bi bi-exclamation-triangle-fill me-2"></i> รายละเอียดข้อผิดพลาดรายแถว (แสดงสูงสุด 50 แถวแรก)</span>
            <span class="badge bg-danger">{{ $previewData['error_count'] }} ข้อผิดพลาด</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 250px;">
                <table class="table table-sm table-striped mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">แถวที่</th>
                            <th>ข้อมูลในไฟล์</th>
                            <th>สาเหตุข้อผิดพลาด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData['error_summary'] as $errRow)
                            <tr>
                                <td class="fw-bold text-danger">แถว {{ $errRow['row_number'] }}</td>
                                <td>
                                    วันที่: {{ $errRow['raw_date'] ?: '-' }} | แผนก: {{ $errRow['department_name'] ?: '-' }} | บริการ: {{ $errRow['service_type_name'] ?: '-' }} | ชั่วโมง: {{ $errRow['hour'] }} | ครั้ง: {{ $errRow['visit_count'] }}
                                </td>
                                <td>
                                    <ul class="text-danger mb-0 ps-3">
                                        @foreach($errRow['errors'] as $msg)
                                            <li>{{ $msg }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Preview Table -->
<div class="card chart-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-table text-primary me-2"></i> ตัวอย่างข้อมูลในไฟล์ (Preview สูงสุด 30 แถว)</span>
        <span class="badge bg-light text-dark border">SHA-256: {{ substr($previewData['file_hash'], 0, 16) }}...</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">แถวที่</th>
                        <th>วันที่</th>
                        <th>ช่วงเวลา</th>
                        <th>แผนก</th>
                        <th>ประเภทบริการ</th>
                        <th class="text-end">จำนวนครั้ง</th>
                        <th class="text-center" style="width: 140px;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewData['preview_rows'] as $row)
                        <tr class="{{ $row['is_valid'] ? '' : 'table-danger bg-opacity-25' }}">
                            <td class="fw-bold text-muted">{{ $row['row_number'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ sprintf('%02d:00 - %02d:59 น.', $row['hour'], $row['hour']) }}</td>
                            <td class="fw-semibold">{{ $row['department_name'] }}</td>
                            <td>{{ $row['service_type_name'] }}</td>
                            <td class="text-end fw-bold">{{ $row['visit_count'] }}</td>
                            <td class="text-center">
                                @if($row['is_valid'])
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i> ถูกต้อง</span>
                                @else
                                    <span class="badge bg-danger" title="{{ implode(', ', $row['errors']) }}">
                                        <i class="bi bi-x-circle me-1"></i> ผิดพลาด
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Confirmation Action Card -->
<div class="card chart-card p-4 text-center">
    @if($previewData['can_import'])
        <h5 class="fw-bold text-dark mb-2">พร้อมนำเข้าข้อมูลจำนวน {{ number_format($previewData['valid_count']) }} แถว?</h5>
        <p class="text-muted small mb-3">
            ระบบจะบันทึกข้อมูลด้วย Database Transaction และสร้างรหัสชุดนำเข้า (Import Batch) สำหรับการตรวจสอบย้อนหลัง
        </p>

        <form action="{{ route('imports.confirm') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="temp_path" value="{{ $previewData['temp_path'] }}">
            <input type="hidden" name="original_filename" value="{{ $previewData['original_filename'] }}">
            <input type="hidden" name="file_hash" value="{{ $previewData['file_hash'] }}">

            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('imports.index') }}" class="btn btn-light border px-4">ยกเลิก</a>
                <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i> ยืนยันการนำเข้าข้อมูล
                </button>
            </div>
        </form>
    @else
        <h5 class="fw-bold text-danger mb-2">ไม่สามารถนำเข้าข้อมูลได้</h5>
        <p class="text-muted small mb-3">ไม่พบแถวข้อมูลที่ถูกต้องตามเกณฑ์ กรุณาแก้ไขไฟล์แล้วลองใหม่อีกครั้ง</p>
        <a href="{{ route('imports.index') }}" class="btn btn-secondary px-4">กลับไปหน้าอัปโหลดไฟล์</a>
    @endif
</div>
@endsection

