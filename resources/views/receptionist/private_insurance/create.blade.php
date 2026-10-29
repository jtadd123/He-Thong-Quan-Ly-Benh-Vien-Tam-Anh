@extends('layouts.admin')

@section('title', 'Tiếp Nhận Bảo Lãnh Viện Phí Tư Nhân GOP')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('reception.private-insurance.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mb-1 text-primary">
            <i class="bi bi-shield-plus me-2"></i>Tiếp Nhận Thư Bảo Lãnh Viện Phí Tư Nhân (GOP)
        </h4>
        <p class="text-muted small">Phát hành và đối soát bảo lãnh trực tiếp cho người bệnh có thẻ bảo hiểm sức khỏe tư nhân / quốc tế</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('reception.private-insurance.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-person-circle text-primary me-2"></i>1. Khách Hàng / Bệnh Nhân VIP
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chọn Bệnh Nhân <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select @error('patient_profile_id') is-invalid @enderror" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ (old('patient_profile_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->full_name }} (Mã: {{ $p->patient_code }} | SĐT: {{ $p->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('patient_profile_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hạng Tiếp Đón VIP <span class="text-danger">*</span></label>
                        <select name="vip_level" class="form-select" required>
                            <option value="standard">Tiêu chuẩn (Standard)</option>
                            <option value="silver">Bạc (Silver Member)</option>
                            <option value="gold" selected>Vàng (Gold VIP - Phòng chờ ưu tiên)</option>
                            <option value="platinum">Bạch kim (Platinum VIP - Tiếp đón riêng)</option>
                            <option value="diamond">Kim cương (Diamond Ambassador - Bác sĩ riêng)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-building-check text-primary me-2"></i>2. Thông Tin Đơn Vị Bảo Hiểm & Thẻ Bảo Lãnh
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Công Ty Bảo Hiểm / Đơn Vị TPA <span class="text-danger">*</span></label>
                        <select name="tpa_company" class="form-select" required>
                            <option value="">-- Chọn công ty bảo hiểm --</option>
                            @foreach($tpaList as $tpa)
                                <option value="{{ $tpa }}" {{ old('tpa_company') == $tpa ? 'selected' : '' }}>{{ $tpa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số Thẻ Bảo Hiểm Sức Khỏe <span class="text-danger">*</span></label>
                        <input type="text" name="card_number" value="{{ old('card_number') }}" class="form-control" placeholder="VD: BV-GOLD-988271" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hạn Mức Thẻ Được Cấp (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="coverage_limit" value="{{ old('coverage_limit', 50000000) }}" step="100000" min="0" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số Tiền TPA Chấp Thuận Bảo Lãnh (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="approved_amount" value="{{ old('approved_amount', 15000000) }}" step="50000" min="0" class="form-control fw-bold text-success" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bệnh Nhân Tự Chi Trả / Vượt Hạn Mức (VNĐ)</label>
                        <input type="number" name="patient_copay_amount" value="{{ old('patient_copay_amount', 0) }}" step="50000" min="0" class="form-control text-danger">
                        <small class="text-muted">Phần đồng chi trả hoặc thuốc ngoài danh mục</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i>3. Người Liên Hệ Thẩm Định TPA
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chuyên Viên Thẩm Định Bồi Thường</label>
                        <input type="text" name="contact_officer" value="{{ old('contact_officer') }}" class="form-control" placeholder="VD: Nguyễn Thị Lan (Insmart Hotline)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số Điện Thoại Hotline Xác Thực</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control" placeholder="VD: 028 7300 1234">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Ghi Chú & Điều Khoản Loại Trừ</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Ghi nhận các dịch vụ không được bảo lãnh (vitamin, thẩm mỹ, bệnh có sẵn...)..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('reception.private-insurance.index') }}" class="btn btn-outline-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-shield-check me-1"></i> Phát Hành Thư Bảo Lãnh GOP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
