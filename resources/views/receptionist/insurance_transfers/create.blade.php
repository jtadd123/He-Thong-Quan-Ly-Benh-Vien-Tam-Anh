@extends('layouts.admin')

@section('title', 'Tiếp Nhận Giấy Chuyển Tuyến BHYT - Mẫu 40/BYT')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('reception.insurance-transfers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mb-1 text-primary">
            <i class="bi bi-file-earmark-plus me-2"></i>Tiếp Nhận Giấy Chuyển Tuyến BHYT (Mẫu 40/BYT)
        </h4>
        <p class="text-muted small">Nhập thông tin giấy chuyển tuyến từ cơ sở y tế nơi đi vào hệ thống Bệnh Viện Tâm Anh</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('reception.insurance-transfers.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-person-bounding-box text-primary me-2"></i>1. Thông Tin Bệnh Nhân Có Thẻ BHYT
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chọn Bệnh Nhân <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select @error('patient_profile_id') is-invalid @enderror" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ (old('patient_profile_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->full_name }} (Mã: {{ $p->patient_code }} | Thẻ BHYT: {{ $p->insurance_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('patient_profile_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mức Hưởng BHYT Khi Chuyển Tuyến <span class="text-danger">*</span></label>
                        <select name="bhyt_reimbursement_rate" class="form-select" required>
                            <option value="100" selected>100% - Đúng tuyến vượt chuyên môn kỹ thuật</option>
                            <option value="80">80% - Đúng tuyến thông thường (Học sinh/Sinh viên/Doanh nghiệp)</option>
                            <option value="40">40% - Trái tuyến điều trị nội trú theo luật</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-hospital text-primary me-2"></i>2. Thông Tin Cơ Sở Khám Chữa Bệnh Nơi Đi
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã KCB Nơi Đi <span class="text-danger">*</span></label>
                        <input type="text" name="issuing_hospital_code" value="{{ old('issuing_hospital_code', '79-012') }}" class="form-control" placeholder="VD: 79-012, 01-045..." required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Tên Cơ Sở Khám Chữa Bệnh Chuyển Đến <span class="text-danger">*</span></label>
                        <input type="text" name="issuing_hospital_name" value="{{ old('issuing_hospital_name') }}" class="form-control" placeholder="VD: Bệnh viện Đa khoa Huyện Củ Chi..." required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phân Loại Lý Do Chuyển Tuyến <span class="text-danger">*</span></label>
                        <select name="transfer_type" class="form-select" required>
                            <option value="right_route">Đúng tuyến (Vượt khả năng chuyên môn kỹ thuật)</option>
                            <option value="emergency">Chuyển viện cấp cứu</option>
                            <option value="patient_request">Theo nguyện vọng của người bệnh/thân nhân</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hiệu Lực Từ Ngày <span class="text-danger">*</span></label>
                        <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hiệu Lực Đến Ngày <span class="text-danger">*</span></label>
                        <input type="date" name="valid_to" value="{{ old('valid_to', date('Y-m-d', strtotime('+30 days'))) }}" class="form-control" required>
                        <small class="text-muted">Theo quy định thường là 10 ngày làm việc hoặc hết đợt điều trị ngoại trú</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-file-medical text-primary me-2"></i>3. Chẩn Đoán & Tóm Tắt Lâm Sàng Nơi Đi
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã Bệnh ICD-10</label>
                        <input type="text" name="icd10_code" value="{{ old('icd10_code', 'I10') }}" class="form-control" placeholder="VD: I10, E11.9...">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Chẩn Đoán Nơi Chuyển Đi <span class="text-danger">*</span></label>
                        <input type="text" name="icd10_diagnosis" value="{{ old('icd10_diagnosis') }}" class="form-control" placeholder="VD: Tăng huyết áp nguyên phát độ 2, biến chứng tim mạch..." required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Tóm Tắt Bệnh Án & Lý Do Chuyển Tuyến</label>
                        <textarea name="clinical_summary" class="form-control" rows="3" placeholder="Ghi nhận diễn biến bệnh, các cận lâm sàng đã làm tại tuyến dưới và lý do chuyển Tâm Anh..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('reception.insurance-transfers.index') }}" class="btn btn-outline-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Lưu & Kích Hoạt Giấy Chuyển Tuyến
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
