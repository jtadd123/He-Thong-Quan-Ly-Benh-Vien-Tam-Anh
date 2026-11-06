@extends('layouts.admin')

@section('title', 'Quản Trị Y Khoa & Đội Ngũ Lâm Sàng Quốc Tế')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-shield-check me-1"></i> JCI & Thông tư 50/BYT Compliant
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-stars me-1"></i> Module 2 Innovation — Nguyễn Văn Đạt
                </span>
            </div>
            <h2 class="fw-bold text-dark mb-1 mt-2">Hệ Sinh Thái Quản Trị Y Khoa & Đội Ngũ Lâm Sàng Quốc Tế</h2>
            <p class="text-muted small mb-0">Giám sát năng lực phẫu thuật, phác đồ điều trị lâm sàng, đào tạo liên tục CME và công trình nghiên cứu thử nghiệm y khoa Bệnh viện Đa khoa Tâm Anh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.medical-governance.surgical-catalog') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-journal-medical me-1"></i> Danh Mục Phẫu Thuật
            </a>
            <a href="{{ route('admin.medical-governance.cme-hub') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-mortarboard-fill me-1"></i> Đào Tạo CME Bác Sĩ
            </a>
        </div>
    </div>

    <!-- 4 Core Metric KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm bg-primary text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold">Danh Mục Kỹ Thuật BYT</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($surgicalCount) }}</h3>
                        <small class="text-white-50">Quy trình phẫu thuật phân hạng</small>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 fs-4">
                        <i class="bi bi-scissors"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-25 small d-flex justify-content-between">
                    <span>Đặc biệt: <strong>{{ number_format($classCounts['Đặc biệt'] ?? 0) }}</strong></span>
                    <span>Loại I: <strong>{{ number_format($classCounts['Loại I'] ?? 0) }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm bg-success text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold">Phác Đồ Điều Trị CPG</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($guidelinesCount) }}</h3>
                        <small class="text-white-50">Phác đồ chuẩn hóa chứng cứ loại A</small>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 fs-4">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-25 small d-flex justify-content-between">
                    <span>Cấp cứu Code-Red</span>
                    <span>Tuân thủ 100%</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm bg-info text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold">Ngân Hàng Ca Lâm Sàng CME</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($cmeCasesCount) }}</h3>
                        <small class="text-white-50">Tình huống đánh giá năng lực</small>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 fs-4">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-25 small d-flex justify-content-between">
                    <span>Tích lũy 48h/năm</span>
                    <span>Luật KCB 2023</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm bg-dark text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="small text-white-50 text-uppercase fw-bold">Công Trình NCKH Bác Sĩ</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($researchCount) }}</h3>
                        <small class="text-white-50">Bài báo PubMed / Scopus</small>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 fs-4">
                        <i class="bi bi-globe-americas"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-25 small d-flex justify-content-between">
                    <span>Thử nghiệm GCP</span>
                    <span>NEJM / Lancet / JCO</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Hub -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-shield-lock text-primary me-2"></i> Phân Quyền Kỹ Thuật Phẫu Thuật (Surgical Privileging)
                    </h5>
                    <span class="badge bg-primary rounded-pill">Thông tư 50/BYT</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small">
                        Hệ thống đối soát năng lực lâm sàng và cấp chứng chỉ đặc quyền phẫu thuật (*Clinical Privileging*). Bác sĩ phải hoàn thành tối thiểu các ca mổ có giám sát (*Proctored Cases*) để được duyệt mổ độc lập các phẫu thuật loại Đặc biệt và Loại I.
                    </p>
                    <div class="list-group list-group-flush rounded-3 border">
                        <a href="{{ route('admin.medical-governance.surgical-catalog') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-0 fw-semibold">Tra cứu 970 quy trình phẫu thuật chuyên khoa</h6>
                                <small class="text-muted">Phân loại kỹ thuật, thời gian mổ chuẩn, tiêu chuẩn học vị tối thiểu</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('admin.medical-governance.guidelines') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-0 fw-semibold">730 phác đồ điều trị lâm sàng chuẩn vàng (CPG)</h6>
                                <small class="text-muted">Tiêu chuẩn chẩn đoán, phác đồ bước 1, dấu hiệu cờ đỏ cấp cứu</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-cpu text-success me-2"></i> Đào Tạo Bác Sĩ & Điều Phối Chống Kiệt Sức
                    </h5>
                    <span class="badge bg-success rounded-pill">Burnout Prevention</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small">
                        Hệ sinh thái tự động kiểm soát chỉ số mệt mỏi sinh học (*Circadian Fatigue Index*), ngăn ngừa xếp lịch mổ phức tạp sau ca trực đêm, và ngân hàng khảo sát năng lực liên tục chuẩn hóa chuyên môn đội ngũ bác sĩ.
                    </p>
                    <div class="list-group list-group-flush rounded-3 border">
                        <a href="{{ route('admin.medical-governance.cme-hub') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-0 fw-semibold">Trung tâm Đào tạo CME & 840 Ca Lâm Sàng</h6>
                                <small class="text-muted">Bộ câu hỏi tình huống thực tế, cập nhật kiến thức y khoa liên tục</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('admin.medical-governance.research') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-0 fw-semibold">Kho 500 Công trình Nghiên cứu & Thử nghiệm Lâm sàng</h6>
                                <small class="text-muted">Chỉ số trích dẫn Scopus/ISI, thử nghiệm ngẫu nhiên đa trung tâm</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
