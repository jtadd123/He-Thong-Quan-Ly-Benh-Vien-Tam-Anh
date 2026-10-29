@extends('layouts.admin')

@section('title', 'Bàn Tiếp Nhận & Check-in - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Bàn Tiếp Nhận & Check-in Ngoại Trú" subtitle="Quản lý và tiếp đón bệnh nhân đến viện theo lịch hẹn hoặc đăng ký trực tiếp tại quầy">
    <x-slot:actions>
        <a href="{{ route('reception.appointments.queue-display') }}" target="_blank" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
            <i class="bi bi-tv"></i> Bảng điện tử phòng chờ
        </a>
        <a href="{{ route('reception.appointments.export', request()->query()) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i> Xuất Excel (CSV)
        </a>
        <a href="{{ route('reception.appointments.walkin') }}" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-box-arrow-in-down-right"></i> Tiếp nhận tại quầy (Walk-in)
        </a>
        <a href="{{ route('reception.patients.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-vcard"></i> Quản lý bệnh nhân
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Statistics Row -->
@if(isset($stats))
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <a href="{{ route('reception.appointments.index', ['date' => $date]) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 border-start border-4 border-secondary h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase">Tổng ca hôm nay</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="{{ route('reception.appointments.index', ['date' => $date, 'status' => 'pending']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="text-warning small fw-bold text-uppercase">Chờ tiếp nhận</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="{{ route('reception.appointments.index', ['date' => $date, 'status' => 'confirmed']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="text-primary small fw-bold text-uppercase">Đã Check-in (Chờ khám)</div>
                    <div class="fs-4 fw-bold text-primary">{{ $stats['confirmed'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="{{ route('reception.appointments.index', ['date' => $date, 'status' => 'completed']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="text-success small fw-bold text-uppercase">Đã khám xong</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['completed'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="{{ route('reception.appointments.index', ['date' => $date, 'status' => 'cancelled']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                <div class="card-body p-3">
                    <div class="text-danger small fw-bold text-uppercase">Đã hủy</div>
                    <div class="fs-4 fw-bold text-danger">{{ $stats['cancelled'] }}</div>
                </div>
            </div>
        </a>
    </div>
</div>
@endif

<!-- Shift & Specialty Distribution Bar -->
@if(isset($shiftChartData) && ($shiftChartData['morning'] > 0 || $shiftChartData['afternoon'] > 0))
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body p-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 small">
            <span class="fw-bold text-secondary"><i class="bi bi-clock-history me-1"></i> Phân bổ theo ca:</span>
            <span class="badge bg-warning-subtle text-dark border"><i class="bi bi-sun me-1"></i> Ca Sáng: <strong>{{ $shiftChartData['morning'] }}</strong> ca</span>
            <span class="badge bg-info-subtle text-dark border"><i class="bi bi-moon me-1"></i> Ca Chiều: <strong>{{ $shiftChartData['afternoon'] }}</strong> ca</span>
        </div>
        @if(isset($specialtyChartData) && $specialtyChartData->isNotEmpty())
            <div class="d-flex align-items-center gap-1 flex-wrap small">
                <span class="fw-bold text-secondary me-1"><i class="bi bi-hospital me-1"></i> Chuyên khoa:</span>
                @foreach($specialtyChartData as $spItem)
                    <span class="badge bg-white text-dark border">{{ $spItem->name }}: <strong>{{ $spItem->appointments_count }}</strong></span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reception.appointments.index') }}" class="row g-2 align-items-center">
            <div class="col-md-2">
                <label class="small text-muted fw-bold d-block">Ngày tiếp nhận:</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small text-muted fw-bold d-block">Ca trực:</label>
                <select name="shift" class="form-select form-select-sm">
                    <option value="">-- Tất cả ca --</option>
                    <option value="morning" {{ ($shift ?? '') === 'morning' ? 'selected' : '' }}>Ca Sáng (07:30 - 11:30)</option>
                    <option value="afternoon" {{ ($shift ?? '') === 'afternoon' ? 'selected' : '' }}>Ca Chiều (13:30 - 17:00)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-muted fw-bold d-block">Trạng thái:</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Đã tiếp nhận (Chờ khám)</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Đã hoàn tất</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small text-muted fw-bold d-block">Chuyên khoa:</label>
                <select name="specialty_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả khoa --</option>
                    @if(isset($specialties))
                        @foreach($specialties as $sp)
                            <option value="{{ $sp->id }}" {{ ($specialtyId ?? '') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold d-block">Tra cứu nhanh / Quét QR:</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="receptionSearchInput" value="{{ $search }}" class="form-control border-start-0 border-end-0" placeholder="Mã hẹn, Mã BN, Họ tên hoặc SĐT...">
                    <button type="button" class="btn btn-outline-primary" onclick="openReceptionQrScanner()" title="Quét Barcode / QR Bằng Camera">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Áp dụng bộ lọc">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-secondary btn-sm" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table Card -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">STT</th>
                        <th style="width: 130px;">Mã hẹn</th>
                        <th>Bệnh nhân</th>
                        <th>Chuyên khoa</th>
                        <th>Bác sĩ phụ trách</th>
                        <th>Ca & Giờ hẹn</th>
                        <th>Triệu chứng / Ghi chú</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-3" style="width: 220px;">Thao tác Tiếp nhận</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $app)
                        <tr class="{{ $app->is_priority ? 'table-warning bg-opacity-25' : '' }}">
                            <td class="ps-3">
                                @if($app->is_priority)
                                    <span class="badge bg-danger text-white border border-danger font-monospace fs-6 shadow-sm" title="Bệnh nhân diện ưu tiên">
                                        <i class="bi bi-star-fill text-warning"></i> #{{ str_pad($app->queue_number, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-6">
                                        #{{ str_pad($app->queue_number, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @endif
                            </td>
                            <td class="fw-bold font-monospace text-primary">
                                {{ $app->appointment_code }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark d-flex align-items-center flex-wrap gap-1">
                                    <a href="{{ route('reception.patients.show', $app->patientProfile) }}" class="text-decoration-none text-dark">
                                        {{ $app->patientProfile->full_name }}
                                    </a>
                                    <!-- ESI Triage Badge -->
                                    {!! $app->esi_badge !!}
                                    @if($app->is_priority)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small py-0 px-1" title="Đối tượng khám ưu tiên">
                                            <i class="bi bi-star-fill text-warning"></i> ƯU TIÊN
                                        </span>
                                    @endif
                                    @if($app->patientProfile && $app->patientProfile->latestVital)
                                        @php $appMews = $app->patientProfile->latestVital->mews_classification; @endphp
                                        @if($appMews['is_emergency'])
                                            <span class="badge bg-danger text-white border border-danger small py-0 px-1 animate-pulse" title="{{ $appMews['action_text'] }}">
                                                <i class="bi bi-exclamation-octagon-fill"></i> MEWS: {{ $appMews['score'] }}đ (CẤP CỨU)
                                            </span>
                                        @elseif($appMews['score'] >= 3)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small py-0 px-1" title="{{ $appMews['action_text'] }}">
                                                MEWS: {{ $appMews['score'] }}đ
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    <span>Mã: <code>{{ $app->patientProfile->patient_code }}</code></span> &bull; 
                                    <span>SĐT: {{ $app->patientProfile->phone }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $app->specialty ? $app->specialty->name : '--' }}</span>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $app->doctor ? $app->doctor->full_name_with_title : '--' }}</div>
                                <small class="text-muted">Phòng {{ $app->doctor ? $app->doctor->room_number : '' }}</small>
                            </td>
                            <td>
                                <div>{{ $app->shift_name }}</div>
                                <small class="text-muted">{{ $app->appointment_time ? substr($app->appointment_time, 0, 5) : 'Trong ca' }}</small>
                            </td>
                            <td class="small" style="max-width: 220px;">
                                @if(str_contains($app->symptom_description ?? '', '[Sinh hiệu:'))
                                    @php
                                        preg_match('/\[Sinh hiệu: (.*?)\]/', $app->symptom_description, $vMatch);
                                        $vitalsText = $vMatch[1] ?? '';
                                        $cleanSymptom = trim(preg_replace('/\[Sinh hiệu: .*?\]/', '', preg_replace('/\[ƯU TIÊN\]/', '', $app->symptom_description)));
                                    @endphp
                                    @if($vitalsText)
                                        <div class="badge bg-info-subtle text-info-emphasis border small mb-1 text-wrap text-start d-block" title="{{ $vitalsText }}">
                                            <i class="bi bi-heart-pulse-fill text-danger me-1"></i> {{ $vitalsText }}
                                        </div>
                                    @endif
                                    <div class="text-truncate" title="{{ $cleanSymptom }}">
                                        {{ $cleanSymptom ?: 'Khám tổng quát' }}
                                    </div>
                                @else
                                    <div class="text-truncate" title="{{ $app->symptom_description }}">
                                        {{ $app->symptom_description }}
                                    </div>
                                @endif
                                @if($app->cancellation_reason)
                                    <small class="text-danger d-block text-truncate" title="{{ $app->cancellation_reason }}">
                                        <i class="bi bi-info-circle"></i> {{ $app->cancellation_reason }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <x-status-badge :status="$app->status" />
                                @if($app->status === 'confirmed')
                                    <small class="text-muted d-block mt-1 font-monospace" title="Thời gian chờ từ lúc tiếp nhận">
                                        <i class="bi bi-clock-history text-primary"></i> Chờ {{ $app->waiting_minutes }}p
                                    </small>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="Gọi loa phát thanh mời vào phòng khám"
                                        onclick="callPatientVoice('{{ addslashes($app->patientProfile->full_name) }}', '{{ $app->queue_number }}', '{{ addslashes($app->doctor ? ($app->doctor->room_number ?? '1') : '1') }}', '{{ addslashes($app->doctor ? $app->doctor->full_name_with_title : '') }}')">
                                        <i class="bi bi-megaphone-fill text-primary"></i>
                                    </button>
                                    <form action="{{ route('reception.notifications.alert', $app) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Gửi Zalo ZNS / SMS nhắc sắp đến lượt khám">
                                            <i class="bi bi-chat-dots-fill"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('public.journey.show', $app->appointment_code) }}" target="_blank" class="btn btn-outline-primary" title="Hành trình khám trực tuyến (Live Tracking)">
                                        <i class="bi bi-compass"></i>
                                    </a>
                                    <a href="{{ route('reception.appointments.print', $app) }}" target="_blank" class="btn btn-outline-dark" title="In phiếu tiếp nhận / khám bệnh">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <form action="{{ route('reception.invoices.create-from-appointment', $app) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Lập hóa đơn viện phí & Thu tiền VietQR">
                                            <i class="bi bi-wallet2"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('reception.appointments.receipt', $app) }}" target="_blank" class="btn btn-outline-secondary" title="Thu viện phí nhanh (Biên lai cũ)">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @if($app->status === 'pending')
                                        <form method="POST" action="{{ route('reception.appointments.checkin', $app) }}" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1" title="Check-in tiếp đón bệnh nhân">
                                                <i class="bi bi-check2-circle"></i> Check-in
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-warning" title="Điều phối / Đổi bác sĩ" data-bs-toggle="modal" data-bs-target="#reassignModal-{{ $app->id }}">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Hủy lịch" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $app->id }}">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge bg-primary text-white py-2 px-2 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-person-check-fill"></i> Đã Check-in
                                        </span>
                                        <button type="button" class="btn btn-outline-warning btn-sm" title="Điều phối lại bác sĩ" data-bs-toggle="modal" data-bs-target="#reassignModal-{{ $app->id }}">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                    @elseif($app->status === 'completed')
                                        <span class="badge bg-success text-white py-2 px-2">
                                            <i class="bi bi-clipboard-check"></i> Đã hoàn tất
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white py-2 px-2">
                                            <i class="bi bi-x-circle"></i> Đã hủy
                                        </span>
                                    @endif
                                </div>

                                <!-- Reassign Doctor Modal -->
                                @if(in_array($app->status, ['pending', 'confirmed']))
                                    <div class="modal fade" id="reassignModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form method="POST" action="{{ route('reception.appointments.reassign', $app) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-warning">
                                                            <i class="bi bi-arrow-left-right me-1"></i> Điều Phối Ca Khám: {{ $app->appointment_code }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            Bệnh nhân: <strong>{{ $app->patientProfile->full_name }}</strong> &bull; Hiện tại: <strong>{{ $app->doctor ? $app->doctor->full_name_with_title : 'Chưa gán' }}</strong>
                                                        </p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small">Bác sĩ phụ trách mới: <span class="text-danger">*</span></label>
                                                            <select name="doctor_id" class="form-select form-select-sm" required>
                                                                @if(isset($doctors))
                                                                    @foreach($doctors as $doc)
                                                                        <option value="{{ $doc->id }}" {{ $app->doctor_id == $doc->id ? 'selected' : '' }}>
                                                                            {{ $doc->full_name_with_title }} - {{ $doc->specialty ? $doc->specialty->name : '' }} ({{ $doc->room_number }})
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>

                                                        <div class="row g-2 mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small">Ngày khám: <span class="text-danger">*</span></label>
                                                                <input type="date" name="appointment_date" value="{{ $app->appointment_date ? $app->appointment_date->format('Y-m-d') : date('Y-m-d') }}" class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold small">Ca khám: <span class="text-danger">*</span></label>
                                                                <select name="shift" class="form-select form-select-sm" required>
                                                                    <option value="morning" {{ $app->shift === 'morning' ? 'selected' : '' }}>Ca Sáng (07:30 - 11:30)</option>
                                                                    <option value="afternoon" {{ $app->shift === 'afternoon' ? 'selected' : '' }}>Ca Chiều (13:30 - 17:00)</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-semibold small">Lý do điều phối / đổi bác sĩ:</label>
                                                            <input type="text" name="reassign_reason" class="form-control form-control-sm" placeholder="VD: Bác sĩ ban đầu bận ca mổ khẩn cấp, bệnh nhân xin đổi ca...">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-warning btn-sm">Xác nhận điều phối</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Cancel Modal -->
                                @if($app->status === 'pending')
                                    <div class="modal fade" id="cancelModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form method="POST" action="{{ route('reception.appointments.cancel', $app) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-danger">Hủy lịch hẹn: {{ $app->appointment_code }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-2">Bệnh nhân: <strong>{{ $app->patientProfile->full_name }}</strong></p>
                                                        <label class="form-label fw-semibold small">Lý do hủy lịch khám: <span class="text-danger">*</span></label>
                                                        <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Bệnh nhân báo hủy, không đến viện..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Xác nhận hủy ca</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Không có ca khám nào trong ngày hoặc phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($appointments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $appointments->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    /**
     * Synthesize standard 2-tone hospital chime using Web Audio API (No external mp3 required)
     */
    function playHospitalChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Tone 1: D5 (587.33 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
            gain1.gain.setValueAtTime(0.2, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.5);

            // Tone 2: A5 (880 Hz) after 200ms
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.22);
            gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.22);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.8);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.22);
            osc2.stop(ctx.currentTime + 0.8);
        } catch (e) {
            console.warn('Hospital chime unavailable', e);
        }
    }

    /**
     * Voice Call Patient using Web Speech API (Tiếng Việt)
     */
    function callPatientVoice(patientName, queueNumber, roomNumber, doctorName) {
        // Broadcast event to connected Waiting Room TV displays (queue-display)
        try {
            localStorage.setItem('tam_anh_queue_call', JSON.stringify({
                name: patientName,
                stt: queueNumber,
                room: roomNumber,
                doctor: doctorName,
                time: Date.now()
            }));
        } catch (e) {}

        // 1. Play dual-tone chime
        playHospitalChime();

        // 2. Announce via text-to-speech
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();

            setTimeout(() => {
                const speechText = `Kính mời bệnh nhân ${patientName}, số thứ tự ${queueNumber}, vào phòng khám ${roomNumber} gặp ${doctorName || 'bác sĩ'}.`;
                const utterance = new SpeechSynthesisUtterance(speechText);
                utterance.lang = 'vi-VN';
                utterance.rate = 0.92;
                utterance.pitch = 1.05;

                const voices = window.speechSynthesis.getVoices();
                const viVoice = voices.find(v => v.lang.includes('vi') || v.lang.includes('VN'));
                if (viVoice) {
                    utterance.voice = viVoice;
                }

                window.speechSynthesis.speak(utterance);
            }, 450);
        }

        // 3. Visual notification banner
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show align-items-center text-bg-primary border-0 shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-megaphone-fill me-2 fs-5"></i>
                        <strong>Đang phát loa:</strong> Mời STT <b>#${queueNumber}</b> (${patientName}) &rarr; Phòng <b>${roomNumber}</b>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    // Camera Scanner at Reception Desk
    let receptionScanner = null;
    function openReceptionQrScanner() {
        const modalEl = document.getElementById('receptionScannerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const feedEl = document.getElementById('receptionScannerFeedback');
        feedEl.textContent = 'Đang khởi động camera...';

        if (receptionScanner) {
            receptionScanner.clear();
        }

        receptionScanner = new Html5Qrcode("receptionCameraReader");
        receptionScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                feedEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check2-circle"></i> Đã tìm thấy: ${decodedText}</span>`;
                stopReceptionScanner();
                const inp = document.getElementById('receptionSearchInput');
                if (inp) {
                    inp.value = decodedText;
                    inp.closest('form').submit();
                }
            },
            () => {}
        ).catch(err => {
            feedEl.textContent = 'Không thể mở camera: ' + err;
        });
    }

    function stopReceptionScanner() {
        if (receptionScanner) {
            receptionScanner.stop().then(() => {
                receptionScanner.clear();
                receptionScanner = null;
            }).catch(() => {
                receptionScanner = null;
            });
        }
        const modalEl = document.getElementById('receptionScannerModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
</script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<!-- Reception QR Scanner Modal -->
<div class="modal fade" id="receptionScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-camera me-1"></i> Quét Mã Lịch Hẹn / Thẻ Bệnh Nhân</h6>
                <button type="button" class="btn-close btn-close-white" onclick="stopReceptionScanner()"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div id="receptionCameraReader" class="rounded overflow-hidden mx-auto" style="max-width: 380px; background: #000;"></div>
                <div id="receptionScannerFeedback" class="small text-muted mt-2">Hướng mã QR phiếu hẹn hoặc mã vạch thẻ BN vào camera.</div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" onclick="stopReceptionScanner()">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
