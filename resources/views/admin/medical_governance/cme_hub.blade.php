@extends('layouts.admin')

@section('title', 'Trung Tâm Đào Tạo Y Khoa Liên Tục CME - Đánh Giá Năng Lực Bác Sĩ')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.medical-governance.index') }}">Quản Trị Y Khoa</a></li>
                    <li class="breadcrumb-item active">Trung Tâm Đào Tạo Y Khoa CME</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Trung Tâm Đào Tạo Y Khoa Liên Tục (CME Hub) & Ngân Hàng Ca Lâm Sàng</h3>
            <p class="text-muted small mb-0">Hệ thống khảo sát tình huống thực tế, tích lũy giờ tín chỉ đào tạo liên tục theo quy định Luật Khám bệnh, chữa bệnh 2023</p>
        </div>
        <div>
            <a href="{{ route('admin.medical-governance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-primary border-4">
                <div class="small text-muted text-uppercase fw-bold">Tổng Chuyên Đề Đào Tạo</div>
                <h4 class="fw-bold text-primary mb-0 mt-1">{{ number_format($totalModules) }} Chuyên đề</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-success border-4">
                <div class="small text-muted text-uppercase fw-bold">Tổng Tình Huống Lâm Sàng (Vignettes)</div>
                <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($totalVignettes) }} Ca bệnh</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-warning border-4">
                <div class="small text-muted text-uppercase fw-bold">Quy Chuẩn Giờ Tín Chỉ Bắt Buộc</div>
                <h4 class="fw-bold text-dark mb-0 mt-1">48 Giờ / Năm / Bác sĩ</h4>
            </div>
        </div>
    </div>

    <!-- List of Modules and Case Samples -->
    <div class="row g-4">
        @foreach(array_slice($allCases, 0, 4) as $specSlug => $specData)
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-mortarboard-fill me-2"></i> {{ $specData['specialty_title'] }}
                        </h6>
                        <span class="badge bg-white text-primary rounded-pill">{{ count($specData['modules']) }} Chuyên đề CME</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="accordion" id="accordion-{{ $specSlug }}">
                            @foreach(array_slice($specData['modules'], 0, 3) as $modId => $mod)
                                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                                    <h2 class="accordion-header" id="heading-{{ $modId }}">
                                        <button class="accordion-button collapsed fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $modId }}">
                                            <span class="badge bg-primary-subtle text-primary me-2">{{ $modId }}</span>
                                            {{ $mod['module_title'] }}
                                            <span class="badge bg-success-subtle text-success ms-auto me-3">{{ $mod['cme_credits'] }} Tín chỉ CME</span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $modId }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $specSlug }}">
                                        <div class="accordion-body bg-light bg-opacity-50 p-4">
                                            @foreach(array_slice($mod['vignettes'], 0, 2) as $vId => $v)
                                                <div class="card border-0 rounded-3 shadow-sm mb-3">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="badge bg-dark font-monospace">{{ $vId }}</span>
                                                            <span class="small text-muted">{{ $v['patient_profile'] }}</span>
                                                        </div>
                                                        <p class="small text-muted mb-2"><strong>Bệnh sử:</strong> {{ $v['history'] }}</p>
                                                        <div class="p-2 bg-light rounded small mb-2">
                                                            <div><strong>Sinh hiệu:</strong> {{ $v['physical_exam']['vitals'] }}</div>
                                                            <div><strong>Khám:</strong> {{ $v['physical_exam']['cardiopulmonary'] }}</div>
                                                        </div>
                                                        <div class="fw-semibold text-primary small mb-2">
                                                            <i class="bi bi-question-circle me-1"></i> {{ $v['clinical_question'] }}
                                                        </div>
                                                        <div class="list-group list-group-flush small">
                                                            @foreach($v['options'] as $key => $opt)
                                                                <div class="list-group-item px-2 py-1 border-0 {{ $key === $v['correct_option'] ? 'bg-success-subtle rounded text-success fw-bold' : 'text-muted' }}">
                                                                    <strong>{{ $key }}.</strong> {{ $opt }}
                                                                    @if($key === $v['correct_option'])
                                                                        <span class="badge bg-success float-end">Đáp án chuẩn</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
