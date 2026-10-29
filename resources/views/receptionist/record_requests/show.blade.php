@extends('layouts.admin')

@section('title', 'Thẩm Định Đơn Sao Lục Bệnh Án: ' . $recordRequest->request_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.record-requests.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-file-earmark-lock2 me-2"></i>Đơn Đề Nghị Sao Lục Bệnh Án: {{ $recordRequest->request_code }}
            </h4>
            <p class="text-muted small mb-0">Hồ sơ trích sao bệnh án điện tử - Bệnh nhân: <strong>{{ $recordRequest->patientProfile->full_name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            @if($recordRequest->status === 'approved' || $recordRequest->status === 'printed')
                <a href="{{ route('reception.record-requests.print', $recordRequest) }}" target="_blank" class="btn btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-printer"></i> In Bản Tóm Tắt Watermark
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-info-circle text-primary me-2"></i>Nội Dung Đơn Đề Nghị & Căn Cứ Pháp Lý
                    </h6>
                    <div>{!! $recordRequest->status_badge !!}</div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Người Xin Trích Sao:</span>
                            <span class="fw-bold text-dark fs-6">{{ $recordRequest->requester_name }}</span>
                            <div class="small text-muted">Số CCCD/HC: <strong>{{ $recordRequest->requester_identity_card }}</strong> | SĐT: {{ $recordRequest->requester_phone }}</div>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Tư Cách Pháp Lý:</span>
                            <span class="badge bg-primary fs-6">
                                {{ match($recordRequest->requester_type) {
                                    'patient_self' => 'Người bệnh chính chủ',
                                    'guardian' => 'Thân nhân / Đại diện theo pháp luật',
                                    'law_enforcement' => 'Cơ quan tiến hành tố tụng',
                                    default => 'Doanh nghiệp bảo hiểm'
                                } }}
                            </span>
                            @if($recordRequest->relationship_to_patient)
                                <div class="small text-muted mt-1">Quan hệ: {{ $recordRequest->relationship_to_patient }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted small d-block mb-1">Mục Đích Sử Dụng Bản Sao:</span>
                        <div class="p-3 bg-light rounded text-secondary border">
                            {{ $recordRequest->purpose }}
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Số Lượng Bản Sao Yêu Cầu:</span>
                            <span class="fw-bold fs-5 text-dark">{{ $recordRequest->copies_count }} Bản</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Người Phê Duyệt (KHTH):</span>
                            <span class="fw-semibold text-primary">{{ $recordRequest->approvedBy?->name ?? 'Chưa duyệt' }}</span>
                        </div>
                    </div>

                    @if($recordRequest->rejection_reason)
                        <div class="alert alert-danger py-2 mt-3 mb-0">
                            <strong>Lý do từ chối:</strong> {{ $recordRequest->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thẩm Định Phê Duyệt Form -->
            @if($recordRequest->status !== 'printed')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-check text-primary me-2"></i>Quy Trình Phê Duyệt Hồ Sơ (Phòng KHTH & Lễ Tân)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('reception.record-requests.status', $recordRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cập Nhật Trạng Thái Xử Lý</label>
                                    <select name="status" class="form-select" required>
                                        <option value="reviewing" {{ $recordRequest->status === 'reviewing' ? 'selected' : '' }}>Đang thẩm tra giấy tờ pháp lý</option>
                                        <option value="approved" {{ $recordRequest->status === 'approved' ? 'selected' : '' }}>Chấp thuận cấp bản sao (Phê duyệt)</option>
                                        <option value="printed" {{ $recordRequest->status === 'printed' ? 'selected' : '' }}>Đã xuất bản sao & bàn giao</option>
                                        <option value="rejected" {{ $recordRequest->status === 'rejected' ? 'selected' : '' }}>Từ chối cấp trích sao</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-lg me-1"></i> Cập Nhật Quyết Định
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-person-circle text-primary me-2"></i>Hồ Sơ Bệnh Nhân Gốc
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="fw-bold fs-6 text-dark">{{ $recordRequest->patientProfile->full_name }}</div>
                    <small class="text-muted d-block mb-3">Mã: {{ $recordRequest->patientProfile->patient_code }} | SĐT: {{ $recordRequest->patientProfile->phone }}</small>

                    <div class="mb-3">
                        <span class="text-muted small d-block">Lịch Sử Bệnh Án Có Thể Sao Lục:</span>
                        <div class="badge bg-info text-dark">{{ $recordRequest->patientProfile->medicalRecords->count() }} Lần khám bệnh</div>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('reception.patients.show', $recordRequest->patientProfile) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Xem Chi Tiết Bệnh Án Điện Tử
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
