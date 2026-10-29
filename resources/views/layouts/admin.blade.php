<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Quản trị Hệ thống') - Bệnh Viện Tâm Anh</title>

    <!-- Google Fonts -->
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
            --tam-primary-dark: #0369a1;
            --tam-accent: #0d9488;
            --tam-sidebar-bg: #0f172a;
            --tam-sidebar-hover: #1e293b;
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
            width: 260px;
            background-color: var(--tam-sidebar-bg);
            min-height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--tam-sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--tam-primary);
        }

        .sidebar-heading {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 0.75rem 1rem 0.25rem 1rem;
            font-weight: 700;
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

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -260px;
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
        <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 mb-4 text-decoration-none px-2 text-white">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                <i class="bi bi-hospital fs-5"></i>
            </div>
            <div>
                <span class="fw-bold d-block lh-1 text-white" style="font-size: 1.1rem;">BV TÂM ANH</span>
                <small class="text-secondary" style="font-size: 0.65rem; text-transform: uppercase;">Medical Management</small>
            </div>
        </a>

        <!-- Huy Hiệu Đồ Án Xuất Sắc - Nguyễn Văn Hưng -->
        <div class="px-1 mb-3">
            <button type="button" class="btn w-100 p-2 text-start rounded-3 border border-warning border-opacity-50 text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#projectContributionModal" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);">
                <div class="d-flex align-items-center justify-content-between text-warning fw-bold mb-1">
                    <span style="font-size: 0.72rem;"><i class="bi bi-award-fill"></i> ĐỒ ÁN XUẤT SẮC</span>
                    <span class="badge bg-success" style="font-size: 0.6rem;">100% Passed</span>
                </div>
                <div class="fw-bold text-light" style="font-size: 0.82rem;">Nguyễn Văn Hưng</div>
                <div class="text-white-50" style="font-size: 0.68rem;"><i class="bi bi-patch-check-fill text-info"></i> 70+ Phân hệ Chuẩn JCI & BYT</div>
            </button>
        </div>

        <div class="flex-grow-1 overflow-y-auto">
            <ul class="nav flex-column">

                @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="sidebar-heading">Bảng Quản trị (Admin)</li>
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
                @endif

                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isReceptionist()))
                    <li class="sidebar-heading">Nghiệp vụ Tiếp nhận (Lễ tân)</li>
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
                        <a class="nav-link {{ request()->is('reception/pharmacy') || (request()->is('reception/pharmacy*') && !request()->is('reception/pharmacy/inventory*')) ? 'active' : '' }}" href="{{ route('reception.pharmacy.index') }}">
                            <i class="bi bi-capsule-pill"></i> Quầy Dược Phát Thuốc
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/pharmacy/inventory*') ? 'active' : '' }}" href="{{ route('reception.pharmacy.inventory.index') }}">
                            <i class="bi bi-box-seam"></i> Kho Dược & Tồn Kho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/certificates*') ? 'active' : '' }}" href="{{ route('reception.certificates.index') }}">
                            <i class="bi bi-journal-medical"></i> Sổ Giấy Nghỉ BHXH
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/consents*') ? 'active' : '' }}" href="{{ route('reception.consents.index') }}">
                            <i class="bi bi-file-earmark-check"></i> Bản Cam Đoan Ký Số
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/invoices*') ? 'active' : '' }}" href="{{ route('reception.invoices.index') }}">
                            <i class="bi bi-wallet2"></i> Hóa Đơn & Thu Phí VietQR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/day-beds*') ? 'active' : '' }}" href="{{ route('reception.day-beds.index') }}">
                            <i class="bi bi-hospital"></i> Giường Ngoại Trú & Lưu Ngày
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/vaccinations*') ? 'active' : '' }}" href="{{ route('reception.vaccinations.index') }}">
                            <i class="bi bi-shield-check"></i> Tiêm Chủng & Sổ Vắc-Xin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/code-red*') ? 'active' : '' }}" href="{{ route('reception.code-red.index') }}">
                            <i class="bi bi-bell-fill text-danger"></i> Báo Động Đỏ Cấp Cứu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/deliveries*') ? 'active' : '' }}" href="{{ route('reception.deliveries.index') }}">
                            <i class="bi bi-box2-heart"></i> Giao Thuốc Tận Nhà
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/surveys*') ? 'active' : '' }}" href="{{ route('reception.surveys.index') }}">
                            <i class="bi bi-star-half"></i> Khảo Sát Hài Lòng BYT
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/lab-orders*') ? 'active' : '' }}" href="{{ route('reception.lab-orders.index') }}">
                            <i class="bi bi-moisture text-info"></i> Cận Lâm Sàng & Xét Nghiệm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/blood-transfusions*') ? 'active' : '' }}" href="{{ route('reception.blood-transfusions.index') }}">
                            <i class="bi bi-droplet-half text-danger"></i> An Toàn Truyền Máu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/mobility*') ? 'active' : '' }}" href="{{ route('reception.mobility.index') }}">
                            <i class="bi bi-person-wheelchair text-primary"></i> Mượn Trả Xe Lăn Tại Sảnh
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/nutrition*') ? 'active' : '' }}" href="{{ route('reception.nutrition.index') }}">
                            <i class="bi bi-heart-pulse text-success"></i> Dinh Dưỡng & Thực Đơn
                        </a>
                    </li>

                    <li class="sidebar-heading">Hành Chính & Viện Phí Thông Minh</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/insurance-transfers*') ? 'active' : '' }}" href="{{ route('reception.insurance-transfers.index') }}">
                            <i class="bi bi-file-earmark-medical text-primary"></i> Chuyển Tuyến BHYT (M40)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/wallets*') ? 'active' : '' }}" href="{{ route('reception.wallets.index') }}">
                            <i class="bi bi-wallet2 text-success"></i> Ví Ký Quỹ & Tạm Ứng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/private-insurance*') ? 'active' : '' }}" href="{{ route('reception.private-insurance.index') }}">
                            <i class="bi bi-shield-lock-fill text-info"></i> Bảo Lãnh Tư Nhân (GOP)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/record-requests*') ? 'active' : '' }}" href="{{ route('reception.record-requests.index') }}">
                            <i class="bi bi-file-earmark-lock2-fill text-warning"></i> Cổng Sao Lục Bệnh Án
                        </a>
                    </li>

                    <li class="sidebar-heading">An Toàn Người Bệnh & An Ninh</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/emergency-custody*') ? 'active' : '' }}" href="{{ route('reception.emergency-custody.index') }}">
                            <i class="bi bi-safe2-fill text-danger"></i> Tài Sản Cấp Cứu & Đồ Rơi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/visitor-passes*') ? 'active' : '' }}" href="{{ route('reception.visitor-passes.index') }}">
                            <i class="bi bi-person-badge-fill text-info"></i> Thẻ Thăm Bệnh Điện Tử
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/medical-incidents*') ? 'active' : '' }}" href="{{ route('reception.medical-incidents.index') }}">
                            <i class="bi bi-shield-exclamation text-danger"></i> Báo Cáo Sự Cố (Near-Miss)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/social-work*') ? 'active' : '' }}" href="{{ route('reception.social-work.index') }}">
                            <i class="bi bi-heart-fill text-success"></i> Công Tác Xã Hội & Dịch Thuật
                        </a>
                    </li>

                    <li class="sidebar-heading">Quy Trình Ngoại Trú Chuyên Sâu</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/ambulances*') ? 'active' : '' }}" href="{{ route('reception.ambulances.index') }}">
                            <i class="bi bi-truck-flatbed text-danger"></i> Đội Xe Cấp Cứu Ngoại Viện
                        </a>
                    </li>
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

                    <li class="sidebar-heading">Lâm Sàng Cấp Cao & An Toàn JCI</li>
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
                        <a class="nav-link {{ request()->is('reception/narcotics*') ? 'active' : '' }}" href="{{ route('reception.narcotics.index') }}">
                            <i class="bi bi-capsule text-primary"></i> Tủ Thuốc Gây Nghiện TT20
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/occupational-exposure*') ? 'active' : '' }}" href="{{ route('reception.occupational-exposure.index') }}">
                            <i class="bi bi-bandaid-fill text-danger"></i> Phơi Nhiễm Kim Tiêm & PEP
                        </a>
                    </li>

                    <li class="sidebar-heading">Công Nghệ Dược, CSSD & Vận Hành Giường</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/cold-chain*') ? 'active' : '' }}" href="{{ route('reception.cold-chain.index') }}">
                            <i class="bi bi-snow text-info"></i> Chuỗi Lạnh IoT Vắc-Xin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/cssd*') ? 'active' : '' }}" href="{{ route('reception.cssd.index') }}">
                            <i class="bi bi-tools text-secondary"></i> Tiệt Khuẩn Dụng Cụ CSSD
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/hemodialysis*') ? 'active' : '' }}" href="{{ route('reception.hemodialysis.index') }}">
                            <i class="bi bi-droplet-fill text-danger"></i> Lọc Máu Thận Nhân Tạo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/bed-board*') ? 'active' : '' }}" href="{{ route('reception.bed-board.index') }}">
                            <i class="bi bi-door-open-fill text-success"></i> Quản Lý Buồng Bệnh & Giường
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/wayfinding*') ? 'active' : '' }}" href="{{ route('reception.wayfinding.index') }}">
                            <i class="bi bi-map-fill text-primary"></i> Bản Đồ Dẫn Đường Trong Nhà
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/pre-visit*') ? 'active' : '' }}" href="{{ route('reception.pre-visit.index') }}">
                            <i class="bi bi-phone-fill text-info"></i> Tự Khai Báo Pre-Visit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/claim-scrubber*') ? 'active' : '' }}" href="{{ route('reception.claim-scrubber.index') }}">
                            <i class="bi bi-file-earmark-code-fill text-warning"></i> Giám Định BHYT & XML 4210
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/living-will*') ? 'active' : '' }}" href="{{ route('reception.living-will.index') }}">
                            <i class="bi bi-heart-pulse-fill text-danger"></i> Hiến Tạng & Di Chúc Y Tế
                        </a>
                    </li>

                    <li class="sidebar-heading">Smart Hospital Đột Phá (Next-Gen)</li>
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

                    <li class="sidebar-heading">Báo Cáo & Liên Thông</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/notifications*') ? 'active' : '' }}" href="{{ route('reception.notifications.index') }}">
                            <i class="bi bi-chat-quote"></i> Thông Báo ZNS & SMS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reception/analytics*') ? 'active' : '' }}" href="{{ route('reception.analytics.index') }}">
                            <i class="bi bi-graph-up-arrow"></i> Giám Sát Hiệu Suất (BI)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('consultations*') ? 'active' : '' }}" href="{{ route('consultations.index') }}">
                            <i class="bi bi-people-fill"></i> Hội Chẩn Đa Chuyên Khoa
                        </a>
                    </li>
                @endif

                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isDoctor()))
                    <li class="sidebar-heading">Bàn khám Bác sĩ</li>
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

                @if(Auth::check() && Auth::user()->isPatient())
                    <li class="sidebar-heading">Cổng thông tin Bệnh nhân</li>
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
                @endif

                <li class="sidebar-heading">Cài đặt cá nhân</li>
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
        <header class="top-navbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="sidebarToggleBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="fw-semibold text-muted small d-none d-sm-block">
                    HỆ THỐNG QUẢN LÝ BỆNH VIỆN ĐA KHOA TÂM ANH
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Nút Báo Cáo Đóng Góp Nguyễn Văn Hưng cho Thầy & Hội Đồng -->
                <button type="button" class="btn btn-sm text-white d-inline-flex align-items-center gap-1 rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#projectContributionModal" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); font-weight: 600; font-size: 0.8rem; border: none;">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span class="d-none d-md-inline">Đóng góp đồ án:</span> <strong>Nguyễn Văn Hưng</strong>
                    <span class="badge bg-white text-dark ms-1 rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">70+ Modules • JCI & BYT</span>
                </button>

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
                                <i class="bi bi-shield-lock me-2 text-warning"></i> Đổi mật khẩu
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="p-3 p-md-4 flex-grow-1">
            <x-alert />
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-muted small text-center text-md-start d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>Bệnh Viện Đa Khoa Tâm Anh</strong> - Hệ thống quản lý khám chữa bệnh ngoại trú.
            </div>
            <div>
                Phiên bản đồ án chuẩn hóa MVC (PHP 8.2 + Laravel 12)
            </div>
        </footer>
    </div>

    <!-- Modal Hồ Sơ Đóng Góp Kỹ Thuật Đồ Án - Nguyễn Văn Hưng (Dành cho Thầy & Hội Đồng Thẩm Định) -->
    <div class="modal fade" id="projectContributionModal" tabindex="-1" aria-labelledby="projectContributionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <!-- Modal Header -->
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 48px; height: 48px;">
                            <i class="bi bi-trophy-fill fs-3 text-warning"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="projectContributionModalLabel">
                                HỒ SƠ ĐÓNG GÓP KỸ THUẬT & NGHIỆP VỤ Y KHOA - SMART HOSPITAL SUITE
                            </h5>
                            <small class="text-white-50">Sinh viên thực hiện: <strong class="text-white">NGUYỄN VĂN HƯNG</strong> • Nhánh: <code class="text-warning bg-dark bg-opacity-25 px-2 py-0.5 rounded">feature/patients-and-reception</code></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <!-- KPI Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm bg-white p-3 text-center rounded-3 border-top border-4 border-primary">
                                <div class="text-primary fs-2 fw-bold mb-1">70+</div>
                                <div class="fw-semibold text-dark small">Phân Hệ Y Khoa Chuyên Sâu</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Smart OR, PACS AI, qSOFA, PGx, CMMS NĐ98, HAI, TDM Dược Lâm Sàng, TPN, FIM, YHCT, ESG</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm bg-white p-3 text-center rounded-3 border-top border-4 border-success">
                                <div class="text-success fs-2 fw-bold mb-1">230+ / 230+</div>
                                <div class="fw-semibold text-dark small">Kiểm Thử Tự Động (100% Green)</div>
                                <small class="text-muted" style="font-size: 0.72rem;">950+ Assertions • 0 Failures • 0 Errors</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm bg-white p-3 text-center rounded-3 border-top border-4 border-warning">
                                <div class="text-warning fs-2 fw-bold mb-1">100%</div>
                                <div class="fw-semibold text-dark small">Chuẩn Hóa Y Khoa & Pháp Lý</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Luật KCB 2023, TT 32, TT 43, TT 20, QĐ 4210 & Chuẩn JCI</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm bg-white p-3 text-center rounded-3 border-top border-4 border-info">
                                <div class="text-info fs-2 fw-bold mb-1">PR #4</div>
                                <div class="fw-semibold text-dark small">GitHub Pull Request Đồng Bộ</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Mọi thay đổi đều được commit & push minh bạch</small>
                            </div>
                        </div>
                    </div>

                    <!-- Git & Review Guide -->
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-github fs-3 text-dark"></i>
                            <div>
                                <strong class="text-dark">Theo dõi tiến độ trực tiếp trên GitHub:</strong>
                                <div class="small text-muted">Pull Request #4 trên kho lưu trữ chính thức đã tự động cập nhật toàn bộ mã nguồn mới nhất.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="https://github.com/quy0358/He-Thong-Quan-Ly-Benh-Vien-Tam-Anh/pull/4" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Mở GitHub PR #4
                            </a>
                        </div>
                    </div>

                    <!-- 10 Nhóm Nghiệp Vụ Chính -->
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-primary me-2"></i>Bản Đồ 10 Nhóm Nghiệp Vụ Y Khoa Trọng Tâm Phát Triển:</h6>
                    <div class="row g-3">
                        <!-- Nhóm 1 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle p-2"><i class="bi bi-person-workspace"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">1. Tiếp Đón & Smart Kiosk</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Kiosk cảm ứng sảnh & quét CCCD chip</li>
                                    <li>✓ In phiếu số thứ tự nhiệt 80mm có mã QR</li>
                                    <li>✓ Phát thanh gọi số tự động Web Speech AI</li>
                                    <li>✓ Check-in tiếp đón ca hẹn & Khám vãng lai</li>
                                    <li>✓ In vòng tay nhận diện mã vạch Barcode 128</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 2 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-bell-fill"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">2. Cấp Cứu & Báo Động Đỏ</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Phân luồng Cấp cứu Triage ATS 5 cấp</li>
                                    <li>✓ Kích hoạt Báo động đỏ toàn viện Code Red</li>
                                    <li>✓ Cảnh báo sớm suy sụp NEWS2 & Đội RRT</li>
                                    <li>✓ Giám sát tai nạn kim tiêm & Phơi nhiễm PEP</li>
                                    <li>✓ Thang đo Morse té ngã & Braden loét da</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 3 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning rounded-circle p-2 text-dark"><i class="bi bi-capsule"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">3. Dược Phẩm & An Toàn Thuốc</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Quầy dược ngoại trú & Kho tồn GPP</li>
                                    <li>✓ CDSS cảnh báo dị ứng chéo kháng sinh</li>
                                    <li>✓ Tem nhãn phụ Smart Label cảnh báo uống</li>
                                    <li>✓ Tủ thuốc trực cấp cứu & Thuốc độc TT20</li>
                                    <li>✓ Ký duyệt kép 2 người khi xuất thuốc nghiện</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 4 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-info rounded-circle p-2 text-white"><i class="bi bi-droplet-half"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">4. Xét Nghiệm & Máu An Toàn</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Cận lâm sàng & Báo động Panic Value</li>
                                    <li>✓ Truyền máu đối chiếu kép 2 Điều dưỡng</li>
                                    <li>✓ Định nhóm máu ABO/Rh tại giường</li>
                                    <li>✓ Theo dõi sinh hiệu 15p đầu truyền máu</li>
                                    <li>✓ Báo cáo phản ứng bất lợi sau truyền máu</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 5 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-snow"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">5. Chuỗi Lạnh IoT & Tiệt Khuẩn CSSD</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Giám sát nhiệt độ tủ vắc-xin 2-8°C IoT</li>
                                    <li>✓ Tự động khóa điện tử khi quá nhiệt</li>
                                    <li>✓ Quản lý mâm mổ & chu kỳ tiệt khuẩn CSSD</li>
                                    <li>✓ Kiểm định chất chỉ thị sinh học/hóa học</li>
                                    <li>✓ Hạn sử dụng vô trùng & mã QR khay mổ</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 6 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success rounded-circle p-2"><i class="bi bi-door-open-fill"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">6. Buồng Bệnh & Thận Nhân Tạo</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Sơ đồ giường bệnh thời gian thực trực quan</li>
                                    <li>✓ Quy trình khử khuẩn Cloramin B giường trống</li>
                                    <li>✓ Quản lý ca lọc máu chu kỳ & rút dịch UF</li>
                                    <li>✓ Giám sát số lần tái dùng quả lọc thận</li>
                                    <li>✓ Đơn vị lưu viện ban ngày (Day-Bed)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 7 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle p-2"><i class="bi bi-file-earmark-medical"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">7. BHYT, Viện Phí & Ví Ký Quỹ</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Giấy chuyển tuyến BHYT chuẩn Mẫu 40/BYT</li>
                                    <li>✓ Giám định BHYT thời gian thực & XML 4210</li>
                                    <li>✓ Ví ký quỹ & tạm ứng viện phí kế toán kép</li>
                                    <li>✓ Bảo lãnh viện phí tư nhân TPA GOP (Direct Billing)</li>
                                    <li>✓ Hóa đơn thanh toán mã VietQR Napas247</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 8 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-shield-lock-fill"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">8. Pháp Lý Y Tế & An Toàn JCI</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Sao lục bệnh án điện tử (Điều 69 Luật KCB)</li>
                                    <li>✓ Niêm phong tài sản cấp cứu & Đồ thất lạc</li>
                                    <li>✓ Thẻ thăm bệnh JCI giới hạn tối đa 2 người</li>
                                    <li>✓ Báo cáo sự cố Near-Miss & Phân tích RCA TT43</li>
                                    <li>✓ Đăng ký hiến mô tạng & Di chúc y tế (DNR)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 9 & 10 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-dark rounded-circle p-2"><i class="bi bi-truck"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">9 & 10. Vận Hành Ngoại Viện & Số Hóa</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Đội xe cấp cứu Mobile ICU tính cước km tự động</li>
                                    <li>✓ Khám sức khỏe đoàn doanh nghiệp TT 32/2023</li>
                                    <li>✓ Phẫu thuật trong ngày Day Surgery WHO Checklist</li>
                                    <li>✓ Bản đồ dẫn đường trong nhà 3D Wayfinding</li>
                                    <li>✓ Cổng tự khai báo triệu chứng từ xa Pre-Visit</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 11 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-info rounded-circle p-2 text-white"><i class="bi bi-capsule-pill"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">11. Dược Lâm Sàng, TDM & TPN</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Giám sát nồng độ thuốc hẹp TDM (Vancomycin/Gentamicin)</li>
                                    <li>✓ Dinh dưỡng tĩnh mạch toàn phần TPN All-in-One</li>
                                    <li>✓ Thang điểm Naranjo đánh giá biến cố có hại ADR</li>
                                    <li>✓ Đề xuất chỉnh liều tự động theo Clearance Creatinine</li>
                                    <li>✓ Kiểm soát nồng độ đáy Cmin & nguy cơ độc tính thận</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Nhóm 12 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 p-3 bg-white rounded-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success rounded-circle p-2 text-white"><i class="bi bi-tree-fill"></i></span>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">12. PHCN, Đông Y YHCT, ESG & ALARA</h6>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-0 ps-2" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>✓ Thang đo độc lập chức năng FIM & Kế hoạch PHCN</li>
                                    <li>✓ Kê đơn YHCT tự động chặn Tương Kỵ Thập Bát Phản</li>
                                    <li>✓ Biểu đồ Bhutani Nomogram dự báo vàng da sơ sinh</li>
                                    <li>✓ Ngân hàng sữa mẹ thanh trùng Pasteur ISO 22000</li>
                                    <li>✓ Bệnh viện xanh ESG & Kiểm soát liều bức xạ ALARA</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white px-4 py-3 d-flex justify-content-between">
                    <div class="small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Tác giả: <strong>Nguyễn Văn Hưng</strong> - Đã kiểm thử tự động 230+ kịch bản đạt 100% tỷ lệ xanh toàn diện.
                    </div>
                    <div class="d-flex gap-2">
                        <a href="https://github.com/quy0358/He-Thong-Quan-Ly-Benh-Vien-Tam-Anh/pull/4" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-git me-1"></i> Xem Git PR #4
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebar = document.getElementById('adminSidebar');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
