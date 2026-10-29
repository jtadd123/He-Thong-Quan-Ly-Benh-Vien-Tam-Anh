@extends('layouts.admin')

@section('title', 'Báo Cáo Sự Cố Y Khoa Tự Nguyện')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('reception.medical-incidents.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mb-1 text-danger">
            <i class="bi bi-shield-exclamation me-2"></i>Báo Cáo Sự Cố Y Khoa & Nguy Cơ An Toàn (Thông tư 43/2018/TT-BYT)
        </h4>
        <p class="text-muted small">Chính sách báo cáo không xử phạt (Blameless Policy) nhằm cải tiến chất lượng và bảo vệ người bệnh - Bệnh Viện Tâm Anh</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('reception.medical-incidents.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Mọi nhân viên y tế và lễ tân đều được khuyến khích báo cáo mọi nguy cơ <strong>suýt xảy ra (Near-Miss)</strong> để kịp thời hoàn thiện quy trình.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hình thức báo cáo <span class="text-danger">*</span></label>
                        <select name="reporter_type" class="form-select" required>
                            <option value="named" selected>Định danh (Ghi nhận tên cán bộ báo cáo)</option>
                            <option value="anonymous">Ẩn danh (Bảo mật danh tính tuyệt đối)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mức độ sự cố (Theo Bộ Y Tế) <span class="text-danger">*</span></label>
                        <select name="severity_level" class="form-select" required>
                            <option value="NC0" selected>NC0: Sự cố suýt xảy ra (Đã kịp thời phát hiện và chặn lại, chưa ảnh hưởng đến người bệnh)</option>
                            <option value="NC1">NC1: Sự cố xảy ra nhưng không gây tổn thương cho người bệnh</option>
                            <option value="NC2">NC2: Sự cố gây tổn thương nhẹ hoặc tạm thời</option>
                            <option value="NC3">NC3: Sự cố nghiêm trọng cần xử lý khẩn</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nhóm nguy cơ sự cố <span class="text-danger">*</span></label>
                        <select name="incident_category" class="form-select" required>
                            <option value="Nhầm lẫn định danh người bệnh (Trùng tên, nhầm số hồ sơ)">Nhầm lẫn định danh người bệnh (Trùng tên, nhầm số hồ sơ)</option>
                            <option value="Nhầm lẫn thuốc / Sai liều lượng / Dị ứng">Nhầm lẫn thuốc / Sai liều lượng / Dị ứng</option>
                            <option value="Sai nhãn mẫu xét nghiệm / Nhầm kết quả">Sai nhãn mẫu xét nghiệm / Nhầm kết quả</option>
                            <option value="Trang thiết bị y tế gặp sự cố">Trang thiết bị y tế gặp sự cố</option>
                            <option value="Nguy cơ té ngã tại sảnh / khu vệ sinh">Nguy cơ té ngã tại sảnh / khu vệ sinh</option>
                            <option value="Sự cố giao tiếp / Bàn giao ca trực">Sự cố giao tiếp / Bàn giao ca trực</option>
                            <option value="Khác">Nhóm nguy cơ khác</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Thời điểm xảy ra <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="occurred_at" value="{{ date('Y-m-d\TH:i') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Khu vực / Địa điểm <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" placeholder="VD: Quầy tiếp đón số 3..." required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Mô tả chi tiết sự việc <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả cụ thể sự việc đã diễn ra như thế nào, ai phát hiện và tại sao..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Hành động khắc phục tức thì đã thực hiện</label>
                        <textarea name="immediate_action" class="form-control" rows="2" placeholder="Ví dụ: Đã đối chiếu lại CCCD và in lại nhãn hồ sơ chính xác, hủy mẫu xét nghiệm nhầm..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('reception.medical-incidents.index') }}" class="btn btn-outline-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-danger px-4 fw-semibold">
                        <i class="bi bi-send-check me-1"></i> Gửi Báo Cáo An Toàn Người Bệnh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
