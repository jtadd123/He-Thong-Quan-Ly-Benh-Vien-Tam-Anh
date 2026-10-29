@extends('layouts.admin')

@section('title', 'Phục Hồi Chức Năng, YHCT & Bệnh Viện Xanh ESG')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-tree text-success me-2"></i>Phục Hồi Chức Năng, Y Học Cổ Truyền & Bệnh Viện Xanh ESG
            </h4>
            <p class="text-muted small mb-0">
                Thang điểm FIM, bài thuốc Thập Bát Phản, châm cứu, vàng da Bhutani, sữa mẹ thanh trùng & Chỉ số Bệnh viện xanh ESG
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newRadiationModal">
                <i class="bi bi-radioactive me-1"></i> Liều Tia Xạ ALARA
            </button>
            <button type="button" class="btn btn-outline-info rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newMilkModal">
                <i class="bi bi-droplet-half me-1"></i> Sữa Mẹ Pasteur
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newJaundiceModal">
                <i class="bi bi-sun me-1"></i> Vàng Da Bhutani
            </button>
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newHerbalModal">
                <i class="bi bi-flower1 me-1"></i> Bốc Thuốc Thang YHCT
            </button>
            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newRehabModal">
                <i class="bi bi-universal-access me-1"></i> Kế Hoạch PHCN (FIM)
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="rehabTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill small fw-semibold" id="rehab-tab" data-bs-toggle="pill" data-bs-target="#rehab-pane" type="button">
                <i class="bi bi-person-walking me-1 text-success"></i> 1. Phục Hồi Chức Năng FIM ({{ $rehabPlans->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="herbal-tab" data-bs-toggle="pill" data-bs-target="#herbal-pane" type="button">
                <i class="bi bi-cup-hot me-1 text-primary"></i> 2. Thuốc Thang Đông Y ({{ $herbalPrescriptions->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="jaundice-tab" data-bs-toggle="pill" data-bs-target="#jaundice-pane" type="button">
                <i class="bi bi-sun-fill me-1 text-warning"></i> 3. Vàng Da Sơ Sinh Bhutani ({{ $jaundiceLogs->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="milk-tab" data-bs-toggle="pill" data-bs-target="#milk-pane" type="button">
                <i class="bi bi-heart-pulse me-1 text-info"></i> 4. Ngân Hàng Sữa Mẹ ({{ $milkBatches->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="esg-tab" data-bs-toggle="pill" data-bs-target="#esg-pane" type="button">
                <i class="bi bi-leaf me-1 text-success"></i> 5. Bệnh Viện Xanh ESG ({{ $esgLogs->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="rad-tab" data-bs-toggle="pill" data-bs-target="#rad-pane" type="button">
                <i class="bi bi-radioactive me-1 text-danger"></i> 6. Bức Xạ ALARA ({{ $radiationLogs->total() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="rehabTabsContent">
        <!-- 1. REHAB PANE -->
        <div class="tab-pane fade show active" id="rehab-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-walking text-success me-2"></i>Kế Hoạch Phục Hồi Chức Năng & Lượng Giá Độc Lập FIM / Barthel Index</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Chẩn Đoán Di Chứng</th>
                                <th>Thang Điểm FIM</th>
                                <th>Chỉ Số Barthel</th>
                                <th>Mức Độ Tự Chủ</th>
                                <th>Chuyên Khoa Trị Liệu</th>
                                <th>Mục Tiêu Tuần</th>
                                <th class="text-end pe-3">Chuyên Viên PHCN</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($rehabPlans as $rp)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $rp->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $rp->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="text-dark fw-medium">{{ $rp->diagnosis }}</span></td>
                                <td><strong class="text-success fs-6">{{ $rp->fim_score_total }} / 126 đ</strong></td>
                                <td><strong>{{ $rp->barthel_index }} / 100 đ</strong></td>
                                <td>
                                    <span class="badge {{ $rp->functional_status === 'INDEPENDENT' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $rp->functional_status }}
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $rp->assigned_therapy_type }}</span></td>
                                <td>{{ $rp->weekly_goals }}</td>
                                <td class="text-end pe-3">{{ $rp->pt_specialist }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có kế hoạch phục hồi chức năng nào được lập.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. HERBAL PANE -->
        <div class="tab-pane fade" id="herbal-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cup-hot text-primary me-2"></i>Đơn Thuốc Thang Đông Y & Giám Sát Tương Kỵ Thập Bát Phản</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Tên Bài Thuốc</th>
                                <th>Các Vị Thảo Dược</th>
                                <th>Kiểm Tra Thập Bát Phản</th>
                                <th>Số Túi Sắc Sẵn</th>
                                <th class="text-end pe-3">Bác Sĩ YHCT</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($herbalPrescriptions as $hp)
                            <tr class="{{ $hp->has_incompatibility_contraindication ? 'table-danger' : '' }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $hp->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $hp->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-dark">{{ $hp->remedy_name }}</strong></td>
                                <td>{{ $hp->herbal_ingredients }}</td>
                                <td>
                                    @if($hp->has_incompatibility_contraindication)
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>TƯƠNG KỴ: {{ $hp->incompatible_pair }}</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i>AN TOÀN TUYỆT ĐỐI</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary rounded-pill">{{ $hp->decoction_bags_count }} gói</span></td>
                                <td class="text-end pe-3">{{ $hp->traditional_doctor }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có đơn thuốc thang đông y nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. JAUNDICE BHUTANI PANE -->
        <div class="tab-pane fade" id="jaundice-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sun text-warning me-2"></i>Đánh Giá Nguy Cơ Vàng Da Sơ Sinh (Đồ Thị Bhutani Nomogram)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bé Sơ Sinh</th>
                                <th>Tuổi Sau Sinh (Giờ)</th>
                                <th>Bilirubin Toàn Phần (TSB)</th>
                                <th>Phân Vùng Nguy Cơ</th>
                                <th>Chiếu Đèn Phototherapy</th>
                                <th>Thay Máu Cấp Cứu</th>
                                <th class="text-end pe-3">Bác Sĩ Sơ Sinh</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($jaundiceLogs as $jl)
                            <tr class="{{ $jl->exchange_transfusion_indicated ? 'table-danger' : ($jl->phototherapy_indicated ? 'table-warning' : '') }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $jl->infant->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $jl->infant->patient_code ?? '' }}</small>
                                </td>
                                <td><strong>{{ $jl->age_in_hours }} giờ tuổi</strong></td>
                                <td><span class="fs-6 fw-bold {{ $jl->phototherapy_indicated ? 'text-danger' : 'text-success' }}">{{ $jl->total_serum_bilirubin_mg_dl }} mg/dL</span></td>
                                <td>
                                    <span class="badge {{ $jl->risk_zone === 'HIGH_RISK' ? 'bg-danger' : ($jl->risk_zone === 'HIGH_INTERMEDIATE' ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill">
                                        {{ $jl->risk_zone }}
                                    </span>
                                </td>
                                <td>
                                    @if($jl->phototherapy_indicated)
                                        <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-lightbulb-fill me-1"></i>CHỈ ĐỊNH CHIẾU ĐÈN</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Theo dõi thường</span>
                                    @endif
                                </td>
                                <td>
                                    @if($jl->exchange_transfusion_indicated)
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-droplet-fill me-1"></i>THAY MÁU CẤP CỨU</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Không</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $jl->neonatologist }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có kết quả đánh giá vàng da sơ sinh nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. DONOR MILK PANE -->
        <div class="tab-pane fade" id="milk-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-heart-pulse text-info me-2"></i>Ngân Hàng Sữa Mẹ Thanh Trùng Pasteur (62.5°C / 30 Phút)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã Lô Sữa</th>
                                <th>Người Hiến Tặng</th>
                                <th>Thể Tích</th>
                                <th>Nhiệt Độ / Thời Gian</th>
                                <th>Kiểm Nghiệm Vi Sinh</th>
                                <th>Trạng Thái Cấp Phát</th>
                                <th class="text-end pe-3">Hạn Sử Dụng (-20°C)</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($milkBatches as $mb)
                            <tr class="{{ $mb->microbiology_result === 'FAILED_CONTAMINATED' ? 'table-danger' : '' }}">
                                <td><strong class="font-monospace text-primary fs-6">{{ $mb->batch_code }}</strong></td>
                                <td>{{ $mb->donor_code }}</td>
                                <td><strong>{{ $mb->volume_ml }} mL</strong></td>
                                <td>{{ $mb->pasteurization_temperature }}°C • {{ $mb->pasteurization_duration_min }} phút</td>
                                <td>
                                    @if($mb->microbiology_result === 'PASSED_STERILE')
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>VÔ TRÙNG 100%</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>NHIỄM KHUẨN (HỦY)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $mb->status === 'available' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                        {{ strtoupper($mb->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-secondary">{{ $mb->expiry_date ? $mb->expiry_date->format('d/m/Y') : '' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có lô sữa mẹ nào được lưu trữ.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. ESG PANE -->
        <div class="tab-pane fade" id="esg-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-leaf text-success me-2"></i>Bảng Đo Lường Chỉ Số Bệnh Viện Xanh ESG & Giảm Phát Thải CO2</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        @php $latestEsg = $esgLogs->first(); @endphp
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3 rounded-3 border-start border-4 border-success">
                                <div class="text-success fs-3 fw-bold">{{ number_format($latestEsg->paperless_sheets_saved ?? 4200) }}</div>
                                <div class="fw-semibold text-dark small">Tờ Giấy Tiết Kiệm (EMR)</div>
                                <small class="text-muted">Bệnh viện không giấy tờ</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3 rounded-3 border-start border-4 border-info">
                                <div class="text-info fs-3 fw-bold">{{ $latestEsg->carbon_footprint_saved_kg ?? 46.2 }} kg</div>
                                <div class="fw-semibold text-dark small">CO2 Giảm Phát Thải</div>
                                <small class="text-muted">Đóng góp bảo vệ môi trường</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3 rounded-3 border-start border-4 border-warning">
                                <div class="text-warning-emphasis fs-3 fw-bold">{{ $latestEsg->energy_kwh_conserved ?? 145.5 }} kWh</div>
                                <div class="fw-semibold text-dark small">Điện Năng Tiết Kiệm</div>
                                <small class="text-muted">Tối ưu IoT phòng mổ & buồng bệnh</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3 rounded-3 border-start border-4 border-primary">
                                <div class="text-primary fs-3 fw-bold">{{ $latestEsg->esg_score ?? 96 }} / 100</div>
                                <div class="fw-semibold text-dark small">Điểm ESG Bệnh Viện Xanh</div>
                                <small class="text-muted">Chuẩn quốc tế Green Hospital</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. RADIATION ALARA PANE -->
        <div class="tab-pane fade" id="rad-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-radioactive text-danger me-2"></i>Giám Sát An Toàn Bức Xạ Y Tế (Nguyên Tắc ALARA)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Kỹ Thuật Chụp Chiếu</th>
                                <th>Liều Ca Này (mSv)</th>
                                <th>Liều Tích Lũy Năm (mSv)</th>
                                <th>Trạng Thái ALARA</th>
                                <th class="text-end pe-3">Kỹ Thuật Viên CĐHA</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($radiationLogs as $rl)
                            <tr class="{{ $rl->alara_status === 'EXCEEDED_THRESHOLD' ? 'table-danger' : ($rl->alara_status === 'MONITOR_CLOSELY' ? 'table-warning' : '') }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $rl->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $rl->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="text-dark fw-medium">{{ $rl->procedure_name }}</span></td>
                                <td><strong>{{ $rl->dose_msv }} mSv</strong></td>
                                <td><strong class="fs-6 text-primary">{{ $rl->cumulative_dose_msv }} mSv</strong></td>
                                <td>
                                    <span class="badge {{ $rl->alara_status === 'OPTIMAL_SAFE' ? 'bg-success' : ($rl->alara_status === 'MONITOR_CLOSELY' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill">
                                        {{ $rl->alara_status }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">{{ $rl->radiology_technician }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có nhật ký liều tia bức xạ nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Kế Hoạch PHCN -->
<div class="modal fade" id="newRehabModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.rehab-esg.rehab-plan') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-universal-access me-2"></i>Lập Kế Hoạch Phục Hồi Chức Năng & Điểm FIM</h6>
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
                        <label class="form-label fw-semibold">Chẩn đoán di chứng cần PHCN <span class="text-danger">*</span></label>
                        <input type="text" name="diagnosis" class="form-control form-control-sm" value="Liệt nửa người bên trái sau nhồi máu não tháng thứ 2" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thang điểm FIM (18-126) <span class="text-danger">*</span></label>
                            <input type="number" name="fim_score_total" class="form-control form-control-sm" value="78" min="18" max="126" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Chỉ số Barthel (0-100) <span class="text-danger">*</span></label>
                            <input type="number" name="barthel_index" class="form-control form-control-sm" value="60" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chuyên khoa trị liệu chỉ định</label>
                        <select name="assigned_therapy_type" class="form-select form-select-sm" required>
                            <option value="Vật lý trị liệu (PT - Tập vận động, đi lại)">Vật lý trị liệu (PT - Vận động)</option>
                            <option value="Hoạt động trị liệu (OT - Tự phục vụ sinh hoạt)">Hoạt động trị liệu (OT - Sinh hoạt)</option>
                            <option value="Ngôn ngữ trị liệu (ST - Tập nói, nuốt)">Ngôn ngữ trị liệu (ST - Phát âm, nuốt)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mục tiêu phục hồi tuần <span class="text-danger">*</span></label>
                        <input type="text" name="weekly_goals" class="form-control form-control-sm" value="Tự xúc thìa ăn cơm, đứng thăng bằng có gậy 4 chân" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Chuyên viên PHCN phụ trách</label>
                        <input type="text" name="pt_specialist" class="form-control form-control-sm" value="CN. Phục Hồi Chức Năng" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Lưu Kế Hoạch PHCN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Bốc Thuốc Thang YHCT -->
<div class="modal fade" id="newHerbalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.rehab-esg.herbal') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-flower1 me-2"></i>Bốc Thuốc Thang Đông Y & Kiểm Tra Tương Kỵ</h6>
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
                            <label class="form-label fw-semibold">Tên bài thuốc cổ phương <span class="text-danger">*</span></label>
                            <input type="text" name="remedy_name" class="form-control form-control-sm" value="Bát Trân Thang Gia Giảm" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Số gói sắc sẵn</label>
                            <input type="number" name="decoction_bags_count" class="form-control form-control-sm" value="10" min="1" max="30" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Các vị thảo dược (Cách nhau bởi dấu phẩy) <span class="text-danger">*</span></label>
                        <textarea name="herbal_ingredients" class="form-control form-control-sm" rows="2" required>Đương quy 12g, Thục địa 12g, Bạch thược 10g, Xuyên khung 8g, Đảng sâm 12g, Bạch truật 10g, Phục linh 10g, Cam thảo 6g</textarea>
                        <small class="text-muted">Hệ thống sẽ tự động quét phát hiện tương kỵ Thập Bát Phản (Cam thảo kỵ Cam toại, Ô đầu kỵ Bán hạ...).</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Bác sĩ Y học cổ truyền</label>
                        <input type="text" name="traditional_doctor" class="form-control form-control-sm" value="BS. Y Học Cổ Truyền Tâm Anh" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Kiểm Tra & Lưu Đơn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Vàng Da Bhutani -->
<div class="modal fade" id="newJaundiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.rehab-esg.jaundice') }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold"><i class="bi bi-sun me-2"></i>Đánh Giá Vàng Da Sơ Sinh (Bhutani Nomogram)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bé sơ sinh <span class="text-danger">*</span></label>
                        <select name="infant_patient_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn bé --</option>
                            @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tuổi sau sinh (Giờ) <span class="text-danger">*</span></label>
                            <input type="number" name="age_in_hours" class="form-control form-control-sm" value="72" min="1" max="168" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Bilirubin toàn phần TSB (mg/dL) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="total_serum_bilirubin_mg_dl" class="form-control form-control-sm" value="16.5" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Bác sĩ Nhi Sơ Sinh</label>
                        <input type="text" name="neonatologist" class="form-control form-control-sm" value="BS. Khoa Sơ Sinh NICU" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3">Phân Tích Đồ Thị Bhutani</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Sữa Mẹ Pasteur -->
<div class="modal fade" id="newMilkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.rehab-esg.milk-batch') }}">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-droplet-half me-2"></i>Nhập Mẻ Sữa Mẹ Thanh Trùng Pasteur</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mã Người Hiến Tặng Sữa <span class="text-danger">*</span></label>
                            <input type="text" name="donor_code" class="form-control form-control-sm" value="DONOR-HN-2024-05" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thể Tích Mẻ Sữa (mL) <span class="text-danger">*</span></label>
                            <input type="number" name="volume_ml" class="form-control form-control-sm" value="650" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quy chuẩn tiệt trùng nhiệt độ</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="Pasteurizer 62.5°C trong 30 phút liên tục" disabled>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Kết quả kiểm nghiệm vi sinh</label>
                        <select name="microbiology_result" class="form-select form-select-sm" required>
                            <option value="PASSED_STERILE">Đạt tiêu chuẩn vô trùng (Passed Sterile)</option>
                            <option value="FAILED_CONTAMINATED">Nhiễm vi sinh (Hủy bỏ tiêu hủy)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-info btn-sm rounded-pill px-3 text-white">Lưu Mẻ Sữa Mẹ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 5: Liều Bức Xạ ALARA -->
<div class="modal fade" id="newRadiationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.rehab-esg.radiation') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-radioactive me-2"></i>Ghi Nhận Liều Bức Xạ Tia X (Chuẩn ALARA)</h6>
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
                        <label class="form-label fw-semibold">Kỹ thuật chụp chiếu <span class="text-danger">*</span></label>
                        <input type="text" name="procedure_name" class="form-control form-control-sm" value="Chụp CT Lồng ngực liều thấp (Low-dose Chest CT)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Liều tia phát xạ ca chụp (mSv) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="dose_msv" class="form-control form-control-sm" value="1.50" required>
                        <small class="text-muted">Ngưỡng an toàn bức xạ công chúng: < 20 mSv/năm</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Kỹ thuật viên CĐHA</label>
                        <input type="text" name="radiology_technician" class="form-control form-control-sm" value="KTV. Khoa Chẩn Đoán Hình Ảnh" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Lưu Giám Sát Tia X</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
