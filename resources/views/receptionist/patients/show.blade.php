@extends('layouts.admin')

@section('title', 'Hồ Sơ Bệnh Nhân: ' . $patient->full_name . ' - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="{{ $patient->full_name }}" subtitle="Mã hồ sơ: {{ $patient->patient_code }} | Quản lý thông tin định danh và lịch sử khám chữa bệnh">
    <x-slot:actions>
        <a href="{{ route('reception.patients.print-wristband', $patient) }}" target="_blank" class="btn btn-outline-danger d-inline-flex align-items-center gap-1" title="In vòng tay y tế đeo tay nhận diện người bệnh (Chuẩn JCI)">
            <i class="bi bi-tag"></i> In vòng tay JCI
        </a>
        <a href="{{ route('reception.patients.print-card', $patient) }}" target="_blank" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
            <i class="bi bi-credit-card-2-front"></i> In thẻ bệnh nhân
        </a>
        <a href="{{ route('reception.appointments.walkin', ['patient_id' => $patient->id]) }}" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-calendar-plus"></i> Đăng ký tiếp nhận tại quầy
        </a>
        <a href="{{ route('reception.patients.edit', $patient) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-pencil"></i> Chỉnh sửa
        </a>
        <a href="{{ route('reception.patients.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Danh sách
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <!-- Left Column: Patient Profile Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 1rem;">
            <div class="card-body p-4 text-center border-bottom bg-light bg-opacity-50">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px; font-size: 1.8rem; font-weight: 700;">
                    {{ mb_substr($patient->full_name, 0, 1) }}
                </div>
                <h5 class="fw-bold mb-1 text-dark">{{ $patient->full_name }}</h5>
                <div class="font-monospace text-primary fw-bold mb-2">
                    <code>{{ $patient->patient_code }}</code>
                </div>
                <div>
                    @if($patient->gender === 'male')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Nam</span>
                    @elseif($patient->gender === 'female')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Nữ</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Khác</span>
                    @endif
                    <span class="badge bg-light text-dark border px-2 py-1 ms-1">
                        {{ $patient->dob ? $patient->dob->age . ' tuổi' : 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                @if($patient->hasAllergies())
                    <div class="alert alert-danger border-danger shadow-sm mb-3 p-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                            <strong class="text-danger text-uppercase small">Cảnh Báo Dị Ứng Thuốc:</strong>
                        </div>
                        <div class="fw-bold text-dark fs-6">{{ $patient->allergies }}</div>
                        <small class="text-danger d-block mt-1">* Thận trọng khi kê đơn nhóm kháng sinh, giảm đau hoặc NSAID.</small>
                    </div>
                @endif

                @if($patient->hasMedicalHistory())
                    <div class="alert alert-warning border-warning shadow-sm mb-3 p-2 small">
                        <strong class="text-warning-emphasis d-block"><i class="bi bi-activity me-1"></i> Tiền sử bệnh nền / Phẫu thuật:</strong>
                        <span class="text-dark">{{ $patient->medical_history }}</span>
                    </div>
                @endif

                <h6 class="text-uppercase text-muted fw-bold small mb-3">Thông Tin Hành Chính</h6>
                
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> Ngày sinh:</span>
                        <span class="fw-semibold text-dark">{{ $patient->dob ? $patient->dob->format('d/m/Y') : '--' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i> Điện thoại:</span>
                        <span class="fw-semibold font-monospace text-dark">{{ $patient->phone }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i> Email:</span>
                        <span class="fw-medium text-dark text-truncate" style="max-width: 170px;">{{ $patient->email ?: '--' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-card-heading me-1"></i> Số CCCD:</span>
                        <span class="font-monospace fw-semibold text-dark">{{ $patient->identity_card ?: '--' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-shield-check me-1"></i> Mã BHYT:</span>
                        <span class="font-monospace fw-semibold text-dark">{{ $patient->insurance_code ?: '--' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="bi bi-droplet-half text-danger me-1"></i> Nhóm máu:</span>
                        <span class="fw-bold font-monospace {{ $patient->blood_group ? 'badge bg-danger text-white' : 'text-muted' }}">
                            {{ $patient->blood_group ?: 'Chưa xác định' }}
                        </span>
                    </li>
                    <li class="py-1 border-bottom">
                        <span class="text-muted d-block mb-1"><i class="bi bi-geo-alt me-1"></i> Địa chỉ thường trú:</span>
                        <span class="fw-medium text-dark">{{ $patient->address ?: 'Chưa cập nhật' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i> Ngày mở hồ sơ:</span>
                        <span class="text-muted">{{ $patient->created_at ? $patient->created_at->format('d/m/Y H:i') : '--' }}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-white border-top p-3 d-flex flex-column gap-2">
                <a href="{{ route('reception.appointments.walkin', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm w-100 shadow-sm">
                    <i class="bi bi-box-arrow-in-down-right me-1"></i> Tiếp nhận ca khám ngay
                </a>
                <a href="{{ route('reception.appointments.walkin', ['patient_id' => $patient->id, 'follow_up' => 1]) }}" class="btn btn-outline-warning btn-sm w-100 text-dark">
                    <i class="bi bi-calendar-plus me-1"></i> Lên lịch hẹn tái khám
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Medical & Appointment History -->
    <div class="col-lg-8">
        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mb-3 gap-2" id="patientTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4" id="appointments-tab" data-bs-toggle="pill" data-bs-target="#appointments" type="button" role="tab">
                    <i class="bi bi-calendar-week me-1"></i> Lịch Sử Đặt Hẹn & Tiếp Nhận ({{ $patient->appointments->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="records-tab" data-bs-toggle="pill" data-bs-target="#records" type="button" role="tab">
                    <i class="bi bi-file-earmark-medical me-1"></i> Hồ Sơ Bệnh Án & Đơn Thuốc ({{ $patient->medicalRecords->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="vitals-tab" data-bs-toggle="pill" data-bs-target="#vitals" type="button" role="tab">
                    <i class="bi bi-activity text-danger me-1"></i> Diễn Tiến Sinh Hiệu ({{ $patient->vitals->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="audit-tab" data-bs-toggle="pill" data-bs-target="#audit" type="button" role="tab">
                    <i class="bi bi-shield-check text-info me-1"></i> Nhật Ký Truy Vết ({{ $patient->auditLogs->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="consents-tab" data-bs-toggle="pill" data-bs-target="#consents" type="button" role="tab">
                    <i class="bi bi-file-earmark-check text-success me-1"></i> Cam Đoan Thủ Thuật ({{ $patient->informedConsents->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="patientTabsContent">
            <!-- TAB 1: Appointments -->
            <div class="tab-pane fade show active" id="appointments" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="card-title fw-bold text-dark mb-0">Danh Sách Ca Khám Của Bệnh Nhân</h6>
                        <span class="small text-muted">Tổng cộng: {{ $patient->appointments->count() }} lượt</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Mã lịch hẹn</th>
                                        <th>Ngày khám & Ca</th>
                                        <th>Chuyên khoa</th>
                                        <th>Bác sĩ phụ trách</th>
                                        <th>Triệu chứng ban đầu</th>
                                        <th class="text-end pe-3">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($patient->appointments as $app)
                                        <tr>
                                            <td class="ps-3 fw-bold font-monospace text-primary">
                                                {{ $app->appointment_code }}
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $app->appointment_date ? $app->appointment_date->format('d/m/Y') : '--' }}</div>
                                                <small class="text-muted">{{ $app->shift_name }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $app->specialty ? $app->specialty->name : '--' }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $app->doctor ? $app->doctor->full_name_with_title : '--' }}</div>
                                                <small class="text-muted">{{ $app->doctor ? $app->doctor->room_number : '' }}</small>
                                            </td>
                                            <td class="small text-muted" style="max-width: 200px;">
                                                <span class="text-truncate d-inline-block" style="max-width: 190px;" title="{{ $app->symptom_description }}">
                                                    {{ $app->symptom_description }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <x-status-badge :status="$app->status" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                                Chưa có lịch hẹn khám nào được ghi nhận cho bệnh nhân này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Medical Records & Prescriptions -->
            <div class="tab-pane fade" id="records" role="tabpanel">
                @forelse($patient->medicalRecords as $record)
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace me-2">{{ $record->record_code }}</span>
                                <strong class="text-dark">Khám ngày: {{ $record->created_at ? $record->created_at->format('d/m/Y H:i') : '--' }}</strong>
                            </div>
                            <div class="small text-muted">
                                Bác sĩ điều trị: <strong>{{ $record->doctor ? $record->doctor->full_name_with_title : '--' }}</strong>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded border">
                                        <div class="small text-muted fw-bold mb-1"><i class="bi bi-activity text-danger me-1"></i> Triệu chứng ghi nhận:</div>
                                        <div class="text-dark">{{ $record->symptoms }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded border">
                                        <div class="small text-muted fw-bold mb-1"><i class="bi bi-clipboard2-pulse text-primary me-1"></i> Chẩn đoán kết luận:</div>
                                        <div class="fw-bold text-primary">{{ $record->diagnosis }}</div>
                                    </div>
                                </div>
                            </div>

                            @if($record->clinical_notes || $record->treatment_plan)
                                <div class="row g-3 mb-3 small">
                                    @if($record->clinical_notes)
                                        <div class="col-md-6">
                                            <strong class="text-muted d-block mb-1">Chỉ số sinh tồn / Ghi chú:</strong>
                                            <p class="mb-0 text-secondary">{{ $record->clinical_notes }}</p>
                                        </div>
                                    @endif
                                    @if($record->treatment_plan)
                                        <div class="col-md-6">
                                            <strong class="text-muted d-block mb-1">Phác đồ điều trị:</strong>
                                            <p class="mb-0 text-secondary">{{ $record->treatment_plan }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($record->follow_up_date)
                                <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Hẹn tái khám vào ngày: <strong>{{ \Carbon\Carbon::parse($record->follow_up_date)->format('d/m/Y') }}</strong></span>
                                </div>
                            @endif

                            <!-- Prescriptions -->
                            @if($record->prescriptions && $record->prescriptions->count() > 0)
                                <h6 class="fw-bold text-dark small text-uppercase mb-2">
                                    <i class="bi bi-capsule text-success me-1"></i> Đơn thuốc điều trị ({{ $record->prescriptions->count() }} loại thuốc)
                                </h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>#</th>
                                                <th>Tên thuốc</th>
                                                <th>Liều dùng</th>
                                                <th>Tần suất</th>
                                                <th>Số ngày</th>
                                                <th>Hướng dẫn</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($record->prescriptions as $idx => $pres)
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td class="fw-semibold text-primary">{{ $pres->medication_name }}</td>
                                                    <td>{{ $pres->dosage }}</td>
                                                    <td>{{ $pres->frequency }}</td>
                                                    <td>{{ $pres->duration_days }} ngày</td>
                                                    <td class="text-muted">{{ $pres->instructions ?: '--' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Attachments -->
                            @if($record->attachments && $record->attachments->count() > 0)
                                <h6 class="fw-bold text-dark small text-uppercase mb-2">
                                    <i class="bi bi-paperclip text-info me-1"></i> Tệp kết quả cận lâm sàng / X-quang
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($record->attachments as $att)
                                        <span class="badge bg-light text-dark border p-2 d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                                            <span>{{ $att->original_name }}</span>
                                            <small class="text-muted">({{ number_format($att->file_size / 1024, 1) }} KB)</small>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-5 text-center text-muted">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Bệnh nhân chưa có bệnh án điều trị nào hoàn tất tại viện.
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- TAB 3: Longitudinal EHR Vitals Tracker -->
            <div class="tab-pane fade" id="vitals" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="card-title fw-bold text-dark mb-0">
                                <i class="bi bi-activity text-danger me-1"></i> Theo Dõi Sinh Hiệu Dài Hạn (EHR Vitals Tracker)
                            </h6>
                            <small class="text-muted">Biểu đồ giám sát huyết áp, nhịp tim và các chỉ số sinh tồn qua từng lần thăm khám</small>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRecordVital">
                            <i class="bi bi-plus-circle"></i> Đo Sinh Hiệu Mới
                        </button>
                    </div>

                    @if($patient->vitals->isNotEmpty() && $latestVital)
                        <div class="card-body border-bottom bg-light bg-opacity-50 p-3">
                            <div class="small fw-bold text-muted text-uppercase mb-2">
                                <i class="bi bi-clock-history me-1"></i> Chỉ số đo gần nhất ({{ $latestVital->recorded_at->format('H:i d/m/Y') }}):
                            </div>
                            <div class="row g-2">
                                <!-- Huyết áp -->
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <div class="text-muted small">Huyết Áp (HA)</div>
                                        <div class="fs-5 fw-bold font-monospace text-dark mt-1">
                                            @if($latestVital->systolic_bp && $latestVital->diastolic_bp)
                                                {{ $latestVital->systolic_bp }}/{{ $latestVital->diastolic_bp }} <small class="fs-6 text-muted fw-normal">mmHg</small>
                                            @else
                                                <span class="text-muted fs-6">--</span>
                                            @endif
                                        </div>
                                        @php $bpClass = $latestVital->bp_classification; @endphp
                                        <span class="badge bg-{{ $bpClass['color'] }}-subtle text-{{ $bpClass['color'] }} border border-{{ $bpClass['color'] }}-subtle small mt-1">
                                            {{ $bpClass['label'] }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Nhịp tim -->
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <div class="text-muted small">Mạch / Nhịp Tim</div>
                                        <div class="fs-5 fw-bold font-monospace text-danger mt-1">
                                            @if($latestVital->heart_rate)
                                                {{ $latestVital->heart_rate }} <small class="fs-6 text-muted fw-normal">lần/phút</small>
                                            @else
                                                <span class="text-muted fs-6">--</span>
                                            @endif
                                        </div>
                                        @if($latestVital->isTachycardia())
                                            <span class="badge bg-warning-subtle text-warning border small mt-1">Nhịp nhanh (&gt;100)</span>
                                        @elseif($latestVital->isBradycardia())
                                            <span class="badge bg-info-subtle text-info border small mt-1">Nhịp chậm (&lt;60)</span>
                                        @elseif($latestVital->heart_rate)
                                            <span class="badge bg-success-subtle text-success border small mt-1">Nhịp xoang đều</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Thân nhiệt -->
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <div class="text-muted small">Thân Nhiệt</div>
                                        <div class="fs-5 fw-bold font-monospace text-primary mt-1">
                                            @if($latestVital->temperature)
                                                {{ number_format($latestVital->temperature, 1) }} <small class="fs-6 text-muted fw-normal">°C</small>
                                            @else
                                                <span class="text-muted fs-6">--</span>
                                            @endif
                                        </div>
                                        @if($latestVital->isFever())
                                            <span class="badge bg-danger-subtle text-danger border small mt-1">Có sốt (≥37.5°C)</span>
                                        @elseif($latestVital->temperature)
                                            <span class="badge bg-success-subtle text-success border small mt-1">Bình thường</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- SpO2 -->
                                <div class="col-sm-6 col-md-3">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <div class="text-muted small">Nồng Độ SpO2</div>
                                        <div class="fs-5 fw-bold font-monospace text-success mt-1">
                                            @if($latestVital->spo2)
                                                {{ $latestVital->spo2 }} <small class="fs-6 text-muted fw-normal">%</small>
                                            @else
                                                <span class="text-muted fs-6">--</span>
                                            @endif
                                        </div>
                                        @if($latestVital->isHypoxia())
                                            <span class="badge bg-danger text-white small mt-1">Thiếu oxy (&lt;95%)</span>
                                        @elseif($latestVital->spo2)
                                            <span class="badge bg-success-subtle text-success border small mt-1">Đạt chuẩn</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php $mews = $latestVital->mews_classification; @endphp
                            <!-- MEWS Clinical Early Warning Banner -->
                            <div class="mt-3 p-3 rounded border d-flex align-items-center justify-content-between {{ $mews['is_emergency'] ? 'bg-danger-subtle border-danger text-danger' : ($mews['score'] >= 3 ? 'bg-warning-subtle border-warning text-dark' : 'bg-light border-secondary-subtle text-dark') }}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-3">
                                        @if($mews['is_emergency'])
                                            <i class="bi bi-exclamation-triangle-fill text-danger animate-pulse"></i>
                                        @elseif($mews['score'] >= 3)
                                            <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                        @else
                                            <i class="bi bi-shield-check text-success"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold d-flex align-items-center gap-2">
                                            <span>Thang Điểm Cảnh Báo Lâm Sàng Sớm (MEWS):</span>
                                            <span class="badge {{ $mews['badge_class'] }} fs-6 px-2 py-1">
                                                {{ $mews['score'] }} Điểm &bull; {{ $mews['level'] }}
                                            </span>
                                        </div>
                                        <div class="small mt-1 {{ $mews['is_emergency'] ? 'fw-bold text-danger' : 'text-muted' }}">
                                            <i class="bi bi-info-circle me-1"></i> {{ $mews['action_text'] }}
                                        </div>
                                    </div>
                                </div>
                                @if($mews['is_emergency'])
                                    <span class="badge bg-danger text-white p-2 text-uppercase fw-bold shadow-sm">
                                        <i class="bi bi-bell-fill me-1"></i> Báo Động Cấp Cứu
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Interactive Chart Container -->
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark small text-uppercase mb-3">
                                <i class="bi bi-graph-up me-1 text-primary"></i> Biểu đồ diễn tiến Huyết áp & Nhịp tim theo thời gian
                            </h6>
                            <div style="height: 280px; position: relative;">
                                <canvas id="vitalsChart"></canvas>
                            </div>
                        </div>

                        <!-- Historical Vitals Table -->
                        <div class="card-body p-0 border-top">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Thời gian đo</th>
                                            <th>Huyết áp (mmHg)</th>
                                            <th>Mạch (l/p)</th>
                                            <th>Nhiệt độ</th>
                                            <th>SpO2</th>
                                            <th>Cảnh báo MEWS</th>
                                            <th>BMI / Thể trạng</th>
                                            <th>Người đo</th>
                                            <th>Ghi chú lâm sàng</th>
                                            <th class="text-end pe-3">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->vitals as $vital)
                                            @php $vMews = $vital->mews_classification; @endphp
                                            <tr class="{{ $vMews['is_emergency'] ? 'table-danger' : '' }}">
                                                <td class="ps-3 font-monospace fw-bold text-dark">
                                                    {{ $vital->recorded_at->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    @if($vital->systolic_bp && $vital->diastolic_bp)
                                                        <span class="font-monospace fw-bold">{{ $vital->systolic_bp }}/{{ $vital->diastolic_bp }}</span>
                                                        @php $vClass = $vital->bp_classification; @endphp
                                                        <span class="badge bg-{{ $vClass['color'] }}-subtle text-{{ $vClass['color'] }} ms-1">
                                                            {{ $vClass['label'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">--</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="font-monospace fw-bold {{ $vital->isTachycardia() ? 'text-danger' : '' }}">
                                                        {{ $vital->heart_rate ?: '--' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-monospace {{ $vital->isFever() ? 'text-danger fw-bold' : '' }}">
                                                        {{ $vital->temperature ? number_format($vital->temperature, 1) . '°C' : '--' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-monospace {{ $vital->isHypoxia() ? 'badge bg-danger text-white' : '' }}">
                                                        {{ $vital->spo2 ? $vital->spo2 . '%' : '--' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $vMews['badge_class'] }} font-monospace" title="{{ $vMews['action_text'] }}">
                                                        {{ $vMews['score'] }}đ &bull; {{ $vMews['level'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($vital->bmi)
                                                        <span class="font-monospace fw-bold">{{ $vital->bmi }}</span>
                                                        <small class="text-muted">({{ $vital->bmi_classification }})</small>
                                                    @elseif($vital->weight)
                                                        <span class="font-monospace">{{ $vital->weight }} kg</span>
                                                    @else
                                                        <span class="text-muted">--</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $vital->recordedBy ? $vital->recordedBy->name : 'Hệ thống tự động' }}
                                                </td>
                                                <td class="text-muted" style="max-width: 180px;">
                                                    {{ $vital->notes ?: '--' }}
                                                </td>
                                                <td class="text-end pe-3">
                                                    <form action="{{ route('reception.vitals.destroy', $vital) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lượt đo sinh hiệu này?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Xóa bản ghi">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="card-body p-5 text-center text-muted">
                            <i class="bi bi-heart-pulse fs-1 d-block mb-3 text-secondary opacity-50"></i>
                            <h6 class="fw-bold text-dark">Chưa có dữ liệu sinh hiệu cho bệnh nhân này</h6>
                            <p class="small text-muted mb-3">Thực hiện đo và nhập chỉ số sinh hiệu (huyết áp, nhịp tim, thân nhiệt, SpO2) tại quầy tiếp nhận hoặc phòng khám.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRecordVital">
                                <i class="bi bi-plus-circle me-1"></i> Đo Sinh Hiệu Mới Ngay
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 4: Clinical & Security Audit Trail -->
            <div class="tab-pane fade" id="audit" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="card-title fw-bold text-dark mb-0">
                                <i class="bi bi-shield-lock-fill text-info me-1"></i> Nhật Ký Truy Vết Y Khoa & Giám Sát An Toàn (Audit Trail)
                            </h6>
                            <small class="text-muted">Tuân thủ Thông tư 46/2018/TT-BYT & Tiêu chuẩn bảo mật hồ sơ bệnh án điện tử (EMR/EHR)</small>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace">
                            <i class="bi bi-fingerprint text-primary me-1"></i>{{ $patient->auditLogs->count() }} bản ghi truy vết
                        </span>
                    </div>

                    <div class="card-body p-0">
                        @if($patient->auditLogs->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 170px;">Thời Gian</th>
                                            <th style="width: 160px;">Nhân Sự Thao Tác</th>
                                            <th style="width: 180px;">Hành Vi / Thao Tác</th>
                                            <th>Mô Tả Chi Tiết</th>
                                            <th style="width: 130px;">Địa Chỉ IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->auditLogs as $log)
                                            @php $badge = $log->action_badge; @endphp
                                            <tr>
                                                <td class="ps-3 font-monospace text-dark">
                                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                                </td>
                                                <td>
                                                    @if($log->user)
                                                        <div class="fw-bold text-dark">{{ $log->user->name }}</div>
                                                        <span class="badge bg-secondary-subtle text-secondary border small">{{ $log->user->role_name }}</span>
                                                    @else
                                                        <span class="text-muted fst-italic">Hệ thống tự động</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $badge['color'] }}-subtle text-{{ $badge['color'] }} border border-{{ $badge['color'] }}-subtle px-2 py-1">
                                                        <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-dark">{{ $log->description }}</div>
                                                    @if($log->old_values && $log->new_values)
                                                        <div class="mt-1">
                                                            <a class="text-decoration-none small text-primary" data-bs-toggle="collapse" href="#diffLog{{ $log->id }}" role="button">
                                                                <i class="bi bi-code-slash me-1"></i>Xem dữ liệu thay đổi
                                                            </a>
                                                            <div class="collapse mt-2" id="diffLog{{ $log->id }}">
                                                                <div class="p-2 border rounded bg-light font-monospace small" style="font-size: 0.75rem;">
                                                                    <div class="text-danger"><strong>- Dữ liệu cũ:</strong> {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</div>
                                                                    <div class="text-success mt-1"><strong>+ Dữ liệu mới:</strong> {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <code class="text-muted">{{ $log->ip_address ?: '127.0.0.1' }}</code>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-shield-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Chưa có nhật ký truy vết nào được ghi nhận cho hồ sơ này.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 5: Informed Consents & Digital Signatures -->
            <div class="tab-pane fade" id="consents" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="card-title fw-bold text-dark mb-0">
                                <i class="bi bi-file-earmark-check-fill text-success me-1"></i> Hồ Sơ Cam Đoan Phẫu Thuật / Thủ Thuật Y Khoa Ký Số (E-Consent)
                            </h6>
                            <small class="text-muted">Lưu trữ chữ ký điện tử hợp pháp của Bệnh nhân / Thân nhân theo Luật Khám bệnh, chữa bệnh 2023</small>
                        </div>
                        <a href="{{ route('reception.consents.create', $patient) }}" class="btn btn-success btn-sm shadow-sm">
                            <i class="bi bi-pen-fill me-1"></i> Lập Bản Cam Đoan Ký Số Mới
                        </a>
                    </div>

                    <div class="card-body p-0">
                        @if($patient->informedConsents->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 140px;">Mã Bản Cam Kết</th>
                                            <th>Tên Thủ Thuật / Phẫu Thuật</th>
                                            <th>Bác Sĩ Phụ Trách</th>
                                            <th>Người Ký & Quan Hệ</th>
                                            <th style="width: 130px;">Chữ Ký Số</th>
                                            <th style="width: 150px;">Thời Gian Ký</th>
                                            <th class="text-end pe-3" style="width: 130px;">Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->informedConsents as $consent)
                                            <tr>
                                                <td class="ps-3 font-monospace fw-bold text-primary">
                                                    {{ $consent->consent_code }}
                                                </td>
                                                <td>
                                                    <strong class="text-dark d-block">{{ $consent->procedure_name }}</strong>
                                                    <span class="badge bg-light text-dark border">{{ $consent->department_name }}</span>
                                                </td>
                                                <td>
                                                    {{ $consent->doctor ? $consent->doctor->full_name_with_title : '--' }}
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $consent->signer_name }}</div>
                                                    <span class="badge bg-secondary-subtle text-secondary small">{{ $consent->signer_relationship_text }}</span>
                                                    @if($consent->signer_id_card)
                                                        <div class="text-muted" style="font-size: 0.72rem;">CCCD: {{ $consent->signer_id_card }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($consent->signature_data)
                                                        <div class="border rounded bg-white p-1 text-center" style="max-width: 100px;">
                                                            <img src="{{ $consent->signature_data }}" alt="Chữ ký" style="height: 32px; max-width: 100%; object-fit: contain;">
                                                        </div>
                                                        <span class="badge bg-success-subtle text-success mt-1" style="font-size: 0.65rem;">
                                                            <i class="bi bi-shield-check"></i> Đã ký số
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning">Chưa ký</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace text-muted">
                                                    {{ $consent->signed_at ? $consent->signed_at->format('d/m/Y H:i') : '--' }}
                                                    <div style="font-size: 0.7rem;">IP: {{ $consent->ip_address ?: '127.0.0.1' }}</div>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="{{ route('reception.consents.print', $consent) }}" target="_blank" class="btn btn-outline-primary btn-sm py-1 px-2" title="In bản cam đoan chuẩn Bộ Y Tế">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                        <form action="{{ route('reception.consents.destroy', $consent) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản cam đoan này?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Xóa">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-file-earmark-lock fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">Chưa có bản cam đoan nào được lập cho bệnh nhân này</h6>
                                <p class="small text-muted mb-3">Thực hiện lập và ký số giấy cam đoan phẫu thuật/thủ thuật trên màn hình cảm ứng hoặc tablet trước khi can thiệp y khoa.</p>
                                <a href="{{ route('reception.consents.create', $patient) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-pen me-1"></i> Lập Bản Cam Đoan Mới Ngay
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ghi Nhận Sinh Hiệu Mới (Record Vitals) -->
<div class="modal fade" id="modalRecordVital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('reception.patients.vitals.store', $patient) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold">
                        <i class="bi bi-heart-pulse me-2"></i>Ghi Nhận Chỉ Số Sinh Hiệu - {{ $patient->full_name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex align-items-center mb-3">
                        <i class="bi bi-person-vcard fs-3 text-danger me-3"></i>
                        <div>
                            <div class="fw-bold">{{ $patient->full_name }} ({{ $patient->patient_code }})</div>
                            <div class="small text-muted">Giới tính: {{ $patient->gender_name }} | {{ $patient->dob ? $patient->dob->age . ' tuổi' : '' }} | Nhóm máu: {{ $patient->blood_group ?: 'Chưa rõ' }}</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Thời điểm đo & Ca hẹn -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Thời Điểm Đo Sinh Hiệu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="recorded_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Liên Kết Ca Hẹn Khám (Nếu có)</label>
                            <select name="appointment_id" class="form-select">
                                <option value="">-- Không gắn ca khám cụ thể --</option>
                                @foreach($patient->appointments as $app)
                                    <option value="{{ $app->id }}">
                                        {{ $app->appointment_code }} - {{ $app->appointment_date ? $app->appointment_date->format('d/m/Y') : '' }} ({{ $app->specialty ? $app->specialty->name : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Huyết áp -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-danger text-uppercase">Huyết Áp Tâm Thu (Tối Đa)</label>
                            <div class="input-group">
                                <input type="number" name="systolic_bp" class="form-control font-monospace fw-bold" placeholder="VD: 120" min="40" max="300">
                                <span class="input-group-text">mmHg</span>
                            </div>
                            <div class="form-text small">Chuẩn bình thường: 90 - 129 mmHg.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-primary text-uppercase">Huyết Áp Tâm Trương (Tối Thiểu)</label>
                            <div class="input-group">
                                <input type="number" name="diastolic_bp" class="form-control font-monospace fw-bold" placeholder="VD: 80" min="30" max="200">
                                <span class="input-group-text">mmHg</span>
                            </div>
                            <div class="form-text small">Chuẩn bình thường: 60 - 84 mmHg.</div>
                        </div>

                        <!-- Mạch & Thân nhiệt -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Mạch / Nhịp Tim</label>
                            <div class="input-group">
                                <input type="number" name="heart_rate" class="form-control font-monospace fw-bold" placeholder="VD: 75" min="30" max="250">
                                <span class="input-group-text">lần/phút</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Thân Nhiệt</label>
                            <div class="input-group">
                                <input type="number" name="temperature" step="0.1" class="form-control font-monospace fw-bold" placeholder="VD: 36.8" min="30" max="45">
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Nồng Độ Oxy SpO2</label>
                            <div class="input-group">
                                <input type="number" name="spo2" class="form-control font-monospace fw-bold" placeholder="VD: 98" min="50" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- Thể trạng: Cân nặng & Chiều cao -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cân Nặng</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="weight" step="0.1" class="form-control font-monospace" placeholder="VD: 65.5" min="1" max="300">
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Chiều Cao</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="height" step="0.5" class="form-control font-monospace" placeholder="VD: 168" min="30" max="250">
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>

                        <!-- Live MEWS Score Calculation Box -->
                        <div class="col-12">
                            <div id="modalMewsBox" class="p-3 rounded border bg-light d-flex align-items-center justify-content-between transition">
                                <div class="d-flex align-items-center gap-2">
                                    <i id="modalMewsIcon" class="bi bi-heart-pulse-fill fs-4 text-primary"></i>
                                    <div>
                                        <div class="small fw-bold">Thang Điểm Cảnh Báo Lâm Sàng Sớm (MEWS Realtime):</div>
                                        <div id="modalMewsText" class="small text-muted">Nhập các chỉ số sinh hiệu phía trên để hệ thống tự động phân tầng nguy cơ lâm sàng.</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span id="modalMewsBadge" class="badge bg-secondary fs-6 px-3 py-2 font-monospace">0 Điểm</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Ghi Chú Triệu Chứng / Đánh Giá Lúc Đo</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="VD: Đo sau nghỉ ngơi 15 phút, người bệnh tỉnh táo, không chóng mặt">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Kết Quả Sinh Hiệu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('vitalsChart');
    if (!ctx) return;

    const chartData = @json($vitalsChartData);

    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        return;
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'HA Tâm Thu (mmHg)',
                    data: chartData.systolic,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#ef4444'
                },
                {
                    label: 'HA Tâm Trương (mmHg)',
                    data: chartData.diastolic,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#3b82f6'
                },
                {
                    label: 'Nhịp Tim (lần/phút)',
                    data: chartData.heartRate,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.3,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                },
                tooltip: {
                    callbacks: {
                        footer: function (items) {
                            return 'Đo đạc theo dõi tại BV Đa Khoa Tâm Anh';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 40,
                    suggestedMax: 160,
                    grid: {
                        color: '#f1f5f9'
                    },
                    title: {
                        display: true,
                        text: 'Chỉ số (mmHg / l/p)',
                        color: '#64748b',
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11 }
                    }
                }
            }
        }
    });

    // MEWS Realtime Live Calculator in Modal
    const sysInput = document.querySelector('input[name="systolic_bp"]');
    const hrInput = document.querySelector('input[name="heart_rate"]');
    const tempInput = document.querySelector('input[name="temperature"]');
    const spo2Input = document.querySelector('input[name="spo2"]');
    const mewsBox = document.getElementById('modalMewsBox');
    const mewsIcon = document.getElementById('modalMewsIcon');
    const mewsText = document.getElementById('modalMewsText');
    const mewsBadge = document.getElementById('modalMewsBadge');

    function calculateLiveMews() {
        if (!mewsBox || !mewsBadge) return;

        const sys = parseFloat(sysInput?.value) || null;
        const hr = parseFloat(hrInput?.value) || null;
        const temp = parseFloat(tempInput?.value) || null;
        const spo2 = parseFloat(spo2Input?.value) || null;

        if (!sys && !hr && !temp && !spo2) {
            mewsBox.className = 'p-3 rounded border bg-light d-flex align-items-center justify-content-between transition';
            mewsIcon.className = 'bi bi-heart-pulse-fill fs-4 text-primary';
            mewsText.textContent = 'Nhập các chỉ số sinh hiệu phía trên để hệ thống tự động phân tầng nguy cơ lâm sàng.';
            mewsBadge.className = 'badge bg-secondary fs-6 px-3 py-2 font-monospace';
            mewsBadge.textContent = '0 Điểm';
            return;
        }

        let score = 0;
        // 1. Systolic BP
        if (sys !== null) {
            if (sys <= 70) score += 3;
            else if (sys <= 80) score += 2;
            else if (sys <= 100) score += 1;
            else if (sys >= 200) score += 2;
        }
        // 2. Heart rate
        if (hr !== null) {
            if (hr < 40) score += 2;
            else if (hr <= 50) score += 1;
            else if (hr >= 130) score += 3;
            else if (hr >= 111) score += 2;
            else if (hr >= 101) score += 1;
        }
        // 3. Temp
        if (temp !== null) {
            if (temp < 35.0) score += 2;
            else if (temp >= 38.5) score += 2;
        }
        // 4. SpO2
        if (spo2 !== null) {
            if (spo2 < 90) score += 3;
            else if (spo2 <= 92) score += 2;
            else if (spo2 <= 94) score += 1;
        }

        if (score >= 5) {
            mewsBox.className = 'p-3 rounded border border-danger bg-danger-subtle d-flex align-items-center justify-content-between transition';
            mewsIcon.className = 'bi bi-exclamation-octagon-fill fs-3 text-danger animate-pulse';
            mewsText.innerHTML = '<strong class="text-danger">🚨 CẢNH BÁO NGUY CƠ CHUYỂN BIẾN NẶNG!</strong> Kích hoạt chuông cấp cứu, ưu tiên xử trí khẩn cấp.';
            mewsBadge.className = 'badge bg-danger fs-6 px-3 py-2 font-monospace shadow-sm';
            mewsBadge.textContent = `${score} Điểm - Báo Động Đỏ`;
        } else if (score >= 3) {
            mewsBox.className = 'p-3 rounded border border-warning bg-warning-subtle d-flex align-items-center justify-content-between transition';
            mewsIcon.className = 'bi bi-exclamation-triangle-fill fs-4 text-warning';
            mewsText.innerHTML = '<strong class="text-dark">⚠️ NGUY CƠ TRUNG BÌNH (MỨC VÀNG):</strong> Ưu tiên khám sớm, báo điều dưỡng theo dõi sát sinh hiệu.';
            mewsBadge.className = 'badge bg-warning text-dark fs-6 px-3 py-2 font-monospace';
            mewsBadge.textContent = `${score} Điểm - Mức Vàng`;
        } else {
            mewsBox.className = 'p-3 rounded border border-success bg-success-subtle d-flex align-items-center justify-content-between transition';
            mewsIcon.className = 'bi bi-check-circle-fill fs-4 text-success';
            mewsText.innerHTML = '<span class="text-success fw-semibold">✅ NGUY CƠ THẤP (MỨC XANH):</span> Chỉ số sinh hiệu ổn định, tiến hành khám thường quy.';
            mewsBadge.className = 'badge bg-success fs-6 px-3 py-2 font-monospace';
            mewsBadge.textContent = `${score} Điểm - An Toàn`;
        }
    }

    [sysInput, hrInput, tempInput, spo2Input].forEach(inp => {
        if (inp) {
            inp.addEventListener('input', calculateLiveMews);
        }
    });
});
</script>
@endpush
