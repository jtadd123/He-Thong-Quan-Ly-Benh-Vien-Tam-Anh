<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Quản Trị Bệnh Viện') - Bệnh Viện Đa Khoa Tâm Anh</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --tam-primary: #0284c7;
            --tam-primary-rgb: 2, 132, 199;
            --tam-primary-hover: #0369a1;
            --tam-accent: #0d9488;
            --tam-dark: #0f172a;
            --tam-sidebar-bg: #0f172a;
            --tam-sidebar-hover: #1e293b;
            --bs-primary: #0284c7;
            --bs-primary-rgb: 2, 132, 199;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, 0.08) !important;
        }

        @media print {
            .sidebar, .top-navbar, .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Fix pagination SVG icon sizes */
        .pagination svg, svg.w-5 {
            width: 1rem !important;
            height: 1rem !important;
            display: inline-block !important;
        }

        .sidebar {
            width: 270px;
            background-color: var(--tam-sidebar-bg);
            min-height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-search-box {
            position: relative;
        }

        .sidebar-search-box input {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
            font-size: 0.8rem;
            padding-left: 2rem;
            border-radius: 0.5rem;
        }

        .sidebar-search-box input:focus {
            border-color: var(--tam-primary) !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
        }

        .sidebar-search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .sidebar-clear-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            font-size: 0.85rem;
            display: none;
        }

        .sidebar-category-header {
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 0.6rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            user-select: none;
            margin-top: 0.35rem;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .sidebar-category-header:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-category-header.active-category {
            color: #38bdf8;
        }

        .sidebar-category-header .chevron-icon {
            font-size: 0.65rem;
            transition: transform 0.25s ease;
        }

        .sidebar-category-header[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.45rem 0.75rem;
            border-radius: 0.4rem;
            font-weight: 500;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.15rem;
            transition: all 0.15s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--tam-sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--tam-primary);
            font-weight: 600;
        }

        .sidebar-subnav {
            padding-left: 0.5rem;
            margin-left: 0.25rem;
            border-left: 2px solid rgba(148, 163, 184, 0.15);
            margin-top: 0.2rem;
            margin-bottom: 0.4rem;
        }

        .main-content {
            flex-grow: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        /* Spotlight Universal Search Styles */
        .spotlight-result-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1.25rem;
            text-decoration: none;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .spotlight-result-item:hover, .spotlight-result-item.selected {
            background-color: #f0f9ff;
            color: var(--tam-primary);
        }

        .spotlight-result-item:last-child {
            border-bottom: none;
        }

        .spotlight-category-header {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            background-color: #f8fafc;
            padding: 0.35rem 1.25rem;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-xs {
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -270px;
                top: 0;
                bottom: 0;
            }
            .sidebar.show {
                left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="d-flex">

    <!-- Sidebar -->
    <aside class="sidebar p-3 d-flex flex-column" id="adminSidebar">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 mb-3 text-decoration-none px-2 text-white">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                <i class="bi bi-hospital fs-5"></i>
            </div>
            <div>
                <span class="fw-bold d-block lh-1 text-white" style="font-size: 1.1rem;">BV TÂM ANH</span>
                <small class="text-secondary" style="font-size: 0.65rem; text-transform: uppercase;">Medical Management</small>
            </div>
        </a>

        <!-- Thanh Tìm Kiếm Nhanh Chức Năng -->
        <div class="sidebar-search-box px-1 mb-3 position-relative">
            <i class="bi bi-search sidebar-search-icon"></i>
            <input type="text" id="sidebarSearchInput" class="form-control form-control-sm" placeholder="Tìm nhanh... (Ctrl+K)" autocomplete="off">
            <span class="badge bg-primary bg-opacity-75 rounded-pill position-absolute d-none" id="sidebarSearchBadge" style="right: 2rem; top: 50%; transform: translateY(-50%); font-size: 0.65rem;"></span>
            <i class="bi bi-x-circle-fill sidebar-clear-icon" id="sidebarSearchClear" title="Xóa tìm kiếm"></i>
        </div>

        <div class="flex-grow-1 overflow-y-auto pe-1" id="sidebarMenuContainer">
            <ul class="nav flex-column" id="sidebarNavList">

                {{-- 1. QUẢN TRỊ HỆ THỐNG (ADMIN) --}}
                @if(Auth::check() && Auth::user()->isAdmin())
                    @php $isCatAdminActive = request()->is('admin/*'); @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatAdminActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupAdmin" 
                                aria-expanded="{{ $isCatAdminActive ? 'true' : 'false' }}" aria-controls="groupAdmin">
                            <span><i class="bi bi-shield-lock-fill text-primary me-2"></i>Quản Trị Hệ Thống</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatAdminActive ? 'show' : '' }}" id="groupAdmin">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard Tổng quan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people"></i> Quản lý Người dùng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/specialties*') ? 'active' : '' }}" href="{{ route('admin.specialties.index') }}">
                                        <i class="bi bi-diagram-3"></i> Quản lý Chuyên khoa
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/doctors*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}">
                                        <i class="bi bi-person-badge"></i> Bác sĩ & Lịch trực
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/security-audit*') ? 'active' : '' }}" href="{{ route('admin.security-audit.index') }}">
                            <i class="bi bi-shield-lock"></i> Nhật Ký Kiểm Toán Bảo Mật
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear-wide-connected"></i> Cấu Hình Hệ Thống
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/system-health*') ? 'active' : '' }}" href="{{ route('admin.system-health.index') }}">
                            <i class="bi bi-heart-pulse"></i> Giám Sát Sức Khỏe Hạ Tầng
                        </a>
                    </li>
                @endif

                {{-- 2. TIẾP ĐÓN & SẢNH CHỜ --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatReceptionActive = request()->is('reception/appointments*') || request()->is('reception/patients*') || 
                            request()->is('reception/day-beds*') || request()->is('reception/mobility*') || 
                            request()->is('reception/wayfinding*') || request()->is('reception/pre-visit*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatReceptionActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupReception" 
                                aria-expanded="{{ $isCatReceptionActive ? 'true' : 'false' }}" aria-controls="groupReception">
                            <span><i class="bi bi-calendar-check text-info me-2"></i>Tiếp Đón & Sảnh Chờ</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatReceptionActive ? 'show' : '' }}" id="groupReception">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/appointments*') ? 'active' : '' }}" href="{{ route('reception.appointments.index') }}">
                                        <i class="bi bi-calendar-check"></i> Tiếp nhận & Check-in
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/patients*') ? 'active' : '' }}" href="{{ route('reception.patients.index') }}">
                                        <i class="bi bi-person-vcard"></i> Quản lý Bệnh nhân
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/day-beds*') ? 'active' : '' }}" href="{{ route('reception.day-beds.index') }}">
                                        <i class="bi bi-hospital"></i> Giường Ngoại Trú & Lưu Ngày
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/mobility*') ? 'active' : '' }}" href="{{ route('reception.mobility.index') }}">
                                        <i class="bi bi-person-wheelchair text-primary"></i> Mượn Trả Xe Lăn Sảnh
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/wayfinding*') ? 'active' : '' }}" href="{{ route('reception.wayfinding.index') }}">
                                        <i class="bi bi-map-fill text-primary"></i> Bản Đồ Dẫn Đường Tòa Nhà
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/pre-visit*') ? 'active' : '' }}" href="{{ route('reception.pre-visit.index') }}">
                                        <i class="bi bi-phone-fill text-info"></i> Tự Khai Báo Pre-Visit
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 3. THU PHÍ, VIỆN PHÍ & BẢO HIỂM --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatFinanceActive = request()->is('reception/invoices*') || request()->is('reception/wallets*') || 
                            request()->is('reception/insurance-transfers*') || request()->is('reception/private-insurance*') || 
                            request()->is('reception/claim-scrubber*') || request()->is('reception/certificates*') || 
                            request()->is('reception/consents*') || request()->is('reception/record-requests*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatFinanceActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupFinance" 
                                aria-expanded="{{ $isCatFinanceActive ? 'true' : 'false' }}" aria-controls="groupFinance">
                            <span><i class="bi bi-wallet2 text-success me-2"></i>Viện Phí & Bảo Hiểm</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatFinanceActive ? 'show' : '' }}" id="groupFinance">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/invoices*') ? 'active' : '' }}" href="{{ route('reception.invoices.index') }}">
                                        <i class="bi bi-qr-code-scan text-success"></i> Hóa Đơn & Thu Phí VietQR
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/wallets*') ? 'active' : '' }}" href="{{ route('reception.wallets.index') }}">
                                        <i class="bi bi-wallet2 text-success"></i> Ví Ký Quỹ & Tạm Ứng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/insurance-transfers*') ? 'active' : '' }}" href="{{ route('reception.insurance-transfers.index') }}">
                                        <i class="bi bi-file-earmark-medical text-primary"></i> Chuyển Tuyến BHYT (M40)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/private-insurance*') ? 'active' : '' }}" href="{{ route('reception.private-insurance.index') }}">
                                        <i class="bi bi-shield-lock-fill text-info"></i> Bảo Lãnh Tư Nhân (GOP)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/claim-scrubber*') ? 'active' : '' }}" href="{{ route('reception.claim-scrubber.index') }}">
                                        <i class="bi bi-file-earmark-code-fill text-warning"></i> Giám Định BHYT & XML 4210
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/certificates*') ? 'active' : '' }}" href="{{ route('reception.certificates.index') }}">
                                        <i class="bi bi-journal-medical"></i> Sổ Giấy Nghỉ BHXH (C65)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/consents*') ? 'active' : '' }}" href="{{ route('reception.consents.index') }}">
                                        <i class="bi bi-file-earmark-check"></i> Bản Cam Đoan Ký Số
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/record-requests*') ? 'active' : '' }}" href="{{ route('reception.record-requests.index') }}">
                                        <i class="bi bi-file-earmark-lock2-fill text-warning"></i> Cổng Sao Lục Bệnh Án
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 4. DƯỢC BỆNH VIỆN & VẮC-XIN --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatPharmacyActive = request()->is('reception/pharmacy*') || request()->is('reception/vaccinations*') || 
                            request()->is('reception/narcotics*') || request()->is('reception/cold-chain*') || request()->is('reception/deliveries*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatPharmacyActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupPharmacy" 
                                aria-expanded="{{ $isCatPharmacyActive ? 'true' : 'false' }}" aria-controls="groupPharmacy">
                            <span><i class="bi bi-capsule-pill text-warning me-2"></i>Dược & Tiêm Chủng</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatPharmacyActive ? 'show' : '' }}" id="groupPharmacy">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/pharmacy') || (request()->is('reception/pharmacy*') && !request()->is('reception/pharmacy/inventory*')) ? 'active' : '' }}" href="{{ route('reception.pharmacy.index') }}">
                                        <i class="bi bi-capsule-pill"></i> Quầy Dược Phát Thuốc
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/pharmacy/inventory*') ? 'active' : '' }}" href="{{ route('reception.pharmacy.inventory.index') }}">
                                        <i class="bi bi-box-seam"></i> Kho Dược & Tồn Kho (FEFO)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/narcotics*') ? 'active' : '' }}" href="{{ route('reception.narcotics.index') }}">
                                        <i class="bi bi-capsule text-danger"></i> Tủ Thuốc Gây Nghiện TT20
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/cold-chain*') ? 'active' : '' }}" href="{{ route('reception.cold-chain.index') }}">
                                        <i class="bi bi-snow text-info"></i> Chuỗi Lạnh IoT Vắc-Xin
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/vaccinations*') ? 'active' : '' }}" href="{{ route('reception.vaccinations.index') }}">
                                        <i class="bi bi-shield-check text-success"></i> Tiêm Chủng & Sổ Vắc-Xin
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/deliveries*') ? 'active' : '' }}" href="{{ route('reception.deliveries.index') }}">
                                        <i class="bi bi-box2-heart text-primary"></i> Giao Thuốc Tận Nhà
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 5. CẤP CỨU & AN TOÀN NGƯỜI BỆNH --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatEmergencyActive = request()->is('reception/code-red*') || request()->is('reception/ambulances*') || 
                            request()->is('reception/news2-rrt*') || request()->is('reception/risk-assessments*') || 
                            request()->is('reception/medical-incidents*') || request()->is('reception/emergency-custody*') || 
                            request()->is('reception/visitor-passes*') || request()->is('reception/occupational-exposure*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatEmergencyActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupEmergency" 
                                aria-expanded="{{ $isCatEmergencyActive ? 'true' : 'false' }}" aria-controls="groupEmergency">
                            <span><i class="bi bi-bell-fill text-danger me-2"></i>Cấp Cứu & An Toàn</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatEmergencyActive ? 'show' : '' }}" id="groupEmergency">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/code-red*') ? 'active' : '' }}" href="{{ route('reception.code-red.index') }}">
                                        <i class="bi bi-bell-fill text-danger"></i> Báo Động Đỏ Cấp Cứu
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/ambulances*') ? 'active' : '' }}" href="{{ route('reception.ambulances.index') }}">
                                        <i class="bi bi-truck-flatbed text-danger"></i> Đội Xe Cấp Cứu Ngoại Viện
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/news2-rrt*') ? 'active' : '' }}" href="{{ route('reception.news2-rrt.index') }}">
                                        <i class="bi bi-activity text-danger"></i> Cảnh Báo Sớm NEWS2 & RRT
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/risk-assessments*') ? 'active' : '' }}" href="{{ route('reception.risk-assessments.index') }}">
                                        <i class="bi bi-shield-check text-warning"></i> Thang Đo Braden & Morse
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/medical-incidents*') ? 'active' : '' }}" href="{{ route('reception.medical-incidents.index') }}">
                                        <i class="bi bi-shield-exclamation text-danger"></i> Báo Cáo Sự Cố (Near-Miss)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/emergency-custody*') ? 'active' : '' }}" href="{{ route('reception.emergency-custody.index') }}">
                                        <i class="bi bi-safe2-fill text-secondary"></i> Tài Sản Cấp Cứu & Đồ Rơi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/visitor-passes*') ? 'active' : '' }}" href="{{ route('reception.visitor-passes.index') }}">
                                        <i class="bi bi-person-badge-fill text-info"></i> Thẻ Thăm Bệnh Điện Tử
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/occupational-exposure*') ? 'active' : '' }}" href="{{ route('reception.occupational-exposure.index') }}">
                                        <i class="bi bi-bandaid-fill text-danger"></i> Phơi Nhiễm Kim Tiêm & PEP
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 6. CẬN LÂM SÀNG & BUỒNG BỆNH --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatDiagnosticsActive = request()->is('reception/lab-orders*') || request()->is('reception/blood-transfusions*') || 
                            request()->is('reception/bed-board*') || request()->is('reception/hemodialysis*') || 
                            request()->is('reception/cssd*') || request()->is('reception/nutrition*') || 
                            request()->is('reception/social-work*') || request()->is('reception/living-will*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatDiagnosticsActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupDiagnostics" 
                                aria-expanded="{{ $isCatDiagnosticsActive ? 'true' : 'false' }}" aria-controls="groupDiagnostics">
                            <span><i class="bi bi-moisture text-primary me-2"></i>Cận Lâm Sàng & Buồng Bệnh</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatDiagnosticsActive ? 'show' : '' }}" id="groupDiagnostics">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/lab-orders*') ? 'active' : '' }}" href="{{ route('reception.lab-orders.index') }}">
                                        <i class="bi bi-moisture text-info"></i> Cận Lâm Sàng & Xét Nghiệm
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/blood-transfusions*') ? 'active' : '' }}" href="{{ route('reception.blood-transfusions.index') }}">
                                        <i class="bi bi-droplet-half text-danger"></i> An Toàn Truyền Máu (ABO/Rh)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/bed-board*') ? 'active' : '' }}" href="{{ route('reception.bed-board.index') }}">
                                        <i class="bi bi-door-open-fill text-success"></i> Quản Lý Buồng Bệnh & Giường
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/hemodialysis*') ? 'active' : '' }}" href="{{ route('reception.hemodialysis.index') }}">
                                        <i class="bi bi-droplet-fill text-danger"></i> Lọc Máu Thận Nhân Tạo
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/cssd*') ? 'active' : '' }}" href="{{ route('reception.cssd.index') }}">
                                        <i class="bi bi-tools text-secondary"></i> Tiệt Khuẩn Dụng Cụ CSSD
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/nutrition*') ? 'active' : '' }}" href="{{ route('reception.nutrition.index') }}">
                                        <i class="bi bi-heart-pulse text-success"></i> Dinh Dưỡng & Thực Đơn
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/social-work*') ? 'active' : '' }}" href="{{ route('reception.social-work.index') }}">
                                        <i class="bi bi-heart-fill text-success"></i> Công Tác Xã Hội & Dịch Thuật
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/living-will*') ? 'active' : '' }}" href="{{ route('reception.living-will.index') }}">
                                        <i class="bi bi-heart-pulse-fill text-danger"></i> Hiến Tạng & Di Chúc Y Tế
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 7. BÀN KHÁM BÁC SĨ & NGOẠI TRÚ --}}
                @php 
                    $isCatClinicalActive = request()->is('doctor/queue*') || request()->is('consultations*') || 
                        request()->is('reception/corporate-screening*') || request()->is('reception/day-surgeries*') || 
                        request()->is('reception/chronic-recalls*');
                @endphp
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isDoctor() || Auth::user()->isReceptionist()))
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatClinicalActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupClinical" 
                                aria-expanded="{{ $isCatClinicalActive ? 'true' : 'false' }}" aria-controls="groupClinical">
                            <span><i class="bi bi-heart-pulse text-danger me-2"></i>Khám Bệnh & Bác Sĩ</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatClinicalActive ? 'show' : '' }}" id="groupClinical">
                            <ul class="nav flex-column sidebar-subnav">
                                @if(Auth::user()->isAdmin() || Auth::user()->isDoctor())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('doctor/queue*') ? 'active' : '' }}" href="{{ route('doctor.queue') }}">
                                            <i class="bi bi-heart-pulse"></i> Hàng đợi khám hôm nay
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('consultations*') ? 'active' : '' }}" href="{{ route('consultations.index') }}">
                                            <i class="bi bi-people-fill"></i> Hội Chẩn Đa Chuyên Khoa
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->isAdmin() || Auth::user()->isReceptionist())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('reception/corporate-screening*') ? 'active' : '' }}" href="{{ route('reception.corporate-screening.index') }}">
                                            <i class="bi bi-buildings-fill text-primary"></i> Khám Sức Khỏe Doanh Nghiệp
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('reception/day-surgeries*') ? 'active' : '' }}" href="{{ route('reception.day-surgeries.index') }}">
                                            <i class="bi bi-hospital-fill text-warning"></i> Phẫu Thuật Trong Ngày (WHO)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('reception/chronic-recalls*') ? 'active' : '' }}" href="{{ route('reception.chronic-recalls.index') }}">
                                            <i class="bi bi-alarm-fill text-info"></i> Tái Khám Tự Động (Recall)
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 8. KỸ THUẬT Y KHOA CHUYÊN SÂU (SMART HOSPITAL SUITE) --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatSmartHospitalActive = request()->is('reception/operating-room*') || request()->is('reception/clinical-ai*') || 
                            request()->is('reception/biomedical-infection*') || request()->is('reception/maternity-oncology*') || 
                            request()->is('reception/clinical-pharmacy*') || request()->is('reception/rehab-esg*') || 
                            request()->is('reception/ai-clinical-decision*') || request()->is('reception/advanced-specialty*') || 
                            request()->is('reception/hospital-operations*') || request()->is('reception/finance-hr*') || 
                            request()->is('reception/patient-experience*') || request()->is('reception/critical-resuscitation*') || 
                            request()->is('reception/transplant-robotics*') || request()->is('reception/emergency-maternity-cardio*') || 
                            request()->is('reception/genomics-hospital-at-home*') || request()->is('reception/oncology-advanced*') || 
                            request()->is('reception/critical-extracorporeal*') || request()->is('reception/advanced-intervention*') || 
                            request()->is('reception/critical-resuscitation-safety*') || request()->is('reception/surgical-chemo-device*') || 
                            request()->is('reception/patient-safety-pediatric*') || request()->is('reception/smart-inpatient-ward-facility*') || 
                            request()->is('reception/specialty-high-risk-room*') || request()->is('reception/facility-automation-lounge*') || 
                            request()->is('reception/autonomous-logistics-esg*') || request()->is('reception/asset-engineering-haccp-linen*') || 
                            request()->is('reception/disaster-safety-fatigue-discharge*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatSmartHospitalActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupSmartHospital" 
                                aria-expanded="{{ $isCatSmartHospitalActive ? 'true' : 'false' }}" aria-controls="groupSmartHospital">
                            <span><i class="bi bi-cpu-fill text-info me-2"></i>Kỹ Thuật Y Khoa Chuyên Sâu</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatSmartHospitalActive ? 'show' : '' }}" id="groupSmartHospital">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/operating-room*') ? 'active' : '' }}" href="{{ route('reception.operating-room.index') }}">
                                        <i class="bi bi-hospital text-info"></i> Phòng Mổ & Hồi Tỉnh PACU
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/clinical-ai*') ? 'active' : '' }}" href="{{ route('reception.clinical-ai.index') }}">
                                        <i class="bi bi-cpu text-warning"></i> AI Lâm Sàng & An Toàn Sinh Học
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/biomedical-infection*') ? 'active' : '' }}" href="{{ route('reception.biomedical-infection.index') }}">
                                        <i class="bi bi-tools text-primary"></i> Kỹ Thuật Y Sinh & KSNK (HAI)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/maternity-oncology*') ? 'active' : '' }}" href="{{ route('reception.maternity-oncology.index') }}">
                                        <i class="bi bi-heart-pulse text-danger"></i> Sản Nhi, Ung Bướu & Giờ Vàng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/clinical-pharmacy*') ? 'active' : '' }}" href="{{ route('reception.clinical-pharmacy.index') }}">
                                        <i class="bi bi-capsule-pill text-info"></i> Dược Lâm Sàng, TDM & TPN
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/rehab-esg*') ? 'active' : '' }}" href="{{ route('reception.rehab-esg.index') }}">
                                        <i class="bi bi-tree text-success"></i> PHCN, Đông Y YHCT & ESG
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/ai-clinical-decision*') ? 'active' : '' }}" href="{{ route('reception.ai-clinical-decision.index') }}">
                                        <i class="bi bi-cpu text-primary"></i> AI Quyết Định CDSS & Sepsis
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/advanced-specialty*') ? 'active' : '' }}" href="{{ route('reception.advanced-specialty.index') }}">
                                        <i class="bi bi-diagram-3-fill text-danger"></i> Lâm Sàng Chuyên Khoa Sâu
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/hospital-operations*') ? 'active' : '' }}" href="{{ route('reception.hospital-operations.index') }}">
                                        <i class="bi bi-buildings text-warning"></i> Vận Hành & Hậu Cần Khoa Phòng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/finance-hr*') ? 'active' : '' }}" href="{{ route('reception.finance-hr.index') }}">
                                        <i class="bi bi-cash-coin text-success"></i> Tài Chính & DRG Y Tế
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/patient-experience*') ? 'active' : '' }}" href="{{ route('reception.patient-experience.index') }}">
                                        <i class="bi bi-person-heart text-info"></i> Trải Nghiệm & Chăm Sóc Số
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/critical-resuscitation*') ? 'active' : '' }}" href="{{ route('reception.critical-resuscitation.index') }}">
                                        <i class="bi bi-shield-shaded text-danger"></i> Hồi Sức Tích Cực, DERS & PBM
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/transplant-robotics*') ? 'active' : '' }}" href="{{ route('reception.transplant-robotics.index') }}">
                                        <i class="bi bi-robot text-primary"></i> Ghép Tạng, Da Vinci & Hybrid OR
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/emergency-maternity-cardio*') ? 'active' : '' }}" href="{{ route('reception.emergency-maternity-cardio.index') }}">
                                        <i class="bi bi-broadcast text-warning"></i> Cấp Cứu 5G, Sản Nhi & Can Thiệp
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/genomics-hospital-at-home*') ? 'active' : '' }}" href="{{ route('reception.genomics-hospital-at-home.index') }}">
                                        <i class="bi bi-dna text-info"></i> Di Truyền, Biobank & HAH Tại Nhà
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/oncology-advanced*') ? 'active' : '' }}" href="{{ route('reception.oncology-advanced.index') }}">
                                        <i class="bi bi-radioactive text-danger"></i> Ung Thư 4D, CAR-T & Hội Đồng MTB
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/critical-extracorporeal*') ? 'active' : '' }}" href="{{ route('reception.critical-extracorporeal.index') }}">
                                        <i class="bi bi-lungs text-primary"></i> Hồi Sức Sống Còn, ECMO & CRRT
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/advanced-intervention*') ? 'active' : '' }}" href="{{ route('reception.advanced-intervention.index') }}">
                                        <i class="bi bi-heart-pulse-fill text-danger"></i> Can Thiệp ESD, CIED & Radar Kháng Sinh
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/critical-resuscitation-safety*') ? 'active' : '' }}" href="{{ route('reception.critical-resuscitation-safety.index') }}">
                                        <i class="bi bi-activity text-danger"></i> Hồi Sức Code Blue, ROTEM & MGPS
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/surgical-chemo-device*') ? 'active' : '' }}" href="{{ route('reception.surgical-chemo-device.index') }}">
                                        <i class="bi bi-tools text-primary"></i> Tiệt Trùng CSSD, Giảm Đau PCA & LVAD
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/patient-safety-pediatric*') ? 'active' : '' }}" href="{{ route('reception.patient-safety-pediatric.index') }}">
                                        <i class="bi bi-shield-lock-fill text-danger"></i> An Ninh Sơ Sinh RTLS & Thử Nghiệm GCP
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/smart-inpatient-ward-facility*') ? 'active' : '' }}" href="{{ route('reception.smart-inpatient-ward-facility.index') }}">
                                        <i class="bi bi-hospital-fill text-primary"></i> Buồng Bệnh & Trạm Điều Dưỡng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/specialty-high-risk-room*') ? 'active' : '' }}" href="{{ route('reception.specialty-high-risk-room.index') }}">
                                        <i class="bi bi-shield-lock-fill text-warning"></i> Phòng Ghép Tủy PE & Phòng Chì
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/facility-automation-lounge*') ? 'active' : '' }}" href="{{ route('reception.facility-automation-lounge.index') }}">
                                        <i class="bi bi-cpu-fill text-success"></i> Tự Động Hóa Tòa Nhà & Lounge VIP
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/autonomous-logistics-esg*') ? 'active' : '' }}" href="{{ route('reception.autonomous-logistics-esg.index') }}">
                                        <i class="bi bi-robot text-primary"></i> Robot AGV, Ống Khí Nén & Microgrid
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/asset-engineering-haccp-linen*') ? 'active' : '' }}" href="{{ route('reception.asset-engineering-haccp-linen.index') }}">
                                        <i class="bi bi-geo-alt-fill text-warning"></i> Định Vị BLE, CMMS & Bếp HACCP
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/disaster-safety-fatigue-discharge*') ? 'active' : '' }}" href="{{ route('reception.disaster-safety-fatigue-discharge.index') }}">
                                        <i class="bi bi-shield-shaded text-danger"></i> Thảm Họa HICS & Meds-to-Beds
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 9. BÁO CÁO & THỐNG KÊ --}}
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    @php 
                        $isCatReportsActive = request()->is('reception/analytics*') || request()->is('reception/surveys*') || request()->is('reception/notifications*');
                    @endphp
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header {{ $isCatReportsActive ? 'active-category' : '' }}" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupReports" 
                                aria-expanded="{{ $isCatReportsActive ? 'true' : 'false' }}" aria-controls="groupReports">
                            <span><i class="bi bi-graph-up-arrow text-success me-2"></i>Báo Cáo & Thống Kê</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse {{ $isCatReportsActive ? 'show' : '' }}" id="groupReports">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/analytics*') ? 'active' : '' }}" href="{{ route('reception.analytics.index') }}">
                                        <i class="bi bi-graph-up-arrow"></i> Giám Sát Hiệu Suất (BI)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/surveys*') ? 'active' : '' }}" href="{{ route('reception.surveys.index') }}">
                                        <i class="bi bi-star-half text-warning"></i> Khảo Sát Hài Lòng BYT
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('reception/notifications*') ? 'active' : '' }}" href="{{ route('reception.notifications.index') }}">
                                        <i class="bi bi-chat-quote text-info"></i> Thông Báo ZNS & SMS
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 10. CỔNG THÔNG TIN BỆNH NHÂN (PATIENT) --}}
                @if(Auth::check() && Auth::user()->isPatient())
                    <li class="sidebar-group-item mb-1">
                        <button class="sidebar-category-header active-category" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#groupPatient" 
                                aria-expanded="true" aria-controls="groupPatient">
                            <span><i class="bi bi-person-fill text-primary me-2"></i>Cổng Bệnh Nhân</span>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="collapse show" id="groupPatient">
                            <ul class="nav flex-column sidebar-subnav">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('patient/history*') ? 'active' : '' }}" href="{{ route('patient.history') }}">
                                        <i class="bi bi-journal-medical"></i> Lịch sử khám bệnh
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('booking') ? 'active' : '' }}" href="{{ route('booking.create') }}">
                                        <i class="bi bi-calendar-plus"></i> Đặt lịch khám mới
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- 11. CÀI ĐẶT TÀI KHOẢN --}}
                <li class="sidebar-group-item mb-1">
                    <button class="sidebar-category-header" 
                            type="button" data-bs-toggle="collapse" data-bs-target="#groupSettings" 
                            aria-expanded="false" aria-controls="groupSettings">
                        <span><i class="bi bi-gear-fill text-secondary me-2"></i>Hệ Thống & Tài Khoản</span>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </button>
                    <div class="collapse" id="groupSettings">
                        <ul class="nav flex-column sidebar-subnav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('profile/password*') ? 'active' : '' }}" href="{{ route('profile.password') }}">
                                    <i class="bi bi-shield-lock"></i> Đổi mật khẩu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/') }}" target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i> Xem Trang chủ Web
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>

            <!-- Thông báo khi không tìm thấy kết quả tìm kiếm -->
            <div id="sidebarNoResults" class="text-center py-4 text-secondary small d-none">
                <i class="bi bi-search fs-4 d-block mb-1 text-muted"></i>
                Không tìm thấy chức năng phù hợp
            </div>
        </div>

        <div class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="sidebarToggleBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="fw-semibold text-muted small d-none d-xl-block">
                    HỆ THỐNG QUẢN LÝ BỆNH VIỆN ĐA KHOA TÂM ANH
                </div>
            </div>

            <!-- Spotlight Universal Search Trigger Button in Navbar -->
            <button type="button" class="btn btn-light bg-light border d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill shadow-xs text-secondary" 
                    id="navbarUniversalSearchBtn" data-bs-toggle="modal" data-bs-target="#universalSearchModal" 
                    style="max-width: 360px; width: 100%; font-size: 0.82rem; cursor: pointer;">
                <i class="bi bi-search text-primary"></i>
                <span class="flex-grow-1 text-start text-truncate">Tìm kiếm toàn viện (Ctrl+K)...</span>
                <span class="badge bg-white text-muted border font-monospace shadow-xs py-1" style="font-size: 0.65rem;">Ctrl K</span>
            </button>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('booking.create') }}" class="btn btn-sm btn-primary d-none d-md-inline-flex align-items-center gap-1 rounded-pill">
                    <i class="bi bi-plus-circle"></i> Đặt lịch hẹn
                </a>

                <div class="dropdown">
                    <button class="btn btn-light bg-white border dropdown-toggle d-flex align-items-center gap-2 py-1 px-3 rounded-pill" type="button" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 text-primary"></i>
                        <span class="fw-medium text-dark">{{ Auth::user()->name ?? 'Người dùng' }}</span>
                        @if(Auth::check())
                            <span class="badge {{ Auth::user()->role_badge_class }} rounded-pill" style="font-size: 0.65rem;">
                                {{ Auth::user()->role_name }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="adminUserDropdown">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ Auth::user()->name ?? '' }}</div>
                            <div class="small text-muted">{{ Auth::user()->email ?? '' }}</div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.password') }}">
                                <i class="bi bi-key me-2 text-muted"></i> Đổi mật khẩu
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="p-4 flex-grow-1">
            <!-- Global Flash Messages (Toast Style) -->
            <x-alert />

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-muted small text-center text-md-start d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>Bệnh Viện Đa Khoa Tâm Anh</strong> - Hệ thống quản lý khám chữa bệnh ngoại trú.
            </div>
            <div>
                Hệ thống Quản Trị Bệnh Viện Đa Khoa Chuẩn Doanh Nghiệp
            </div>
        </footer>
    </div>

    <!-- Universal Hospital Spotlight Search Modal -->
    <div class="modal fade" id="universalSearchModal" tabindex="-1" aria-labelledby="universalSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 760px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden; background: #ffffff;">
                <!-- Header with Input -->
                <div class="p-3 border-bottom bg-white d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10" style="width: 42px; height: 42px;">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div class="flex-grow-1 position-relative">
                        <input type="text" id="spotlightSearchInput" class="form-control form-control-lg border-0 shadow-none px-0" 
                               placeholder="Tìm kiếm toàn viện (Tên BN, Mã BN, SĐT, Bác sĩ, Thuốc, Hóa đơn...)" 
                               style="font-size: 1.05rem; font-weight: 500;" autocomplete="off">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spotlightLoadingSpinner" role="status"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="px-4 py-2 bg-light border-bottom d-flex align-items-center gap-2 overflow-x-auto text-nowrap" id="spotlightPills">
                    <span class="text-muted small fw-semibold me-1"><i class="bi bi-funnel me-1"></i>Lọc:</span>
                    <button type="button" class="btn btn-xs btn-primary rounded-pill spotlight-filter-btn" data-filter="all">Tất cả</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="patients">Bệnh nhân</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="doctors">Bác sĩ</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="medications">Thuốc & Dược</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="invoices">Hóa đơn</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="beds">Buồng & Giường</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="labs">Xét nghiệm</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="appointments">Lịch hẹn</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="specialties">Chuyên khoa</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill spotlight-filter-btn" data-filter="navigation">Chức năng</button>
                </div>

                <!-- Results List -->
                <div class="modal-body p-0" style="max-height: 480px; overflow-y: auto;" id="spotlightResultsContainer">
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-search fs-2 d-block mb-2 text-primary opacity-50"></i>
                        <div class="fw-semibold">Gõ ít nhất 2 ký tự để tìm kiếm toàn viện</div>
                        <small class="text-secondary">Ví dụ: <em>Lê Văn A</em>, <em>BN-2026...</em>, <em>Paracetamol</em>, <em>0912...</em>, <em>Phòng mổ</em></small>
                    </div>
                </div>

                <!-- Footer with shortcuts -->
                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between align-items-center small text-muted">
                    <div class="d-flex align-items-center gap-3">
                        <span><kbd class="bg-white text-dark border px-1.5 py-0.5 rounded shadow-xs">↑</kbd> <kbd class="bg-white text-dark border px-1.5 py-0.5 rounded shadow-xs">↓</kbd> Di chuyển</span>
                        <span><kbd class="bg-white text-dark border px-1.5 py-0.5 rounded shadow-xs">Enter</kbd> Mở</span>
                        <span><kbd class="bg-white text-dark border px-1.5 py-0.5 rounded shadow-xs">Esc</kbd> Đóng</span>
                    </div>
                    <div class="text-primary fw-medium">
                        <i class="bi bi-hospital me-1"></i> Bệnh Viện Đa Khoa Tâm Anh
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Mobile Toggle
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebar = document.getElementById('adminSidebar');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });
            }

            // Global SweetAlert2 delete / action confirmation
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;

                if (form.dataset.swalConfirmed === 'true') {
                    return;
                }

                let confirmMsg = form.getAttribute('data-confirm') || form.dataset.confirm;

                const onsubmitAttr = form.getAttribute('onsubmit');
                if (!confirmMsg && onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                    if (match && match[1]) {
                        confirmMsg = match[1];
                        form.removeAttribute('onsubmit');
                    }
                }

                if (confirmMsg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    Swal.fire({
                        title: 'Xác nhận thao tác',
                        text: confirmMsg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Đồng ý',
                        cancelButtonText: 'Hủy bỏ',
                        reverseButtons: true,
                        focusCancel: true,
                        customClass: {
                            confirmButton: 'btn btn-danger px-4 py-2 me-2 rounded-3',
                            cancelButton: 'btn btn-secondary px-4 py-2 rounded-3',
                            popup: 'rounded-4 shadow-lg border-0'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.swalConfirmed = 'true';
                            form.submit();
                        }
                    });
                }
            }, true);

            // Hàm chuẩn hóa tiếng Việt không dấu
            function removeVietnameseTones(str) {
                if (!str) return '';
                str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
                str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
                str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
                str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
                str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
                str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
                str = str.replace(/đ/g, "d");
                str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "a");
                str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "e");
                str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "i");
                str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "o");
                str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "u");
                str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "y");
                str = str.replace(/Đ/g, "d");
                str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, "");
                str = str.replace(/\u02C6|\u0306|\u031B/g, "");
                return str.toLowerCase().trim();
            }

            // Thanh Tìm Kiếm Nhanh Chức Năng Sidebar
            const searchInput = document.getElementById('sidebarSearchInput');
            const clearBtn = document.getElementById('sidebarSearchClear');
            const searchBadge = document.getElementById('sidebarSearchBadge');
            const groupItems = document.querySelectorAll('.sidebar-group-item');
            const noResults = document.getElementById('sidebarNoResults');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const rawQuery = this.value.trim().toLowerCase();
                    const normQuery = removeVietnameseTones(rawQuery);

                    if (rawQuery.length > 0) {
                        if (clearBtn) clearBtn.style.display = 'block';
                    } else {
                        if (clearBtn) clearBtn.style.display = 'none';
                    }

                    let totalVisible = 0;

                    groupItems.forEach(function (group) {
                        const collapseEl = group.querySelector('.collapse');
                        const navLinks = group.querySelectorAll('.nav-link');
                        let groupHasMatch = false;

                        navLinks.forEach(function (link) {
                            const rawText = link.textContent.toLowerCase();
                            const normText = removeVietnameseTones(link.textContent);

                            const isMatch = (rawQuery === '' || rawText.includes(rawQuery) || normText.includes(normQuery));

                            if (isMatch) {
                                link.parentElement.style.display = '';
                                groupHasMatch = true;
                                totalVisible++;
                            } else {
                                link.parentElement.style.display = 'none';
                            }
                        });

                        if (groupHasMatch) {
                            group.style.display = '';
                            if (collapseEl && rawQuery !== '') {
                                collapseEl.classList.add('show');
                            }
                        } else {
                            group.style.display = 'none';
                        }
                    });

                    // Cập nhật số lượng kết quả
                    if (searchBadge) {
                        if (rawQuery.length > 0) {
                            searchBadge.textContent = totalVisible;
                            searchBadge.classList.remove('d-none');
                        } else {
                            searchBadge.classList.add('d-none');
                        }
                    }

                    // Nếu query trống thì khôi phục trạng thái ban đầu của collapse
                    if (rawQuery === '') {
                        groupItems.forEach(function (group) {
                            group.style.display = '';
                            const header = group.querySelector('.sidebar-category-header');
                            const collapseEl = group.querySelector('.collapse');
                            const navLinks = group.querySelectorAll('.nav-link');
                            navLinks.forEach(function (l) { l.parentElement.style.display = ''; });

                            if (header && collapseEl) {
                                const isExpanded = header.getAttribute('aria-expanded') === 'true';
                                if (isExpanded) {
                                    collapseEl.classList.add('show');
                                } else {
                                    collapseEl.classList.remove('show');
                                }
                            }
                        });
                        if (noResults) noResults.classList.add('d-none');
                    } else {
                        if (noResults) {
                            if (totalVisible === 0) {
                                noResults.classList.remove('d-none');
                            } else {
                                noResults.classList.add('d-none');
                            }
                        }
                    }
                });

                // Nhấn Enter để nhảy ngay đến mục đầu tiên tìm thấy
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const firstLink = document.querySelector('.sidebar-group-item:not([style*="display: none"]) .nav-item:not([style*="display: none"]) .nav-link');
                        if (firstLink) {
                            firstLink.click();
                        }
                    }
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.focus();
                    });
                }
            }

            // ==========================================
            // SPOTLIGHT UNIVERSAL SEARCH SCRIPT
            // ==========================================
            const spotlightModalEl = document.getElementById('universalSearchModal');
            const spotlightInput = document.getElementById('spotlightSearchInput');
            const spotlightSpinner = document.getElementById('spotlightLoadingSpinner');
            const spotlightResults = document.getElementById('spotlightResultsContainer');
            const spotlightFilterBtns = document.querySelectorAll('.spotlight-filter-btn');

            let spotlightData = null;
            let currentFilter = 'all';
            let spotlightDebounceTimer = null;
            let selectedIndex = -1;

            if (spotlightModalEl && spotlightInput) {
                const spotlightModal = new bootstrap.Modal(spotlightModalEl);

                // Auto focus input when modal is shown
                spotlightModalEl.addEventListener('shown.bs.modal', function () {
                    spotlightInput.focus();
                    spotlightInput.select();
                    if (!spotlightData) {
                        fetchSpotlightResults('');
                    }
                });

                // Global Shortcuts: Ctrl+K or / opens Spotlight Universal Search
                document.addEventListener('keydown', function (e) {
                    if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName))) {
                        e.preventDefault();
                        spotlightModal.show();
                    }
                });

                // Debounced input search
                spotlightInput.addEventListener('input', function () {
                    clearTimeout(spotlightDebounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2 && query.length > 0) {
                        return;
                    }

                    if (spotlightSpinner) spotlightSpinner.classList.remove('d-none');

                    spotlightDebounceTimer = setTimeout(function () {
                        fetchSpotlightResults(query);
                    }, 180);
                });

                // Filter buttons
                spotlightFilterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        spotlightFilterBtns.forEach(b => {
                            b.classList.remove('btn-primary');
                            b.classList.add('btn-outline-secondary');
                        });
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-primary');

                        currentFilter = this.getAttribute('data-filter') || 'all';
                        renderSpotlightResults();
                    });
                });

                // Keyboard Navigation in Results: ArrowDown, ArrowUp, Enter
                spotlightInput.addEventListener('keydown', function (e) {
                    const items = spotlightResults.querySelectorAll('.spotlight-result-item');
                    if (items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex + 1) % items.length;
                        updateSelectedItem(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                        updateSelectedItem(items);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (selectedIndex >= 0 && items[selectedIndex]) {
                            items[selectedIndex].click();
                        } else if (items[0]) {
                            items[0].click();
                        }
                    }
                });

                function updateSelectedItem(items) {
                    items.forEach((item, idx) => {
                        if (idx === selectedIndex) {
                            item.classList.add('selected');
                            item.scrollIntoView({ block: 'nearest' });
                        } else {
                            item.classList.remove('selected');
                        }
                    });
                }

                function fetchSpotlightResults(query) {
                    fetch(`{{ route('universal.search') }}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (spotlightSpinner) spotlightSpinner.classList.add('d-none');
                        spotlightData = data.results;
                        selectedIndex = -1;
                        renderSpotlightResults();
                    })
                    .catch(() => {
                        if (spotlightSpinner) spotlightSpinner.classList.add('d-none');
                    });
                }

                function renderSpotlightResults() {
                    if (!spotlightData) return;

                    const categories = [
                        { key: 'patients', label: 'Bệnh Nhân & Hồ Sơ', icon: 'bi-person-vcard text-primary' },
                        { key: 'doctors', label: 'Bác Sĩ & Nhân Sự', icon: 'bi-person-badge text-success' },
                        { key: 'medications', label: 'Thuốc & Dược Phẩm', icon: 'bi-capsule-pill text-warning' },
                        { key: 'invoices', label: 'Hóa Đơn & Viện Phí', icon: 'bi-receipt text-info' },
                        { key: 'beds', label: 'Buồng Bệnh & Giường Nội Trú', icon: 'bi-hospital text-danger' },
                        { key: 'labs', label: 'Cận Lâm Sàng & Xét Nghiệm', icon: 'bi-moisture text-primary' },
                        { key: 'appointments', label: 'Lịch Hẹn & Tiếp Nhận', icon: 'bi-calendar-check text-info' },
                        { key: 'specialties', label: 'Chuyên Khoa & Khoa Phòng', icon: 'bi-diagram-3 text-secondary' },
                        { key: 'navigation', label: 'Chức Năng Hệ Thống Toàn Viện', icon: 'bi-compass text-primary' },
                    ];

                    let html = '';
                    let totalMatches = 0;

                    categories.forEach(cat => {
                        if (currentFilter !== 'all' && currentFilter !== cat.key) return;

                        const items = spotlightData[cat.key] || [];
                        if (items.length > 0) {
                            totalMatches += items.length;
                            html += `<div class="spotlight-category-header d-flex justify-content-between align-items-center">
                                <span><i class="${cat.icon} me-1.5"></i>${cat.label}</span>
                                <span class="badge bg-secondary bg-opacity-25 text-dark" style="font-size: 0.65rem;">${items.length}</span>
                            </div>`;

                            items.forEach(item => {
                                html += `<a href="${item.url}" class="spotlight-result-item">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light border text-primary" style="width: 36px; height: 36px; flex-shrink: 0;">
                                        <i class="bi ${item.icon || 'bi-circle'} fs-6"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="fw-semibold text-truncate" style="font-size: 0.88rem;">${item.title}</span>
                                            ${item.badge ? `<span class="badge ${item.badge_class || 'bg-light text-dark border'} rounded-pill" style="font-size: 0.65rem;">${item.badge}</span>` : ''}
                                        </div>
                                        <div class="text-muted small text-truncate" style="font-size: 0.75rem;">${item.subtitle || ''}</div>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small"></i>
                                </a>`;
                            });
                        }
                    });

                    if (totalMatches === 0) {
                        const q = spotlightInput.value.trim();
                        html = `<div class="p-4 text-center text-muted">
                            <i class="bi bi-emoji-neutral fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold">Không tìm thấy kết quả phù hợp${q ? ` cho "${q}"` : ''}</div>
                            <small class="text-secondary">Hãy thử tìm theo từ khóa khác hoặc lọc theo danh mục</small>
                        </div>`;
                    }

                    spotlightResults.innerHTML = html;
                }
            }
        });

        window.copyWithFeedback = function(text, btnEl, successMessage = 'Đã sao chép thành công!') {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }

            if (btnEl) {
                const originalHtml = btnEl.getAttribute('data-orig-html') || btnEl.innerHTML;
                btnEl.setAttribute('data-orig-html', originalHtml);
                btnEl.innerHTML = '<i class="bi bi-check2-all"></i> Đã chép';
                btnEl.classList.add('btn-success', 'text-white');
                setTimeout(() => {
                    btnEl.innerHTML = originalHtml;
                    btnEl.classList.remove('btn-success', 'text-white');
                }, 2000);
            }

            const toastEl = document.getElementById('globalCopyToast');
            const msgEl = document.getElementById('globalCopyToastMsg');
            if (toastEl && msgEl && window.bootstrap) {
                msgEl.textContent = successMessage;
                const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2500 });
                toast.show();
            }
        };
    </script>

    <!-- Global Copy Toast Feedback Container -->
    <div class="position-fixed bottom-0 end-0 p-3 no-print" style="z-index: 1080;">
        <div id="globalCopyToast" class="toast align-items-center text-bg-dark border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span id="globalCopyToastMsg">Đã sao chép vào bộ nhớ tạm!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
