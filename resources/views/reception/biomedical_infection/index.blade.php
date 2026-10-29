@extends('layouts.admin')

@section('title', 'Kỹ Thuật Y Sinh & Kiểm Soát Nhiễm Khuẩn')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-tools text-primary me-2"></i>Kỹ Thuật Y Sinh CMMS & Kiểm Soát Nhiễm Khuẩn (HAI - ASP)
            </h4>
            <p class="text-muted small mb-0">
                Vòng đời máy y tế NĐ 98/2021, báo hỏng SOS SLA 5p, giám sát nhiễm khuẩn VAP/CLABSI/CAUTI & Duyệt kháng sinh dự trữ
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newHaiModal">
                <i class="bi bi-shield-exclamation me-1"></i> Báo Ca Nhiễm Khuẩn HAI
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newAspModal">
                <i class="bi bi-capsule me-1"></i> Duyệt Kháng Sinh Dự Trữ (ASP)
            </button>
            <button type="button" class="btn btn-danger rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newSosRepairModal">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Báo Hỏng Máy SOS
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newEquipmentModal">
                <i class="bi bi-plus-circle me-1"></i> Thêm Thiết Bị Y Tế
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="bioTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill small fw-semibold" id="eq-tab" data-bs-toggle="pill" data-bs-target="#eq-pane" type="button">
                <i class="bi bi-cpu me-1 text-primary"></i> 1. Danh Mục Thiết Bị CMMS ({{ $equipments->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="repair-tab" data-bs-toggle="pill" data-bs-target="#repair-pane" type="button">
                <i class="bi bi-wrench-adjustable me-1 text-danger"></i> 2. Phiếu Báo Hỏng SOS SLA ({{ $repairRequests->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="hai-tab" data-bs-toggle="pill" data-bs-target="#hai-pane" type="button">
                <i class="bi bi-bug me-1 text-warning"></i> 3. Giám Sát Nhiễm Khuẩn HAI ({{ $haiInfections->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill small fw-semibold" id="asp-tab" data-bs-toggle="pill" data-bs-target="#asp-pane" type="button">
                <i class="bi bi-capsule-pill me-1 text-success"></i> 4. Quản Lý Kháng Sinh Dự Trữ ({{ $antimicrobialApprovals->total() }})
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="bioTabsContent">
        <!-- 1. EQUIPMENT CMMS PANE -->
        <div class="tab-pane fade show active" id="eq-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-card-checklist text-primary me-2"></i>Hồ Sơ Quản Lý Thiết Bị Y Tế & Hạn Kiểm Định (NĐ 98/2021)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Mã / Phân Cấp</th>
                                <th>Tên Thiết Bị</th>
                                <th>Model / Hãng SX</th>
                                <th>Khoa Phòng Vận Hành</th>
                                <th>Trạng Thái Vận Hành</th>
                                <th>Hạn Kiểm Định (Due Date)</th>
                                <th class="text-end pe-3">Yêu Cầu Sửa</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($equipments as $e)
                            <tr>
                                <td>
                                    <strong class="font-monospace text-dark d-block">{{ $e->equipment_code }}</strong>
                                    <span class="badge {{ $e->risk_class === 'CLASS_D' ? 'bg-danger' : ($e->risk_class === 'CLASS_C' ? 'bg-warning text-dark' : 'bg-info text-dark') }} rounded-pill" style="font-size: 0.65rem;">
                                        {{ $e->risk_class }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $e->name }}</div>
                                    <small class="text-muted">S/N: {{ $e->serial_number ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $e->model ?? 'Standard' }} • <span class="text-muted">{{ $e->manufacturer ?? 'OEM' }}</span></td>
                                <td><span class="badge bg-light text-dark border">{{ $e->department }}</span></td>
                                <td>
                                    @php
                                        $statusBadge = match($e->operational_status) {
                                            'ready' => 'bg-success',
                                            'in_use' => 'bg-primary',
                                            'under_repair' => 'bg-danger',
                                            'out_of_service' => 'bg-secondary',
                                            default => 'bg-dark'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }} rounded-pill px-2 py-1">
                                        {{ strtoupper($e->operational_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($e->is_calibration_expired)
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i>QUÁ HẠN KIỂM ĐỊNH</span>
                                    @else
                                        <span class="text-dark fw-medium">{{ $e->next_calibration_due ? $e->next_calibration_due->format('d/m/Y') : 'N/A' }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-secondary rounded-pill">{{ $e->repair_requests_count }} phiếu</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có thiết bị y tế nào được đăng ký.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. REPAIR SOS PANE -->
        <div class="tab-pane fade" id="repair-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-wrench-adjustable text-danger me-2"></i>Phiếu Báo Hỏng Thiết Bị Y Sinh SOS Có SLA</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Thời Điểm</th>
                                <th>Thiết Bị Hỏng</th>
                                <th>Mức Độ Cấp Cứu (SLA)</th>
                                <th>Mô Tả Hư Hỏng</th>
                                <th>Người Báo</th>
                                <th>Trạng Thái Xử Lý</th>
                                <th class="text-end pe-3">Kỹ Sư Phụ Trách</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($repairRequests as $rr)
                            <tr class="{{ $rr->urgency === 'CRITICAL_LIFE_SAVING' ? 'table-danger' : '' }}">
                                <td>{{ $rr->created_at->format('H:i d/m') }}</td>
                                <td>
                                    <strong class="text-dark">{{ $rr->equipment->name ?? 'Thiết bị' }}</strong>
                                    <small class="d-block text-muted font-monospace">{{ $rr->equipment->equipment_code ?? '' }}</small>
                                </td>
                                <td>
                                    @if($rr->urgency === 'CRITICAL_LIFE_SAVING')
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-alarm me-1"></i>SLA 5 PHÚT (CỨU SINH)</span>
                                    @elseif($rr->urgency === 'URGENT')
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">SLA 30 PHÚT</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Tiêu chuẩn</span>
                                    @endif
                                </td>
                                <td>{{ $rr->fault_description }}</td>
                                <td>{{ $rr->reporter_name }}</td>
                                <td>
                                    <span class="badge {{ $rr->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ strtoupper($rr->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">{{ $rr->engineer_name ?? 'Đang điều phối' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không có thiết bị y tế nào đang báo hỏng. Hệ thống vận hành tốt 100%!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. HAI INFECTIONS PANE -->
        <div class="tab-pane fade" id="hai-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-slash text-warning me-2"></i>Giám Sát 4 Loại Nhiễm Khuẩn Bệnh Viện (VAP, CLABSI, CAUTI, SSI)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Loại Nhiễm Khuẩn</th>
                                <th>Vi Khuẩn Phát Hiện</th>
                                <th>Khoa Phòng Phát Sinh</th>
                                <th>Chế Độ Cách Ly (Containment)</th>
                                <th>Ngày Phát Hiện</th>
                                <th class="text-end pe-3">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($haiInfections as $hai)
                            <tr class="table-warning">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $hai->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $hai->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><span class="badge bg-danger rounded-pill">{{ $hai->infection_type }}</span></td>
                                <td><strong class="text-danger font-monospace">{{ $hai->pathogen_detected }}</strong></td>
                                <td>{{ $hai->department }}</td>
                                <td>
                                    <span class="badge bg-dark rounded-pill px-2 py-1">
                                        <i class="bi bi-person-x me-1"></i>{{ $hai->containment_action }}
                                    </span>
                                </td>
                                <td>{{ $hai->identified_date ? $hai->identified_date->format('d/m/Y') : '' }}</td>
                                <td class="text-end pe-3">
                                    <span class="badge {{ $hai->is_resolved ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $hai->is_resolved ? 'ĐÃ KHỐNG CHẾ' : 'ĐANG CÁCH LY' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không phát hiện ca nhiễm khuẩn bệnh viện nào. Khoa KSNK kiểm soát tuyệt đối!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. ASP ANTIMICROBIAL PANE -->
        <div class="tab-pane fade" id="asp-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-capsule text-success me-2"></i>Quy Trình Phê Duyệt Kháng Sinh Dự Trữ (Antimicrobial Stewardship - ASP)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-secondary">
                            <tr>
                                <th>Bệnh Nhân</th>
                                <th>Kháng Sinh Đề Xuất</th>
                                <th>Phân Nhóm Tầng</th>
                                <th>Bác Sĩ Kê Đơn</th>
                                <th>Chỉ Định Lâm Sàng</th>
                                <th>Dược Sĩ LS Duyệt</th>
                                <th>Bác Sĩ Vi Sinh Duyệt</th>
                                <th class="text-end pe-3">Trạng Thái Cấp Thuốc</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($antimicrobialApprovals as $asp)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $asp->patient->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $asp->patient->patient_code ?? '' }}</small>
                                </td>
                                <td><strong class="text-primary font-monospace fs-6">{{ $asp->antibiotic_name }}</strong></td>
                                <td>
                                    <span class="badge {{ $asp->tier_level === 'TIER_3_RESERVE' ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $asp->tier_level }}
                                    </span>
                                </td>
                                <td>{{ $asp->prescribing_doctor }}</td>
                                <td>{{ $asp->clinical_indication }}</td>
                                <td>
                                    @if($asp->pharmacist_approver)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-check-circle me-1"></i>{{ $asp->pharmacist_approver }}</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Chờ ký</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asp->microbiologist_approver)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-check-circle me-1"></i>{{ $asp->microbiologist_approver }}</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Chờ ký</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge {{ $asp->approval_status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-2 py-1">
                                        {{ $asp->approval_status === 'approved' ? 'ĐÃ DUYỆT XUẤT KHO' : 'CHỜ DUYỆT KÉP' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có yêu cầu duyệt kháng sinh dự trữ nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Thêm Thiết Bị Y Tế -->
<div class="modal fade" id="newEquipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.biomedical-infection.equipment') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Đăng Ký Thiết Bị Y Tế Mới (Nghị định 98/2021)</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mã Thiết Bị (Code) <span class="text-danger">*</span></label>
                            <input type="text" name="equipment_code" class="form-control form-control-sm" value="MED-VENT-04" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Phân Cấp Rủi Ro</label>
                            <select name="risk_class" class="form-select form-select-sm" required>
                                <option value="CLASS_D">Loại D (Rủi ro rất cao - Máy thở, Sốc tim)</option>
                                <option value="CLASS_C">Loại C (Rủi ro cao - X-Quang, CT, MRI)</option>
                                <option value="CLASS_B">Loại B (Rủi ro trung bình)</option>
                                <option value="CLASS_A">Loại A (Rủi ro thấp)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên Thiết Bị Y Tế <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" value="Máy Thở Cao Cấp Hamilton-G5" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Model</label>
                            <input type="text" name="model" class="form-control form-control-sm" value="Hamilton-G5 Intelligent">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Số Serial (S/N)</label>
                            <input type="text" name="serial_number" class="form-control form-control-sm" value="HG5-2024-998">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Hãng Sản Xuất</label>
                            <input type="text" name="manufacturer" class="form-control form-control-sm" value="Hamilton Medical (Thụy Sĩ)">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Khoa Phòng Sử Dụng</label>
                            <input type="text" name="department" class="form-control form-control-sm" value="Hồi Sức Tích Cực (ICU)" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Ngày Kiểm Định Gần Nhất</label>
                            <input type="date" name="last_calibration_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Hạn Kiểm Định Tiếp Theo <span class="text-danger">*</span></label>
                            <input type="date" name="next_calibration_due" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Lưu Thiết Bị</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Báo Hỏng Máy SOS -->
<div class="modal fade" id="newSosRepairModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.biomedical-infection.repair-sos') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-octagon-fill me-2"></i>Báo Hỏng Thiết Bị Y Sinh SOS</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thiết bị gặp sự cố <span class="text-danger">*</span></label>
                        <select name="equipment_id" class="form-select form-select-sm" required>
                            <option value="">-- Chọn thiết bị --</option>
                            @foreach($equipments as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->equipment_code }} - {{ $eq->name }} ({{ $eq->department }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mức độ khẩn cấp (SLA)</label>
                        <select name="urgency" class="form-select form-select-sm" required>
                            <option value="CRITICAL_LIFE_SAVING">CỨU SINH KHẨN CẤP (SLA 5 Phút - Máy thở, Sốc tim, Monitor ICU)</option>
                            <option value="URGENT">Khẩn cấp (SLA 30 Phút)</option>
                            <option value="STANDARD">Bình thường trong ca trực</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả hiện tượng lỗi <span class="text-danger">*</span></label>
                        <textarea name="fault_description" class="form-control form-control-sm" rows="2" placeholder="Báo động áp lực đường thở cao, tụt nguồn đột ngột, lỗi sensor oxy..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Điều dưỡng / Bác sĩ phát hiện</label>
                        <input type="text" name="reporter_name" class="form-control form-control-sm" value="ĐD. Trưởng Tua Cấp Cứu" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Phát Lệnh SOS Kỹ Sư</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Báo Ca Nhiễm Khuẩn HAI -->
<div class="modal fade" id="newHaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.biomedical-infection.hai') }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Báo Cáo Nhiễm Khuẩn Bệnh Viện (HAI)</h6>
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
                            <label class="form-label fw-semibold">Loại nhiễm khuẩn</label>
                            <select name="infection_type" class="form-select form-select-sm" required>
                                <option value="VAP">VAP - Viêm phổi thở máy</option>
                                <option value="CLABSI">CLABSI - Nhiễm trùng huyết Catheter trung tâm</option>
                                <option value="CAUTI">CAUTI - Nhiễm trùng thông tiểu</option>
                                <option value="SSI">SSI - Nhiễm trùng vết mổ phẫu thuật</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Khoa phòng</label>
                            <input type="text" name="department" class="form-control form-control-sm" value="Khoa Hồi Sức Tích Cực (ICU)" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chủng vi khuẩn định danh</label>
                        <input type="text" name="pathogen_detected" class="form-control form-control-sm" value="Acinetobacter baumannii kháng đa thuốc (MDR)" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Chế độ phòng ngừa cách ly</label>
                        <select name="containment_action" class="form-select form-select-sm" required>
                            <option value="CONTACT_ISOLATION">Cách ly tiếp xúc (Contact Isolation - Găng tay, Áo choàng)</option>
                            <option value="DROPLET_ISOLATION">Cách ly giọt bắn (Droplet Isolation)</option>
                            <option value="AIRBORNE_ISOLATION">Cách ly không khí áp lực âm (Airborne Isolation)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3">Kích Hoạt Cách Ly</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Duyệt Kháng Sinh Dự Trữ ASP -->
<div class="modal fade" id="newAspModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.biomedical-infection.asp') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-capsule me-2"></i>Phiếu Phê Duyệt Kháng Sinh Dự Trữ (ASP)</h6>
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
                            <label class="form-label fw-semibold">Tên Kháng Sinh Đề Xuất <span class="text-danger">*</span></label>
                            <input type="text" name="antibiotic_name" class="form-control form-control-sm font-monospace fw-bold text-primary" value="Colistin (Polymyxin E) 1.000.000 UI" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Phân Tầng</label>
                            <select name="tier_level" class="form-select form-select-sm" required>
                                <option value="TIER_3_RESERVE">Tầng 3 (Dự trữ)</option>
                                <option value="TIER_2_RESTRICTED">Tầng 2 (Hạn chế)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bác sĩ kê đơn</label>
                        <input type="text" name="prescribing_doctor" class="form-control form-control-sm" value="BS. Nguyễn Văn Hưng" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chỉ định lâm sàng & Bằng chứng vi sinh <span class="text-danger">*</span></label>
                        <textarea name="clinical_indication" class="form-control form-control-sm" rows="2" placeholder="Nhiễm khuẩn huyết Gram âm đa kháng, thất bại với Carbapenem..." required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Dược Sĩ Lâm Sàng Duyệt</label>
                            <input type="text" name="pharmacist_approver" class="form-control form-control-sm" value="DS. Đỗ Thị Thu Trang">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Bác Sĩ Vi Sinh Duyệt</label>
                            <input type="text" name="microbiologist_approver" class="form-control form-control-sm" value="BS. Phạm Minh Tuấn">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Gửi Duyệt Kép</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
