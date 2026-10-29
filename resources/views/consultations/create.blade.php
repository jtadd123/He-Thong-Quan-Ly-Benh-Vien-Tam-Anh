@extends('layouts.admin')

@section('title', 'Tạo Phiếu Yêu Cầu Hội Chẩn Đa Chuyên Khoa')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Trở lại danh sách
        </a>
        <h4 class="fw-bold mb-0 text-slate-800">
            Tạo Phiếu Yêu Cầu Hội Chẩn Chuyên Khoa Nội Viện
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-file-earmark-medical-fill me-2"></i>Phiếu Mời Hội Chẩn (Chuẩn Quy Định Bộ Y Tế)
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('consultations.store') }}" method="POST">
                @csrf

                @if($appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                    <input type="hidden" name="patient_profile_id" value="{{ $appointment->patient_profile_id }}">
                @endif
                @if($medicalRecord)
                    <input type="hidden" name="medical_record_id" value="{{ $medicalRecord->id }}">
                @endif

                <div class="row g-4">
                    <!-- Bệnh nhân -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bệnh nhân cần hội chẩn <span class="text-danger">*</span></label>
                        @if($patient)
                            <input type="hidden" name="patient_profile_id" value="{{ $patient->id }}">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-primary mb-1">{{ $patient->full_name }}</h6>
                                <p class="small text-muted mb-0">
                                    Mã BN: <strong>{{ $patient->patient_code }}</strong> • {{ $patient->age_text }} • {{ $patient->gender_name }} • SĐT: {{ $patient->phone }}
                                </p>
                            </div>
                        @else
                            <select name="patient_profile_id" class="form-select" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->full_name }} ({{ $p->patient_code }} - {{ $p->phone }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Mức độ khẩn cấp -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mức độ khẩn cấp (Emergency Priority) <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <label class="btn btn-outline-danger flex-grow-1 text-start p-2">
                                <input type="radio" name="urgency_level" value="stat" class="me-1" {{ old('urgency_level') == 'stat' ? 'checked' : '' }} required>
                                <strong class="d-block text-danger">🔴 TỐI KHẨN (STAT)</strong>
                                <small class="text-muted" style="font-size: 0.72rem;">Phản hồi trong 15 phút</small>
                            </label>
                            <label class="btn btn-outline-warning flex-grow-1 text-start p-2">
                                <input type="radio" name="urgency_level" value="urgent" class="me-1" {{ old('urgency_level') == 'urgent' ? 'checked' : '' }}>
                                <strong class="d-block text-warning-emphasis">🟠 KHẨN CẤP</strong>
                                <small class="text-muted" style="font-size: 0.72rem;">Phản hồi trong 1 giờ</small>
                            </label>
                            <label class="btn btn-outline-info flex-grow-1 text-start p-2">
                                <input type="radio" name="urgency_level" value="routine" class="me-1" {{ old('urgency_level', 'routine') == 'routine' ? 'checked' : '' }}>
                                <strong class="d-block text-info-emphasis">🟡 THƯỜNG QUY</strong>
                                <small class="text-muted" style="font-size: 0.72rem;">Hội chẩn trong ngày</small>
                            </label>
                        </div>
                    </div>

                    <!-- Chuyên khoa mời -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chuyên khoa được mời hội chẩn <span class="text-danger">*</span></label>
                        <select name="target_specialty_id" class="form-select" required>
                            <option value="">-- Chọn chuyên khoa mời --</option>
                            @foreach($specialties as $spec)
                                <option value="{{ $spec->id }}" {{ old('target_specialty_id') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Bác sĩ cụ thể (Tùy chọn) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bác sĩ chuyên khoa cụ thể (Tùy chọn)</label>
                        <select name="target_doctor_id" class="form-select">
                            <option value="">-- Mời bất kỳ bác sĩ trực của chuyên khoa --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('target_doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->user->name }} ({{ $doc->specialty->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lý do hội chẩn -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Lý do mời hội chẩn <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="VD: Bệnh nhân sốt cao kéo dài ngày thứ 4, giảm tiểu cầu kèm khó thở nhẹ; Đái tháo đường biến chứng bàn chân..." value="{{ old('reason') }}" required>
                    </div>

                    <!-- Tóm tắt lâm sàng -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tóm tắt diễn biến lâm sàng, sinh hiệu và kết quả cận lâm sàng</label>
                        <textarea name="clinical_summary" class="form-control" rows="4" placeholder="Nhập tóm tắt triệu chứng, huyết áp, mạch, SpO2, kết quả xét nghiệm máu/X-quang đã có...">{{ old('clinical_summary', $patient && $patient->latestVital ? 'Sinh hiệu: HA ' . $patient->latestVital->blood_pressure_systolic . '/' . $patient->latestVital->blood_pressure_diastolic . ' mmHg, Mạch ' . $patient->latestVital->pulse . ' l/p, SpO2 ' . $patient->latestVital->spo2 . '%, Nhiệt độ ' . $patient->latestVital->temperature . '°C. Tiền sử dị ứng: ' . ($patient->allergies ?: 'Không') : '') }}</textarea>
                    </div>

                    <!-- Câu hỏi hội chẩn -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nội dung / Câu hỏi xin ý kiến hội chẩn <span class="text-danger">*</span></label>
                        <textarea name="proposed_questions" class="form-control" rows="3" placeholder="VD: Xin ý kiến đánh giá nguy cơ sốc phản vệ; Đề xuất phác đồ kháng sinh phối hợp; Chỉ định chụp CT-Scanner khẩn cấp..." required>{{ old('proposed_questions') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('consultations.index') }}" class="btn btn-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Phát Lệnh Hội Chẩn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
