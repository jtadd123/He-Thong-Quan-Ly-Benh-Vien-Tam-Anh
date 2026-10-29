@extends('layouts.admin')

@section('title', 'Tạo Đơn Đề Nghị Sao Lục Bệnh Án')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('reception.record-requests.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mb-1 text-primary">
            <i class="bi bi-file-earmark-plus me-2"></i>Tiếp Nhận Đơn Đề Nghị Sao Lục Bệnh Án
        </h4>
        <p class="text-muted small">Cung cấp bản sao hồ sơ bệnh án theo Điều 69 Luật Khám bệnh, chữa bệnh 2023</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('reception.record-requests.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-person-fill text-primary me-2"></i>1. Người Bệnh Có Hồ Sơ Tại Bệnh Viện
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chọn Bệnh Nhân Đã Từng Khám <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-semibold">Số Bản Sao Cần Cấp (Tối đa 10 bản) <span class="text-danger">*</span></label>
                        <input type="number" name="copies_count" value="{{ old('copies_count', 1) }}" min="1" max="10" class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark border-bottom pb-2">
                            <i class="bi bi-person-badge text-primary me-2"></i>2. Thông Tin & Tư Cách Pháp Lý Của Người Xin Trích Sao
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tư Cách Pháp Lý <span class="text-danger">*</span></label>
                        <select name="requester_type" class="form-select" required>
                            <option value="patient_self">Người bệnh trực tiếp yêu cầu (Chính chủ)</option>
                            <option value="guardian">Thân nhân / Người đại diện theo pháp luật</option>
                            <option value="law_enforcement">Cơ quan điều tra / Tòa án / Viện kiểm sát</option>
                            <option value="insurance_assessor">Doanh nghiệp bảo hiểm (Có giấy ủy quyền)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Họ Và Tên Người Yêu Cầu <span class="text-danger">*</span></label>
                        <input type="text" name="requester_name" value="{{ old('requester_name') }}" class="form-control" placeholder="VD: Nguyễn Văn A..." required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số CCCD / Hộ Chiếu <span class="text-danger">*</span></label>
                        <input type="text" name="requester_identity_card" value="{{ old('requester_identity_card') }}" class="form-control" placeholder="VD: 001092008899" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số Điện Thoại Liên Hệ <span class="text-danger">*</span></label>
                        <input type="text" name="requester_phone" value="{{ old('requester_phone') }}" class="form-control" placeholder="VD: 0988776655" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mối Quan Hệ Với Người Bệnh</label>
                        <input type="text" name="relationship_to_patient" value="{{ old('relationship_to_patient') }}" class="form-control" placeholder="VD: Bố/Mẹ/Vợ/Chồng hoặc Giám định viên...">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Mục Đích Sử Dụng Bản Sao Bệnh Án <span class="text-danger">*</span></label>
                        <textarea name="purpose" class="form-control" rows="3" placeholder="Ghi rõ mục đích: Thanh toán bảo hiểm nhân thọ, bổ sung hồ sơ chuyển viện quốc tế, giám định y khoa, giải quyết tranh chấp pháp lý..." required></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('reception.record-requests.index') }}" class="btn btn-outline-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Nộp Đơn Đề Nghị Trích Sao
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
