@extends('layouts.admin')

@section('title', 'Sản Nhi An Toàn, Ung Bướu & Hộp Đen Cấp Cứu')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Sản Nhi An Toàn, Ung Bướu & Hộp Đen Cấp Cứu
            </h4>
            <p class="text-muted small mb-0">
                Partograph chuyển dạ WHO, vòng RFID chống bắt cóc trẻ, phác đồ hóa trị BSA, Tumor Board ung thư & Giờ vàng cấp cứu
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newGoldenHourModal">
                <i class="bi bi-stopwatch me-1"></i> Hộp Đen Giờ Vàng Cấp Cứu
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newTumorBoardModal">
                <i class="bi bi-people-fill me-1"></i> Họp Hội Đồng Ung Thư
            </button>
            <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newChemoModal">
                <i class="bi bi-capsule me-1"></i> Lên Chu Kỳ Hóa Trị BSA
            </button>
            <button type="button" class="btn btn-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newInfantTagModal">
                <i class="bi bi-upc-scan me-1"></i> Ghép Đôi RFID Mẹ - Bé
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newPartographModal">
                <i class="bi bi-graph-up me-1"></i> Ghi Nhận Partograph
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="matTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill small fw-semibold" id="parto-tab" data-bs-toggle="pill" data-bs-target="#parto-pane" type="button">
                <i class="bi bi-graph-up-arrow me-1 text-primary"></i> 1. Partograph Chuyển Dạ ({{ $partographs->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="infant-tab" data-bs-toggle="pill" data-bs-target="#infant-pane" type="button">
                <i class="bi bi-shield-check me-1 text-danger"></i> 2. Vòng RFID An Ninh Sơ Sinh ({{ $infantTags->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="chemo-tab" data-bs-toggle="pill" data-bs-target="#chemo-pane" type="button">
                <i class="bi bi-radioactive me-1 text-warning"></i> 3. Hóa Trị Ung Thư BSA ({{ $chemoCycles->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="tumor-tab" data-bs-toggle="pill" data-bs-target="#tumor-pane" type="button">
                <i class="bi bi-mortarboard me-1 text-info"></i> 4. Virtual Tumor Board ({{ $tumorBoards->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="golden-tab" data-bs-toggle="pill" data-bs-target="#golden-pane" type="button">
                <i class="bi bi-alarm me-1 text-danger"></i> 5. Hộp Đen Giờ Vàng ({{ $goldenHours->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="quality-tab" data-bs-toggle="pill" data-bs-target="#quality-pane" type="button">
                <i class="bi bi-award me-1 text-success"></i> 6. Bộ 83 Tiêu Chí BYT ({{ $qualityCriteria->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="matTabsContent">
        <!-- 1. PARTOGRAPH PANE -->
        <div class="tab-pane fade show active" id="parto-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Biểu Đồ Theo Dõi Chuyển Dạ Điện Tử Partograph (WHO Protocol)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Thời Điểm</th>
                                <th>Sản Phụ</th>
                                <th>Độ Mở CTC</th>
                                <th>Độ Lọt Ngôi Thai</th>
                                <th>Tim Thai (FHR)</th>
                                <th>Cơn Co / 10 Phút</th>
                                <th>Cảnh Báo Mổ Bắt Con</th>
                                <th class="text-end pe-3">Nữ Hộ Sinh</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($partographs as $parto)
                            <tr class="{{ $parto->crosses_action_line ? 'table-danger' : ($parto->crosses_alert_line ? 'table-warning' : '') }}">
                                <td>{{ $parto->observation_time ? $parto->observation_time->format('H:i d/m') : 'N/A' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $parto->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $parto->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-primary fs-6">{{ $parto->cervical_dilation_cm }} cm</strong></td>
                                <td>{{ $parto->fetal_head_station }}</td>
                                <td>
                                    <span class="{{ $parto->fetal_heart_rate < 110 || $parto->fetal_heart_rate > 160 ? 'badge bg-danger' : 'text-success fw-bold' }}">
                                        {{ $parto->fetal_heart_rate }} bpm
                                    </span>
                                </td>
                                <td>{{ $parto->contractions_per_10min }} cơn</td>
                                <td>
                                    @if($parto->crosses_action_line)
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-lightning-fill me-1"></i>MỔ CẤP CỨU (ACTION LINE)</span>
                                    @elseif($parto->crosses_alert_line)
                                        <span class="badge bg-warning text-dark rounded-pill">Đình trệ (Alert line)</span>
                                    @else
                                        <span class="badge bg-success rounded-pill">Chuyển dạ thuận lợi</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $parto->midwife_name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có lượt theo dõi Partograph nào trong ngày.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. INFANT RFID PANE -->
        <div class="tab-pane fade" id="infant-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock-fill text-danger me-2"></i>Vòng Tay RFID Ghép Đôi Mẹ - Bé & Chống Bắt Cóc Trẻ Sơ Sinh</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã RFID Bé</th>
                                <th>Sản Phụ (Mẹ)</th>
                                <th>Giới Tính / Cân Nặng</th>
                                <th>Giờ Sinh</th>
                                <th>Mã Vòng Tay Mẹ</th>
                                <th>Ghép Đôi (Pairing)</th>
                                <th class="text-end pe-3">Trạng Thái Ranh Giới</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($infantTags as $tag)
                            <tr class="{{ $tag->perimeter_status === 'BREACH_ALARM' ? 'table-danger' : '' }}">
                                <td><strong class="font-monospace text-primary fs-6">{{ $tag->infant_tag_code }}</strong></td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tag->mother->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $tag->mother->patient_code ?? '' }}</small>
                                </td>
                                <td>{{ $tag->baby_gender }} • <strong>{{ number_format($tag->birth_weight_grams) }}g</strong></td>
                                <td>{{ $tag->birth_time ? $tag->birth_time->format('H:i d/m/Y') : 'N/A' }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $tag->mother_band_code }}</span></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-link-45deg me-1"></i>KHỚP 100%
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($tag->perimeter_status === 'BREACH_ALARM')
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-bell-fill me-1"></i>BÁO ĐỘNG BỎ TRỐN / BẮT CÓC</span>
                                    @elseif($tag->perimeter_status === 'AUTHORIZED_TRANSFER')
                                        <span class="badge bg-info text-dark rounded-pill">Chuyển khoa có phép</span>
                                    @else
                                        <span class="badge bg-success rounded-pill">An toàn trong buồng</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có trẻ sơ sinh nào được kích hoạt vòng tay an ninh RFID.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. CHEMOTHERAPY BSA PANE -->
        <div class="tab-pane fade" id="chemo-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-radioactive text-warning me-2"></i>Phác Đồ Hóa Trị Ung Thư Đa Chu Kỳ Theo Diện Tích Da (BSA)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Phác Đồ Hóa Trị</th>
                                <th>Chu Kỳ Điều Trị</th>
                                <th>Diện Tích Da (BSA)</th>
                                <th>Bạch Cầu ANC</th>
                                <th>Tiểu Cầu</th>
                                <th>An Toàn Cấp Thuốc</th>
                                <th class="text-end pe-3">Ngày Lên Lịch</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($chemoCycles as $cc)
                            <tr class="{{ !$cc->is_eligible_to_dispense ? 'table-warning' : '' }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $cc->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $cc->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-dark">{{ $cc->regimen_name }}</strong></td>
                                <td><span class="badge bg-primary rounded-pill">Chu kỳ {{ $cc->cycle_number }}/{{ $cc->total_cycles }}</span></td>
                                <td>{{ $cc->body_surface_area }} m²</td>
                                <td>
                                    <span class="{{ $cc->anc_neutrophil_count < 1500 ? 'text-danger fw-bold' : 'text-success' }}">
                                        {{ number_format($cc->anc_neutrophil_count) }} /mcL
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $cc->platelet_count < 100000 ? 'text-danger fw-bold' : 'text-success' }}">
                                        {{ number_format($cc->platelet_count) }} /mcL
                                    </span>
                                </td>
                                <td>
                                    @if($cc->is_eligible_to_dispense)
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i>ĐỦ ĐIỀU KIỆN TRUYỀN</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle me-1"></i>HOÃN VÌ ĐỘC TÍNH TỦY</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $cc->scheduled_date ? $cc->scheduled_date->format('d/m/Y') : '' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có chu kỳ hóa trị nào được lên lịch.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. VIRTUAL TUMOR BOARD PANE -->
        <div class="tab-pane fade" id="tumor-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-mortarboard text-info me-2"></i>Biên Bản Hội Đồng Ung Thư Đa Chuyên Khoa (Virtual Tumor Board)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã Hội Đồng</th>
                                <th>Bệnh Nhân</th>
                                <th>Chẩn Đoán Ung Thư</th>
                                <th>Ngày Hội Chẩn</th>
                                <th>Phác Đồ Đồng Thuận Đa Mô Thức</th>
                                <th>Thành Viên Tham Gia</th>
                                <th class="text-end pe-3">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($tumorBoards as $tb)
                            <tr>
                                <td><strong class="font-monospace text-primary">{{ $tb->board_code }}</strong></td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tb->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $tb->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="text-danger fw-semibold">{{ $tb->cancer_diagnosis }}</span></td>
                                <td>{{ $tb->meeting_date ? $tb->meeting_date->format('H:i d/m/Y') : 'N/A' }}</td>
                                <td>{{ $tb->consensus_treatment_plan }}</td>
                                <td>
                                    @if(is_array($tb->multidisciplinary_attendees))
                                        @foreach($tb->multidisciplinary_attendees as $att)
                                            <span class="badge bg-light text-dark border me-1">{{ $att }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-success rounded-pill px-2 py-1">ĐÃ THÔNG QUA</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có biên bản Tumor Board nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. GOLDEN HOUR TRACKER PANE -->
        <div class="tab-pane fade" id="golden-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-stopwatch text-danger me-2"></i>Hộp Đen Cấp Cứu Giờ Vàng (Door-to-Needle < 60p & Door-to-Balloon < 90p)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Loại Cấp Cứu</th>
                                <th>Thời Điểm Đến Viện</th>
                                <th>Thời Điểm Can Thiệp</th>
                                <th>Thời Gian Đo Được (Phút)</th>
                                <th>Đạt Mục Tiêu Giờ Vàng</th>
                                <th class="text-end pe-3">Đội Ngũ Cấp Cứu</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($goldenHours as $gh)
                            <tr class="{{ $gh->met_target ? 'table-success' : 'table-danger' }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $gh->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $gh->patient->patient_code ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $gh->emergency_type === 'STROKE_BRAIN' ? 'bg-primary' : 'bg-danger' }} rounded-pill">
                                        {{ $gh->emergency_type === 'STROKE_BRAIN' ? 'ĐỘT QUỴ NÃO' : 'NHỒI MÁU CƠ TIM' }}
                                    </span>
                                </td>
                                <td>{{ $gh->door_arrival_time ? $gh->door_arrival_time->format('H:i d/m') : '' }}</td>
                                <td>{{ $gh->intervention_time ? $gh->intervention_time->format('H:i d/m') : '' }}</td>
                                <td>
                                    @if($gh->emergency_type === 'STROKE_BRAIN')
                                        <strong>{{ $gh->door_to_needle_minutes }} phút</strong> (Target < 60p)
                                    @else
                                        <strong>{{ $gh->door_to_balloon_minutes }} phút</strong> (Target < 90p)
                                    @endif
                                </td>
                                <td>
                                    @if($gh->met_target)
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-all me-1"></i>ĐẠT CHUẨN GIỜ VÀNG</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-x me-1"></i>QUÁ NGƯỠNG CHO PHÉP</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $gh->attending_team }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có ca cấp cứu giờ vàng nào được ghi nhận.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 6. QUALITY CRITERIA 83 PANE -->
        <div class="tab-pane fade" id="quality-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-award text-success me-2"></i>Tự Đánh Giá 83 Tiêu Chí Chất Lượng Bệnh Viện (Bộ Y Tế)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã Tiêu Chí</th>
                                <th>Tên Tiêu Chí Đánh Giá</th>
                                <th>Phần / Chương</th>
                                <th>Mức Hiện Tại</th>
                                <th>Mục Tiêu Đạt Được</th>
                                <th class="text-end pe-3">Minh Chứng Tự Động Từ EMR</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($qualityCriteria as $qc)
                            <tr>
                                <td><strong class="font-monospace text-primary fs-6">{{ $qc->criteria_code }}</strong></td>
                                <td class="fw-medium text-dark">{{ $qc->criteria_name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $qc->section }}</span></td>
                                <td>
                                    <span class="badge bg-success rounded-pill px-2 py-1">
                                        MỨC {{ $qc->current_level }}/5 (RẤT TỐT)
                                    </span>
                                </td>
                                <td><span class="badge bg-primary rounded-pill">Mức {{ $qc->target_level }}/5</span></td>
                                <td class="text-end pe-3 text-muted">{{ $qc->evidence_summary }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Ghi Nhận Partograph -->
<div class="modal fade" id="newPartographModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.maternity-oncology.partograph') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-graph-up me-2"></i>Ghi Nhận Điểm Theo Dõi Chuyển Dạ Partograph</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sản phụ <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn sản phụ --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thời điểm quan sát <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="observation_time" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Độ mở CTC (cm)</label>
                            <input type="number" step="0.5" name="cervical_dilation_cm" class="form-control form-control-sm" value="5.0" min="1" max="10" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Độ lọt ngôi thai (-3 đến +3)</label>
                            <input type="number" step="0.5" name="fetal_head_station" class="form-control form-control-sm" value="0.0" min="-3" max="3" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tim thai FHR (bpm) <span class="text-danger">*</span></label>
                            <input type="number" name="fetal_heart_rate" class="form-control form-control-sm" value="140" required>
                            <small class="text-muted">Bình thường: 110 - 160 bpm</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cơn co / 10 phút</label>
                            <input type="number" name="contractions_per_10min" class="form-control form-control-sm" value="3" min="1" max="7" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Nữ hộ sinh theo dõi</label>
                        <input type="text" name="midwife_name" class="form-control form-control-sm" value="NHS. Lê Thị Thu Vân" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Lưu Điểm Partograph</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Ghép Đôi RFID Mẹ - Bé -->
<div class="modal fade" id="newInfantTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.maternity-oncology.infant-tag') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-upc-scan me-2"></i>Ghép Đôi Vòng Tay RFID Mẹ - Bé</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sản phụ (Mẹ) <span class="text-danger">*</span></label>
                        <select name="mother_patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn mẹ --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giới tính của bé</label>
                            <select name="baby_gender" class="form-select form-select-sm" required>
                                <option value="Bé trai">Bé trai</option>
                                <option value="Bé gái">Bé gái</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cân nặng sau sinh (gram) <span class="text-danger">*</span></label>
                            <input type="number" step="10" name="birth_weight_grams" class="form-control form-control-sm" value="3250" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Giờ sinh chính thức <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="birth_time" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã vòng tay mẹ quét được (RFID Band) <span class="text-danger">*</span></label>
                        <input type="text" name="mother_band_code" class="form-control form-control-sm font-monospace" value="MOTHER-RFID-{{ date('Ymd') }}-98" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Trạng thái ranh giới phòng sinh</label>
                        <select name="perimeter_status" class="form-select form-select-sm" required>
                            <option value="INSIDE_WARD">An toàn trong buồng sinh (Inside Ward)</option>
                            <option value="AUTHORIZED_TRANSFER">Chuyển khoa có phép (Authorized)</option>
                            <option value="BREACH_ALARM">CẢNH BÁO VI PHẠM RANH GIỚI (Breach Alarm)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Kích Hoạt Ghép Đôi RFID</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Lên Chu Kỳ Hóa Trị BSA -->
<div class="modal fade" id="newChemoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.maternity-oncology.chemo-cycle') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-radioactive me-2"></i>Đăng Ký Chu Kỳ Hóa Trị BSA</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold">Phác đồ hóa trị <span class="text-danger">*</span></label>
                            <input type="text" name="regimen_name" class="form-control form-control-sm font-monospace fw-bold" value="FOLFOX6 (Oxaliplatin + 5-FU + Leucovorin)" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Chu kỳ</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="cycle_number" class="form-control" value="2" min="1" required>
                                <span class="input-group-text">/</span>
                                <input type="number" name="total_cycles" class="form-control" value="6" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Diện tích da tính toán (BSA - m²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="body_surface_area" class="form-control form-control-sm" value="1.68" min="0.5" max="3.0" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Bạch cầu hạt ANC (/mcL) <span class="text-danger">*</span></label>
                            <input type="number" name="anc_neutrophil_count" class="form-control form-control-sm" value="2100" required>
                            <small class="text-muted">An toàn: >= 1500</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tiểu cầu (/mcL) <span class="text-danger">*</span></label>
                            <input type="number" name="platelet_count" class="form-control form-control-sm" value="165000" required>
                            <small class="text-muted">An toàn: >= 100.000</small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Ngày dự kiến truyền</label>
                        <input type="date" name="scheduled_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Kiểm Tra & Lưu Chu Kỳ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Họp Tumor Board -->
<div class="modal fade" id="newTumorBoardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.maternity-oncology.tumor-board') }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold"><i class="bi bi-people-fill me-2"></i>Biên Bản Hội Đồng Ung Thư (Tumor Board)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chẩn đoán ung thư chi tiết <span class="text-danger">*</span></label>
                        <input type="text" name="cancer_diagnosis" class="form-control form-control-sm" value="Carcinoma tuyến đại tràng sigma T3N1M0 Giai đoạn IIIB" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thời điểm họp hội đồng</label>
                        <input type="datetime-local" name="meeting_date" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phác đồ thống nhất đa mô thức <span class="text-danger">*</span></label>
                        <textarea name="consensus_treatment_plan" class="form-control form-control-sm" rows="2" placeholder="Phẫu thuật nội soi cắt đại tràng triệt căn + Hóa trị bổ trợ..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Thành viên chuyên gia tham gia (Cách nhau bằng dấu phẩy)</label>
                        <input type="text" name="attendees" class="form-control form-control-sm" value="BS. Ung Bướu, BS. Phẫu Thuật Ngoại, BS. Giải Phẫu Bệnh, BS. CĐHA" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3">Lưu Biên Bản Hội Đồng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 5: Hộp Đen Giờ Vàng Cấp Cứu -->
<div class="modal fade" id="newGoldenHourModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.maternity-oncology.golden-hour') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-stopwatch me-2"></i>Ghi Nhận Hộp Đen Giờ Vàng Cấp Cứu</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Loại ca cấp cứu sống còn <span class="text-danger">*</span></label>
                        <select name="emergency_type" class="form-select form-select-sm" required>
                            <option value="STROKE_BRAIN">Đột quỵ não cấp (Door-to-Needle < 60 phút)</option>
                            <option value="STEMI_HEART">Nhồi máu cơ tim cấp STEMI (Door-to-Balloon < 90 phút)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thời điểm đến cửa viện (Door) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="door_arrival_time" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i', strtotime('-45 minutes')) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thời điểm can thiệp (Needle/Balloon) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="intervention_time" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Đội ngũ cấp cứu tiếp nhận</label>
                        <input type="text" name="attending_team" class="form-control form-control-sm" value="Code Stroke Team BV Tâm Anh" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Lưu Hộp Đen Cấp Cứu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
