<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบวิเคราะห์สถิติ') - {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom Hospital Theme CSS -->
    <link href="{{ asset('css/hospital-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading text-white d-flex align-items-center gap-2">
                <i class="bi bi-hospital fs-4 text-info"></i>
                <div>
                    <div class="lh-1 fw-bold fs-6">{{ config('hospital.name_th', 'รพ.โกสัมพีนคร') }}</div>
                    <small class="text-secondary" style="font-size: 0.7rem;">Analytics Dashboard</small>
                </div>
            </div>

            <div class="list-group list-group-flush my-2">
                <div class="sidebar-section-title">เมนูหลัก (Overview)</div>
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> แดชบอร์ด (Dashboard)
                </a>
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> รายงานสถิติ (Reports)
                </a>

                <div class="sidebar-section-title">บันทึกข้อมูล (Data Management)</div>
                <a href="{{ route('visit-statistics.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('visit-statistics.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-medical"></i> ข้อมูลสถิติ (Visit Records)
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                    <a href="{{ route('imports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('imports.index') || request()->routeIs('imports.preview') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-up"></i> นำเข้า CSV / Excel
                    </a>
                    <a href="{{ route('imports.batches') }}" class="list-group-item list-group-item-action {{ request()->routeIs('imports.batches') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> ประวัตินำเข้า (Batches)
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <div class="sidebar-section-title">การจัดการระบบ (Administration)</div>
                    <a href="{{ route('departments.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> จัดการแผนก (Departments)
                    </a>
                    <a href="{{ route('service-types.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('service-types.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> ประเภทบริการ (Services)
                    </a>
                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> จัดการผู้ใช้งาน (Users)
                    </a>
                    <a href="{{ route('audit-logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> บันทึกประวัติ (Audit Logs)
                    </a>
                @endif

                <div class="sidebar-section-title">บัญชีผู้ใช้ (Account)</div>
                <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> โปรไฟล์ (Profile)
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg hospital-navbar px-3 py-2 sticky-top">
                <div class="container-fluid px-0">
                    <button class="btn btn-outline-secondary btn-sm me-3" id="sidebarToggle" type="button">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <div class="d-none d-md-block">
                        <span class="fw-semibold text-dark">{{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</span>
                        <span class="text-muted small ms-2">จ.กำแพงเพชร (30 เตียง)</span>
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-3">
                        <span class="badge {{ auth()->user()->role_badge_class }} badge-role">
                            <i class="bi bi-shield-lock me-1"></i>{{ auth()->user()->role_label }}
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-dismiss="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill text-primary"></i>
                                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li class="px-3 py-1">
                                    <div class="fw-bold text-truncate" style="max-width: 200px;">{{ auth()->user()->name }}</div>
                                    <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="bi bi-person-gear me-2"></i> ข้อมูลส่วนตัว
                                    </a>
                                </li>
                                @if(auth()->user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('audit-logs.index') }}">
                                            <i class="bi bi-shield-check me-2"></i> Audit Logs
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Body Container -->
            <main class="container-fluid px-3 px-md-4 py-4">
                @include('components.alerts')
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mt-auto py-3 bg-white border-top text-center text-muted small">
                <div class="container-fluid">
                    <span>© {{ date('Y') + 543 }} {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }} — Khosamphi Hospital Analytics Dashboard v1.0.0 (Production Ready)</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js 4.4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- Hospital Charts Helper -->
    <script src="{{ asset('js/hospital-charts.js') }}"></script>

    <script>
        // Sidebar toggle for mobile/desktop
        document.getElementById('sidebarToggle')?.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('wrapper')?.classList.toggle('toggled');
        });
    </script>

    @stack('scripts')
</body>
</html>

