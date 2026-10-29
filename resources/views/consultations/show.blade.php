@extends('layouts.admin')

@section('title', 'Phòng Hội Chẩn Trực Tuyến - ' . $consultation->consultation_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Trở lại danh sách
                </a>
                <span class="badge bg-secondary font-monospace">{{ $consultation->consultation_code }}</span>
                {!! $consultation->urgency_badge !!}
                {!! $consultation->status_badge !!}
            </div>
            <h4 class="fw-bold mb-0 text-slate-800">
                Hội Chẩn: {{ $consultation->patientProfile->full_name }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('consultations.print', $consultation) }}" target="_blank" class="btn btn-outline-dark d-inline-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> In Biên Bản Hội Chẩn
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Patient & Request Details -->
        <div class="col-lg-5">
            <!-- Patient Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-person-vcard text-primary me-2"></i>Hồ Sơ Bệnh Nhân</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold text-slate-900 mb-0">{{ $consultation->patientProfile->full_name }}</h5>
                            <small class="text-muted">Mã BN: <strong>{{ $consultation->patientProfile->patient_code }}</strong> • {{ $consultation->patientProfile->age_text }} • {{ $consultation->patientProfile->gender_name }}</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $consultation->patientProfile->phone }}</span>
                    </div>

                    @if($consultation->patientProfile->allergies)
                        <div class="alert alert-danger border-0 p-2 rounded small mb-3">
                            <i class="bi bi-shield-slash-fill me-1"></i><strong>Tiền sử dị ứng:</strong> {{ $consultation->patientProfile->allergies }}
                        </div>
                    @endif

                    <!-- Latest Vitals -->
                    @if($consultation->patientProfile->latestVital)
                        @php $v = $consultation->patientProfile->latestVital; @endphp
                        <div class="bg-light p-3 rounded-3 mb-3">
                            <span class="text-muted small fw-bold d-block mb-2 text-uppercase"><i class="bi bi-heart-pulse-fill text-danger me-1"></i>Sinh Hiệu Gần Nhất ({{ $v->recorded_at->format('H:i d/m') }})</span>
                            <div class="row g-2 text-center">
                                <div class="col-3">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Huyết áp</small>
                                        <strong class="text-danger fs-6">{{ $v->blood_pressure_systolic }}/{{ $v->blood_pressure_diastolic }}</strong>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Mạch</small>
                                        <strong class="text-primary fs-6">{{ $v->pulse }} <span style="font-size: 0.65rem;">l/p</span></strong>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Nhiệt độ</small>
                                        <strong class="text-warning fs-6">{{ $v->temperature }}°C</strong>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="bg-white p-2 rounded border">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">SpO2</small>
                                        <strong class="text-success fs-6">{{ $v->spo2 }}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Người gửi yêu cầu:</span>
                            <strong>{{ $consultation->requestingUser->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Khoa yêu cầu:</span>
                            <strong>{{ $consultation->requestingSpecialty?->name ?? 'Khoa Khám Bệnh' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Khoa được mời:</span>
                            <strong class="text-primary">{{ $consultation->targetSpecialty->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Thời điểm phát lệnh:</span>
                            <strong>{{ $consultation->created_at->format('H:i - d/m/Y') }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Request Content -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-chat-left-text text-primary me-2"></i>Nội Dung & Câu Hỏi Hội Chẩn</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block text-uppercase">Lý do mời hội chẩn:</small>
                        <p class="text-slate-800 fw-semibold mb-0 mt-1">{{ $consultation->reason }}</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block text-uppercase">Tóm tắt diễn biến lâm sàng:</small>
                        <div class="p-3 bg-light rounded-3 mt-1 small text-dark" style="white-space: pre-line;">
                            {{ $consultation->clinical_summary }}
                        </div>
                    </div>

                    <div>
                        <small class="text-danger fw-bold d-block text-uppercase">Vấn đề xin ý kiến chuyên gia:</small>
                        <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mt-1 small text-dark fw-semibold" style="white-space: pre-line;">
                            {{ $consultation->proposed_questions }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Consultation Response & Clinical Conclusion -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="bi bi-clipboard2-pulse text-success me-2"></i>Kết Luận & Ý Kiến Chuyên Môn Của Khoa Được Mời
                    </h6>
                    @if($consultation->status === 'completed')
                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Đã Hoàn Tất Hội Chẩn</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Đang Chờ Chuyên Gia Phản Hồi</span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if($consultation->status === 'completed')
                        <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 mb-4">
                            <h6 class="fw-bold text-success mb-2">
                                <i class="bi bi-check-all me-1"></i>KẾT LUẬN HỘI CHẨN CHUYÊN MÔN:
                            </h6>
                            <p class="text-slate-800 mb-0" style="white-space: pre-line;">
                                {{ $consultation->consultation_result }}
                            </p>
                        </div>

                        @if($consultation->treatment_recommendations)
                            <div class="p-3 bg-light rounded-3 mb-4">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="bi bi-prescription2 me-1"></i>KHUYẾN CÁO PHÁC ĐỒ PHỐI HỢP ĐIỀU TRỊ:
                                </h6>
                                <p class="text-slate-800 mb-0" style="white-space: pre-line;">
                                    {{ $consultation->treatment_recommendations }}
                                </p>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top text-muted small">
                            <div>
                                Bác sĩ hội chẩn: <strong>{{ $consultation->consultantDoctor?->user->name ?? 'BS Chuyên Khoa' }}</strong> ({{ $consultation->targetSpecialty->name }})
                            </div>
                            <div>
                                Thời gian ký duyệt: <strong>{{ $consultation->responded_at?->format('H:i - d/m/Y') }}</strong>
                            </div>
                        </div>

                    @else
                        <!-- Form for Doctor to respond -->
                        <form action="{{ route('consultations.respond', $consultation) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    1. Kết luận chẩn đoán và ý kiến chuyên môn <span class="text-danger">*</span>
                                </label>
                                <textarea name="consultation_result" class="form-control" rows="5" placeholder="Nhập kết luận chuyên môn sau khi xem xét hồ sơ, cận lâm sàng hoặc thăm khám trực tiếp bệnh nhân..." required>{{ old('consultation_result') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    2. Hướng xử trí & Phác đồ điều trị phối hợp
                                </label>
                                <textarea name="treatment_recommendations" class="form-control" rows="4" placeholder="Chỉ định dùng thuốc, liều lượng, xét nghiệm bổ sung, theo dõi sinh hiệu, hoặc chuyển khoa điều trị...">{{ old('treatment_recommendations') }}</textarea>
                            </div>

                            <div class="alert alert-info border-0 rounded-3 small mb-4">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Ý kiến hội chẩn sẽ được lưu trực tiếp vào bệnh án điện tử và làm căn cứ pháp lý để in Biên bản Hội chẩn chuẩn Bộ Y Tế.
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-4 fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i> Ký Duyệt & Gửi Kết Luận Hội Chẩn
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
