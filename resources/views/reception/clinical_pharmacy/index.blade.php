@extends('layouts.admin')

@section('title', 'Dược Lâm Sàng Chuyên Sâu, TDM & TPN')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-capsule-pill text-success me-2"></i>Dược Lâm Sàng Chuyên Sâu, TDM & Dinh Dưỡng Tĩnh Mạch (TPN)
            </h4>
            <p class="text-muted small mb-0">
                Đo nồng độ thuốc khoảng trị liệu hẹp (TDM), tính túi dinh dưỡng TPN All-in-One & Báo cáo ADR quốc gia
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newAdrModal">
                <i class="bi bi-exclamation-diamond me-1"></i> Báo Cáo Phản Ứng ADR
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newTpnModal">
                <i class="bi bi-droplet me-1"></i> Tính Túi Dinh Dưỡng TPN
            </button>
            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newTdmModal">
                <i class="bi bi-graph-up-arrow me-1"></i> Ghi Nhận Đo Nồng Độ TDM
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="pharmTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill small fw-semibold" id="tdm-tab" data-bs-toggle="pill" data-bs-target="#tdm-pane" type="button">
                <i class="bi bi-speedometer me-1 text-success"></i> 1. Giám Sát TDM Nồng Độ Thuốc ({{ $tdmLogs->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="tpn-tab" data-bs-toggle="pill" data-bs-target="#tpn-pane" type="button">
                <i class="bi bi-bag-plus me-1 text-warning"></i> 2. Dinh Dưỡng Tĩnh Mạch TPN ({{ $tpnLogs->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="adr-tab" data-bs-toggle="pill" data-bs-target="#adr-pane" type="button">
                <i class="bi bi-shield-fill-exclamation me-1 text-danger"></i> 3. Báo Cáo ADR Quốc Gia ({{ $adrReports->total() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="pharmTabsContent">
        <!-- 1. TDM PANE -->
        <div class="tab-pane fade show active" id="tdm-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-speedometer text-success me-2"></i>Kết Quả Đo Nồng Độ Thuốc Trong Máu (TDM) & Khuyến Cáo Chỉnh Liều</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Thời Điểm</th>
                                <th>Bệnh Nhân</th>
                                <th>Thuốc Theo Dõi</th>
                                <th>Nồng Độ Đáy (Cmin)</th>
                                <th>Thanh Thải CrCl</th>
                                <th>Mục Tiêu Trị Liệu</th>
                                <th>Đánh Giá An Toàn</th>
                                <th class="text-end pe-3">Dược Sĩ Khuyến Cáo</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($tdmLogs as $tdm)
                            <tr class="{{ !$tdm->is_within_target ? 'table-warning' : '' }}">
                                <td>{{ $tdm->created_at->format('H:i d/m') }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tdm->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $tdm->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-primary font-monospace fs-6">{{ $tdm->medication_name }}</strong></td>
                                <td><span class="fs-6 fw-bold {{ !$tdm->is_within_target ? 'text-danger' : 'text-success' }}">{{ $tdm->trough_concentration_cmin }}</span></td>
                                <td>{{ $tdm->creatinine_clearance_crcl }} mL/phút</td>
                                <td><span class="badge bg-light text-dark border">{{ $tdm->target_range }}</span></td>
                                <td>
                                    @if($tdm->is_within_target)
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i>ĐẠT MỤC TIÊU</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-exclamation-circle me-1"></i>CẦN CHỈNH LIỀU</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <span class="d-block text-dark fw-medium">{{ $tdm->dose_adjustment_recommendation }}</span>
                                    <small class="text-muted">DS: {{ $tdm->pharmacist_evaluator }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có kết quả giám sát TDM nào trong hệ thống.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. TPN PANE -->
        <div class="tab-pane fade" id="tpn-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bag-plus text-warning me-2"></i>Đơn Dinh Dưỡng Tĩnh Mạch Toàn Phần (Total Parenteral Nutrition - TPN)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Tổng Năng Lượng</th>
                                <th>Glucose (Đường)</th>
                                <th>Acid Amin (Đạm)</th>
                                <th>Lipid (Chất Béo)</th>
                                <th>Đường Truyền</th>
                                <th>Nguy Cơ Refeeding</th>
                                <th class="text-end pe-3">Chuyên Gia Dinh Dưỡng</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($tpnLogs as $tpn)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tpn->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $tpn->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-primary fs-6">{{ $tpn->total_calories_kcal }} kcal</strong></td>
                                <td>{{ $tpn->glucose_grams }} g</td>
                                <td>{{ $tpn->amino_acids_grams }} g</td>
                                <td>{{ $tpn->lipids_grams }} g</td>
                                <td>
                                    <span class="badge {{ $tpn->infusion_route === 'CENTRAL_LINE' ? 'bg-primary' : 'bg-info text-dark' }} rounded-pill">
                                        {{ $tpn->infusion_route === 'CENTRAL_LINE' ? 'Tĩnh mạch trung tâm' : 'Tĩnh mạch ngoại vi' }}
                                    </span>
                                </td>
                                <td>
                                    @if($tpn->refeeding_risk)
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i>NGUY CƠ CAO</span>
                                    @else
                                        <span class="badge bg-success rounded-pill">An toàn</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $tpn->clinical_dietitian }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có chỉ định dinh dưỡng tĩnh mạch TPN nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. ADR PANE -->
        <div class="tab-pane fade" id="adr-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-fill-exclamation text-danger me-2"></i>Báo Cáo Phản Ứng Có Hại Của Thuốc (ADR Report - Chuẩn Bộ Y Tế)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Thuốc Nghi Ngờ Gây ADR</th>
                                <th>Mô Tả Biểu Hiện Phản Ứng</th>
                                <th>Mức Độ Nặng</th>
                                <th>Thang Điểm Naranjo</th>
                                <th>Kết Luận Mối Liên Quan</th>
                                <th class="text-end pe-3">Người Báo Cáo</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($adrReports as $adr)
                            <tr class="table-danger">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $adr->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $adr->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-danger font-monospace fs-6">{{ $adr->suspected_medication }}</strong></td>
                                <td>{{ $adr->reaction_description }}</td>
                                <td>
                                    <span class="badge {{ $adr->severity_level === 'SEVERE_LIFE_THREATENING' ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $adr->severity_level }}
                                    </span>
                                </td>
                                <td><strong class="fs-6">{{ $adr->causality_naranjo_score }} / 13 đ</strong></td>
                                <td>
                                    <span class="badge bg-dark rounded-pill">{{ $adr->causality_category }}</span>
                                </td>
                                <td class="text-end pe-3">{{ $adr->reporter_name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không có biến cố phản ứng có hại của thuốc nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Ghi Nhận TDM -->
<div class="modal fade" id="newTdmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-pharmacy.tdm') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-speedometer me-2"></i>Đo Nồng Độ Thuốc Khoảng Trị Liệu Hẹp (TDM)</h6>
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
                            <label class="form-label fw-semibold">Tên Thuốc Cần Giám Sát <span class="text-danger">*</span></label>
                            <input type="text" name="medication_name" class="form-control form-control-sm font-monospace fw-bold" value="Vancomycin" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Khoảng Mục Tiêu</label>
                            <input type="text" name="target_range" class="form-control form-control-sm" value="15.0 - 20.0 mg/L" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Nồng độ đáy Cmin (mg/L) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="trough_concentration_cmin" class="form-control form-control-sm" value="17.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Độ thanh thải CrCl (mL/p) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="creatinine_clearance_crcl" class="form-control form-control-sm" value="65.0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Khuyến cáo chỉnh liều của Dược Sĩ Lâm Sàng <span class="text-danger">*</span></label>
                        <input type="text" name="dose_adjustment_recommendation" class="form-control form-control-sm" value="Duy trì liều 1000mg mỗi 12h truyền tĩnh mạch chậm." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Dược sĩ đánh giá</label>
                        <input type="text" name="pharmacist_evaluator" class="form-control form-control-sm" value="DS. Lâm Sàng Tâm Anh" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Lưu Đánh Giá TDM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Tính Dinh Dưỡng TPN -->
<div class="modal fade" id="newTpnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-pharmacy.tpn') }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold"><i class="bi bi-bag-plus me-2"></i>Lập Công Thức Túi Dinh Dưỡng TPN All-in-One</h6>
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
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tổng Năng Lượng (kcal) <span class="text-danger">*</span></label>
                            <input type="number" name="total_calories_kcal" class="form-control form-control-sm" value="1800" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Đường truyền</label>
                            <select name="infusion_route" class="form-select form-select-sm" required>
                                <option value="CENTRAL_LINE">Tĩnh mạch trung tâm (PICC/CVC)</option>
                                <option value="PERIPHERAL">Tĩnh mạch ngoại vi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold">Glucose (g)</label>
                            <input type="number" step="1" name="glucose_grams" class="form-control form-control-sm" value="250" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Acid Amin (g)</label>
                            <input type="number" step="1" name="amino_acids_grams" class="form-control form-control-sm" value="85" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Lipid (g)</label>
                            <input type="number" step="1" name="lipids_grams" class="form-control form-control-sm" value="50" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nguy cơ Hội chứng tái nuôi dưỡng (Refeeding Syndrome)</label>
                        <select name="refeeding_risk" class="form-select form-select-sm" required>
                            <option value="0">Không có nguy cơ (Bình thường)</option>
                            <option value="1">Cảnh báo nguy cơ cao (Cần bù Phospho/Kali trước)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Chuyên gia dinh dưỡng lâm sàng</label>
                        <input type="text" name="clinical_dietitian" class="form-control form-control-sm" value="BS. Dinh Dưỡng Hậu Phẫu" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3">Lưu Đơn TPN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Báo Cáo ADR -->
<div class="modal fade" id="newAdrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-pharmacy.adr') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-diamond me-2"></i>Báo Cáo Phản Ứng Có Hại Của Thuốc (ADR)</h6>
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
                        <label class="form-label fw-semibold">Thuốc nghi ngờ gây biến cố <span class="text-danger">*</span></label>
                        <input type="text" name="suspected_medication" class="form-control form-control-sm font-monospace text-danger fw-bold" value="Augmentin (Amoxicillin/Clavulanate) 1g" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả triệu chứng lâm sàng <span class="text-danger">*</span></label>
                        <textarea name="reaction_description" class="form-control form-control-sm" rows="2" placeholder="Nổi ban dạng sởi, ngứa dữ dội, men gan ALT tăng gấp 3 lần..." required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mức độ nghiêm trọng</label>
                            <select name="severity_level" class="form-select form-select-sm" required>
                                <option value="MODERATE">Trung bình</option>
                                <option value="MILD">Nhẹ</option>
                                <option value="SEVERE_LIFE_THREATENING">Nặng đe dọa tính mạng</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thang điểm Naranjo (0-13)</label>
                            <input type="number" name="causality_naranjo_score" class="form-control form-control-sm" value="7" min="0" max="13" required>
                            <small class="text-muted">>= 5: Có khả năng cao</small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Người lập báo cáo</label>
                        <input type="text" name="reporter_name" class="form-control form-control-sm" value="DS. Bệnh Viện Tâm Anh" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Gửi Báo Cáo DI & ADR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
