@extends('layouts.admin')

@section('title', 'Thời Gian Biểu Uống Thuốc & Lịch Nhắc - ' . $record->patientProfile->full_name)

@section('content')
<style>
    @media print {
        .no-print, .main-header, .sidebar, footer, .btn, .breadcrumb, nav {
            display: none !important;
        }
        body {
            background-color: #fff !important;
            font-size: 12px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .container, .container-fluid {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .print-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 10px !important;
        }
        .card {
            border: 1px solid #ccc !important;
            break-inside: avoid;
        }
    }
</style>

<div class="container-fluid py-4 print-sheet">
    <!-- Header Controls -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('reception.pharmacy.index') }}">Quầy Dược Ngoại Trú</a></li>
                    <li class="breadcrumb-item active">Thời Gian Biểu Dùng Thuốc</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-week text-primary"></i>
                Thời Gian Biểu Uống Thuốc & Lịch Nhắc Điện Tử (Smart Schedule)
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('reception.pharmacy.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại Quầy Dược
            </a>
            <a href="{{ route('reception.pharmacy.label', $record) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-tag me-1"></i> In Nhãn Túi Thuốc
            </a>
            <a href="{{ route('reception.pharmacy.schedule.ical', $record) }}" class="btn btn-success btn-sm shadow-sm">
                <i class="bi bi-calendar-plus me-1"></i> Đồng Bộ iCal Vào Điện Thoại (.ics)
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
                <i class="bi bi-printer me-1"></i> In Lịch Hướng Dẫn Uống Thuốc
            </button>
        </div>
    </div>

    <!-- Hospital Brand Banner -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 bg-primary text-white rounded">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white text-primary p-3 rounded-circle shadow-sm">
                            <i class="bi bi-hospital fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">BỆNH VIỆN ĐA KHOA TÂM ANH</h4>
                            <div class="opacity-75 small">KHOA DƯỢC &bull; TRUNG TÂM TƯ VẤN SỬ DỤNG THUỐC NGOẠI TRÚ AN TOÀN</div>
                            <div class="mt-2 badge bg-white text-primary font-monospace fs-6 px-3 py-1">
                                Mã Hồ Sơ: {{ $record->record_code }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-white bg-opacity-10 p-3 rounded border border-white border-opacity-25 text-start text-md-end">
                        <div class="small opacity-75">Bệnh nhân:</div>
                        <h5 class="fw-bold mb-0 text-white">{{ $record->patientProfile->full_name }}</h5>
                        <div class="small mt-1 opacity-75">
                            Mã BN: <strong>{{ $record->patientProfile->patient_code }}</strong> | {{ $record->patientProfile->gender_name }} | {{ $record->patientProfile->dob ? $record->patientProfile->dob->age . ' tuổi' : '' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Visual 4-Period Schedule -->
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark text-uppercase mb-0">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Khung Giờ Uống Thuốc Trong Ngày
                </h6>
                <span class="badge bg-light text-dark border">
                    Tổng cộng: {{ $schedule['total_medications'] }} loại thuốc &bull; Phác đồ {{ $schedule['max_duration_days'] }} ngày
                </span>
            </div>

            <div class="d-flex flex-column gap-3">
                @foreach($schedule['sessions'] as $session)
                    <div class="card border-0 shadow-sm overflow-hidden {{ empty($session['items']) ? 'opacity-50' : '' }}">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $session['bg_color'] }} text-{{ $session['color'] }} p-2 fs-6 rounded">
                                    <i class="bi {{ $session['icon'] }}"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $session['name'] }}</h6>
                                    <small class="text-muted">{{ $session['time'] }} &bull; {{ $session['description'] }}</small>
                                </div>
                            </div>
                            <span class="badge {{ empty($session['items']) ? 'bg-secondary' : 'bg-' . $session['color'] }} rounded-pill px-3 py-1">
                                {{ count($session['items']) }} loại thuốc
                            </span>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($session['items']))
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light small">
                                            <tr>
                                                <th class="ps-4" style="width: 40px;">#</th>
                                                <th>Tên Thuốc & Hàm Lượng</th>
                                                <th style="width: 140px;">Liều Dùng</th>
                                                <th style="width: 90px;" class="text-center">Số Ngày</th>
                                                <th>Hướng Dẫn Chi Tiết</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($session['items'] as $idx => $item)
                                                <tr>
                                                    <td class="ps-4 text-muted fw-bold">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <strong class="text-dark d-block fs-6">{{ $item['medication_name'] }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-primary border font-monospace fs-6 px-2 py-1">
                                                            {{ $item['dosage'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center font-monospace fw-bold">
                                                        {{ $item['duration_days'] }} ngày
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-1 px-2">
                                                            <i class="bi bi-info-circle me-1"></i>{{ $item['instructions'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-3 text-center text-muted small fst-italic">
                                    Không có thuốc cần uống trong khung giờ này.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Important Patient Notes -->
            <div class="card border-0 shadow-sm mt-4 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-danger d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-exclamation"></i>
                        HƯỚNG DẪN AN TOÀN SỬ DỤNG THUỐC TỪ DƯỢC SĨ BV TÂM ANH
                    </h6>
                    <ul class="text-muted small mb-0 ps-3">
                        <li class="mb-1"><strong>Tuân thủ giờ giấc:</strong> Cố định giờ uống thuốc mỗi ngày để duy trì nồng độ thuốc ổn định trong máu.</li>
                        <li class="mb-1"><strong>Nước uống:</strong> Nên uống thuốc với 1 cốc nước lọc đầy (khoảng 150 - 200ml) ở nhiệt độ phòng. Tránh uống chung với nước ngọt có ga, sữa hoặc nước bưởi chùm.</li>
                        <li class="mb-1"><strong>Quên liều:</strong> Uống ngay khi nhớ ra. Nếu gần đến giờ của liều tiếp theo, bỏ qua liều đã quên và uống liều kế tiếp theo lịch. <strong>Tuyệt đối không uống gấp đôi liều</strong>.</li>
                        <li><strong>Dấu hiệu bất thường:</strong> Nếu xuất hiện phát ban đỏ, sưng mắt môi hoặc khó thở, lập tức ngừng thuốc và gọi ngay đường dây nóng cấp cứu <strong>1800 6858</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column: Follow-up & Smart iCal Sync -->
        <div class="col-lg-4">
            <!-- Smart Follow-up Recall Box -->
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-event text-warning fs-5"></i>
                        Hẹn Tái Khám Tự Động
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 rounded bg-warning-subtle border border-warning-subtle text-center mb-3">
                        <div class="small text-muted text-uppercase fw-semibold">Ngày Tái Khám Khuyến Nghị</div>
                        <div class="fs-3 fw-bold text-dark font-monospace mt-1">
                            {{ $followUp['formatted'] }}
                        </div>
                        <div class="badge bg-warning text-dark mt-1">
                            {{ $followUp['day_of_week'] }} &bull; Còn {{ $followUp['days_left'] }} ngày nữa
                        </div>
                    </div>

                    <p class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        {{ $followUp['explanation'] }}
                    </p>

                    <div class="no-print d-grid gap-2">
                        <a href="{{ route('reception.appointments.walkin', [
                            'patient_id' => $record->patient_profile_id,
                            'appointment_date' => $followUp['date']->format('Y-m-d')
                        ]) }}" class="btn btn-warning text-dark btn-sm fw-bold shadow-sm">
                            <i class="bi bi-calendar-plus me-1"></i> Đặt Trước Lịch Tái Khám Cho BN
                        </a>
                    </div>
                </div>
            </div>

            <!-- Smart Smartphone Sync (iCalendar .ics) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-phone text-success fs-5"></i>
                        Đồng Bộ Vào Điện Thoại Thông Minh
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="small text-muted mb-3">
                        Bệnh nhân quét mã QR hoặc tải tệp <strong>.ics</strong> để tự động đặt lịch nhắc nhở kèm chuông báo trên Apple Calendar (iPhone) hoặc Google Calendar (Android).
                    </p>

                    <!-- QR Code to Download iCal -->
                    <div class="d-inline-block p-2 bg-white border rounded shadow-sm mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode(route('reception.pharmacy.schedule.ical', $record)) }}" 
                             alt="QR Code iCal Sync" class="img-fluid" style="width: 150px; height: 150px;">
                    </div>
                    <div class="small text-muted font-monospace mb-3">
                        Quét bằng Camera điện thoại
                    </div>

                    <div class="no-print">
                        <a href="{{ route('reception.pharmacy.schedule.ical', $record) }}" class="btn btn-outline-success btn-sm w-100 fw-semibold mb-2">
                            <i class="bi bi-download me-1"></i> Tải Tệp Lịch (.ics) Trực Tiếp
                        </a>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            Tương thích 100% với iOS, iPadOS, Android, MacOS & Windows
                        </small>
                    </div>
                </div>
            </div>

            <!-- Prescribing Doctor Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded-circle text-primary">
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Bác sĩ kê đơn:</div>
                            <strong class="text-dark d-block">{{ $record->doctor ? $record->doctor->full_name_with_title : 'Khoa Khám Bệnh' }}</strong>
                            <div class="small text-muted">Chẩn đoán: <span class="text-danger fw-semibold">{{ $record->diagnosis }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
