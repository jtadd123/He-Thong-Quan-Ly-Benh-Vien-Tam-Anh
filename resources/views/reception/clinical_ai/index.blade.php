@extends('layouts.admin')

@section('title', 'AI Lâm Sàng & An Toàn Sinh Học')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-cpu text-info me-2"></i>AI Lâm Sàng, PACS Triage & An Toàn Sinh Học
            </h4>
            <p class="text-muted small mb-0">
                AI phân tích X-quang/CT đôn đọc khẩn cấp, dự báo sốc nhiễm khuẩn qSOFA, bản đồ gen dược lý PGx & Hemovigilance
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newTransfusionReactionModal">
                <i class="bi bi-droplet-half me-1"></i> Báo Cáo Tai Biến Máu
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newQsofaModal">
                <i class="bi bi-activity me-1"></i> Chấm Điểm qSOFA Sepsis
            </button>
            <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newPgxModal">
                <i class="bi bi-dna me-1"></i> Nhập Bản Đồ Gen PGx
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newPacsAiModal">
                <i class="bi bi-robot me-1"></i> Nạp Ảnh PACS Quét AI
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="aiTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill small fw-semibold" id="pacs-tab" data-bs-toggle="pill" data-bs-target="#pacs-pane" type="button">
                <i class="bi bi-file-earmark-medical me-1 text-primary"></i> 1. AI Đọc Phim PACS ({{ $pacsAnalyses->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="sepsis-tab" data-bs-toggle="pill" data-bs-target="#sepsis-pane" type="button">
                <i class="bi bi-virus me-1 text-danger"></i> 2. Dự Báo Sốc qSOFA ({{ $sepsisAlerts->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="pgx-tab" data-bs-toggle="pill" data-bs-target="#pgx-pane" type="button">
                <i class="bi bi-clipboard2-pulse me-1 text-success"></i> 3. Bản Đồ Gen PGx ({{ $genomicProfiles->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="transfusion-tab" data-bs-toggle="pill" data-bs-target="#transfusion-pane" type="button">
                <i class="bi bi-droplet-half me-1 text-danger"></i> 4. Tai Biến Truyền Máu ({{ $reactionReports->total() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="aiTabsContent">
        <!-- 1. AI PACS PANE -->
        <div class="tab-pane fade show active" id="pacs-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-robot text-primary me-2"></i>Kết Quả AI Computer Vision Quét Phim Chẩn Đoán Hình Ảnh</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã Phân Tích</th>
                                <th>Bệnh Nhân</th>
                                <th>Kỹ Thuật / Vị Trí</th>
                                <th>AI Phát Hiện Tổn Thương</th>
                                <th>Độ Tin Cậy AI</th>
                                <th>Mức Độ Cấp Cứu</th>
                                <th>Đôn Đọc Khẩn</th>
                                <th class="text-end pe-3">Thời Gian</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($pacsAnalyses as $p)
                            <tr class="{{ $p->is_prioritized ? 'table-danger' : '' }}">
                                <td class="font-monospace fw-bold text-dark">{{ $p->analysis_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $p->patient->patient_code ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">{{ $p->modality }}</span>
                                    <span class="text-dark fw-medium ms-1">{{ $p->body_part }}</span>
                                </td>
                                <td>
                                    <strong class="{{ $p->is_prioritized ? 'text-danger' : 'text-dark' }}">{{ $p->finding_detected }}</strong>
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px; width: 80px;">
                                        <div class="progress-bar {{ $p->confidence_score >= 90 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $p->confidence_score }}%;"></div>
                                    </div>
                                    <small class="fw-bold">{{ $p->confidence_score }}%</small>
                                </td>
                                <td>
                                    <span class="badge {{ $p->triage_urgency === 'CRITICAL' ? 'bg-danger' : ($p->triage_urgency === 'URGENT' ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill">
                                        {{ $p->triage_urgency }}
                                    </span>
                                </td>
                                <td>
                                    @if($p->is_prioritized)
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-lightning-charge-fill me-1"></i>TOP 1 HÀNG ĐỢI</span>
                                    @else
                                        <span class="text-muted">Bình thường</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-secondary">{{ $p->created_at->format('H:i d/m') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu AI đọc phim PACS.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. SEPSIS QSOFA PANE -->
        <div class="tab-pane fade" id="sepsis-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-virus text-danger me-2"></i>Cảnh Báo Sớm Nguy Cơ Sốc Nhiễm Khuẩn (qSOFA Score)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Nhịp Thở (RR)</th>
                                <th>Thang Glasgow (GCS)</th>
                                <th>Huyết Áp Tâm Thu</th>
                                <th>Điểm qSOFA</th>
                                <th>Nguy Cơ Sốc</th>
                                <th>Báo Động ICU</th>
                                <th class="text-end pe-3">Người Đánh Giá</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($sepsisAlerts as $s)
                            <tr class="{{ $s->qsofa_score >= 2 ? 'table-warning' : '' }}">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $s->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $s->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="{{ $s->respiratory_rate >= 22 ? 'text-danger fw-bold' : '' }}">{{ $s->respiratory_rate }} l/p</span></td>
                                <td><span class="{{ $s->glasgow_coma_scale < 15 ? 'text-danger fw-bold' : '' }}">{{ $s->glasgow_coma_scale }}/15 đ</span></td>
                                <td><span class="{{ $s->systolic_bp <= 100 ? 'text-danger fw-bold' : '' }}">{{ $s->systolic_bp }} mmHg</span></td>
                                <td>
                                    <span class="badge {{ $s->qsofa_score >= 2 ? 'bg-danger' : ($s->qsofa_score == 1 ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill px-2 py-1 fs-6">
                                        {{ $s->qsofa_score }} / 3 đ
                                    </span>
                                </td>
                                <td>
                                    @if($s->risk_level === 'HIGH_RISK_SHOCK')
                                        <span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>NGUY CƠ SỐC CAO</span>
                                    @elseif($s->risk_level === 'MODERATE')
                                        <span class="text-warning fw-semibold">Theo dõi sát</span>
                                    @else
                                        <span class="text-success">Nguy cơ thấp</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->icu_notified)
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-bell-fill me-1"></i>ĐÃ BÁO ICU</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Nội trú</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">{{ $s->assessed_by }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có lượt đánh giá qSOFA nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. PHARMACOGENOMICS PANE -->
        <div class="tab-pane fade" id="pgx-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-dna text-success me-2"></i>Bản Đồ Gen Y Học Chính Xác (Pharmacogenomics - PGx)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Gen Liên Quan</th>
                                <th>Kiểu Gen (Genotype)</th>
                                <th>Kiểu Hình (Phenotype)</th>
                                <th>Thuốc Chống Chỉ Định</th>
                                <th>Thuốc Thay Thế Khuyến Cáo</th>
                                <th class="text-end pe-3">Căn Cứ Hướng Dẫn</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($genomicProfiles as $g)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $g->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $g->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="badge bg-primary rounded-pill font-monospace">{{ $g->gene_name }}</span></td>
                                <td class="font-monospace fw-semibold">{{ $g->genotype }}</td>
                                <td><span class="text-warning-emphasis fw-medium">{{ $g->phenotype }}</span></td>
                                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">{{ $g->contraindicated_medication }}</span></td>
                                <td><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $g->recommended_alternative }}</span></td>
                                <td class="text-end pe-3 text-muted">{{ $g->clinical_guideline_source ?? 'CPIC Guidelines' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có hồ sơ gen dược lý nào được lưu.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. TRANSFUSION REACTION PANE -->
        <div class="tab-pane fade" id="transfusion-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-fill-exclamation text-danger me-2"></i>Báo Cáo Tai Biến Truyền Máu Cấp (Hemovigilance)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Thời Điểm</th>
                                <th>Bệnh Nhân</th>
                                <th>Túi Máu Liên Quan</th>
                                <th>Loại Phản Ứng</th>
                                <th>Triệu Chứng Lâm Sàng</th>
                                <th>Dừng Truyền / Niêm Phong</th>
                                <th class="text-end pe-3">Bác Sĩ Báo Cáo</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($reactionReports as $r)
                            <tr class="table-danger">
                                <td>{{ $r->onset_time ? $r->onset_time->format('H:i d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $r->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="font-monospace text-primary">{{ $r->bloodUnit->unit_code ?? 'Túi Máu' }}</strong></td>
                                <td><span class="badge bg-danger rounded-pill">{{ $r->reaction_type }}</span></td>
                                <td>{{ $r->symptoms }}</td>
                                <td>
                                    <span class="badge bg-dark rounded-pill"><i class="bi bi-lock-fill me-1"></i>ĐÃ NIÊM PHONG</span>
                                </td>
                                <td class="text-end pe-3">{{ $r->reporting_physician }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không có tai biến truyền máu nào được ghi nhận. Toàn bộ các ca truyền máu đều an toàn!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Nạp Ảnh PACS Quét AI -->
<div class="modal fade" id="newPacsAiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-ai.pacs') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-robot me-2"></i>Nạp Kết Quả AI Quét Phim PACS</h6>
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
                        <div class="col-6">
                            <label class="form-label fw-semibold">Kỹ thuật (Modality)</label>
                            <select name="modality" class="form-select form-select-sm" required>
                                <option value="X-RAY">X-Quang Kỹ thuật số</option>
                                <option value="CT">Cắt lớp vi tính (CT)</option>
                                <option value="MRI">Cộng hưởng từ (MRI)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Vị trí chụp (Body Part)</label>
                            <input type="text" name="body_part" class="form-control form-control-sm" value="Lồng Ngực (Chest AP)" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">AI Phát hiện tổn thương <span class="text-danger">*</span></label>
                        <input type="text" name="finding_detected" class="form-control form-control-sm" value="Tràn khí màng phổi áp lực bên phải (Pneumothorax)" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Độ tin cậy AI (%)</label>
                            <input type="number" step="0.1" name="confidence_score" class="form-control form-control-sm" value="96.5" min="0" max="100" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mức độ cấp bách</label>
                            <select name="triage_urgency" class="form-select form-select-sm" required>
                                <option value="CRITICAL">CẤP CỨU NGUY KỊCH (Đôn Top 1)</option>
                                <option value="URGENT">Khẩn cấp</option>
                                <option value="ROUTINE">Thường quy</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Lưu & Triage AI</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Chấm Điểm qSOFA Sepsis -->
<div class="modal fade" id="newQsofaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-ai.sepsis') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-activity me-2"></i>Đánh Giá Nguy Cơ Sốc Nhiễm Khuẩn (qSOFA)</h6>
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
                        <label class="form-label fw-semibold">1. Tần số thở (Respiratory Rate - bpm)</label>
                        <input type="number" name="respiratory_rate" class="form-control form-control-sm" value="24" required>
                        <small class="text-muted">>= 22 nhịp/phút tính 1 điểm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">2. Thang điểm Glasgow (GCS - Điểm)</label>
                        <input type="number" name="glasgow_coma_scale" class="form-control form-control-sm" value="13" min="3" max="15" required>
                        <small class="text-muted">< 15 điểm tính 1 điểm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">3. Huyết áp tâm thu (Systolic BP - mmHg)</label>
                        <input type="number" name="systolic_bp" class="form-control form-control-sm" value="95" required>
                        <small class="text-muted"><= 100 mmHg tính 1 điểm</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Điều dưỡng / Bác sĩ đánh giá</label>
                        <input type="text" name="assessed_by" class="form-control form-control-sm" value="BS. Trực Cấp Cứu A&E" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Tính Điểm & Báo Sepsis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Bản Đồ Gen PGx -->
<div class="modal fade" id="newPgxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-ai.pharmacogenomic') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-dna me-2"></i>Thêm Bản Đồ Gen Dược Lý (PGx)</h6>
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
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tên Gen (Gene)</label>
                            <input type="text" name="gene_name" class="form-control form-control-sm" value="CYP2C19" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Kiểu Gen (Genotype)</label>
                            <input type="text" name="genotype" class="form-control form-control-sm" value="*2/*2 (Đột biến mất hoạt tính)" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kiểu hình chuyển hóa (Phenotype)</label>
                        <input type="text" name="phenotype" class="form-control form-control-sm" value="Chuyển hóa kém (Poor Metabolizer)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thuốc chống chỉ định (Cấm kê) <span class="text-danger">*</span></label>
                        <input type="text" name="contraindicated_medication" class="form-control form-control-sm text-danger fw-bold" value="Clopidogrel (Plavix)" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Thuốc thay thế an toàn khuyến cáo</label>
                        <input type="text" name="recommended_alternative" class="form-control form-control-sm text-success fw-bold" value="Prasugrel hoặc Ticagrelor (Brilinta)" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Lưu Bản Đồ Gen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Báo Cáo Tai Biến Truyền Máu -->
<div class="modal fade" id="newTransfusionReactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.clinical-ai.transfusion-reaction') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-shield-fill-exclamation me-2"></i>Báo Cáo Tai Biến Truyền Máu Khẩn Cấp</h6>
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
                        <label class="form-label fw-semibold">Túi máu đang truyền <span class="text-danger">*</span></label>
                        <select name="blood_unit_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn túi máu --</option>
                            @foreach($bloodUnits as $bu)
                            <option value="{{ $bu->id }}">{{ $bu->unit_code }} (Nhóm {{ $bu->blood_type_abo }}{{ $bu->blood_type_rh === 'positive' ? '+' : '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Loại phản ứng bất lợi <span class="text-danger">*</span></label>
                        <select name="reaction_type" class="form-select form-select-sm" required>
                            <option value="FEBRILE">Sốt không do tan máu (Febrile Non-Hemolytic)</option>
                            <option value="ALLERGIC_ANAPHYLACTIC">Dị ứng mày đay / Phản vệ</option>
                            <option value="ACUTE_HEMOLYTIC">Tan máu cấp do bất đồng nhóm máu</option>
                            <option value="TRALI">Tổn thương phổi cấp do truyền máu (TRALI)</option>
                            <option value="TACO">Quá tải tuần hoàn do truyền máu (TACO)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Triệu chứng lâm sàng ghi nhận <span class="text-danger">*</span></label>
                        <textarea name="symptoms" class="form-control form-control-sm" rows="2" placeholder="Sốt rét run, khó thở, SpO2 tụt, nổi mề đay toàn thân..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Bác sĩ phụ trách báo cáo</label>
                        <input type="text" name="reporting_physician" class="form-control form-control-sm" value="BS. Điều Trị Lâm Sàng" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Dừng Truyền & Niêm Phong Ngay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
