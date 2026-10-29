@extends('layouts.admin')

@section('title', 'Khảo Sát Hài Lòng & NPS Người Bệnh (Bộ 83 Tiêu Chí BYT)')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-star-half text-warning me-2"></i>Chất Lượng Dịch Vụ & Sự Hài Lòng Người Bệnh (CSAT & NPS)
            </h4>
            <p class="text-muted small mb-0">Theo dõi 4 trụ cột đánh giá chất lượng khám chữa bệnh theo Bộ 83 Tiêu chí của Bộ Y Tế và quản lý phản hồi của người bệnh.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Summary Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <span class="text-muted small d-block">Điểm CSAT Trung Bình</span>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold mb-0 text-warning">{{ $stats['avg_overall'] }}</h2>
                    <span class="text-muted">/ 5.0 ⭐</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <span class="text-muted small d-block">Chỉ số NPS (Khách hàng tích cực)</span>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold mb-0 text-primary">{{ $stats['nps'] }}%</h2>
                    <span class="text-muted">NPS Score</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <span class="text-muted small d-block">Tổng lượt đánh giá</span>
                <h2 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['total']) }}</h2>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <span class="text-muted small d-block">Cần CSKH Hỗ Trợ (Cờ Đỏ)</span>
                <h2 class="fw-bold mb-0 text-danger">{{ $stats['pending_followups'] }} ca</h2>
            </div>
        </div>
    </div>

    <!-- 4 MOH Criteria Scores -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Điểm 4 Trụ Cột Đánh Giá Chất Lượng Bộ Y Tế</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 text-center">
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">1. Đón tiếp & Thủ tục</small>
                        <h4 class="fw-bold text-primary mb-0">{{ $stats['avg_reception'] }} / 5.0</h4>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">2. Bác sĩ & Điều dưỡng</small>
                        <h4 class="fw-bold text-success mb-0">{{ $stats['avg_doctor'] }} / 5.0</h4>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">3. Cơ sở & Vệ sinh</small>
                        <h4 class="fw-bold text-info mb-0">{{ $stats['avg_facilities'] }} / 5.0</h4>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">4. Thời gian chờ đợi</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $stats['avg_waiting'] }} / 5.0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-list-check me-2"></i>Ý Kiến Đánh Giá Của Người Bệnh</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('reception.surveys.index') }}" class="btn btn-sm {{ !request('followup') ? 'btn-primary' : 'btn-outline-secondary' }}">Tất cả</a>
                <a href="{{ route('reception.surveys.index', ['followup' => 'pending']) }}" class="btn btn-sm {{ request('followup') == 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-flag-fill me-1"></i>Chờ CSKH Xử Lý ({{ $stats['pending_followups'] }})
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Bệnh Nhân / Mã BN</th>
                        <th>Điểm 4 Trụ Cột</th>
                        <th>Điểm Chung / NPS</th>
                        <th>Ý Kiến Phản Hồi</th>
                        <th>Trạng Thái CSKH</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveys as $sv)
                        <tr class="{{ $sv->requires_followup && $sv->followup_status === 'pending' ? 'table-danger bg-opacity-25' : '' }}">
                            <td>
                                <strong class="text-primary font-monospace">{{ $sv->survey_code }}</strong>
                                <small class="text-muted d-block">{{ $sv->created_at->format('H:i d/m/Y') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $sv->patientProfile?->full_name ?? ($sv->appointment?->patientProfile?->full_name ?? 'Khách vãng lai') }}</strong>
                                <small class="text-muted">{{ $sv->patientProfile?->phone ?? $sv->appointment?->patientProfile?->phone }}</small>
                            </td>
                            <td>
                                <div class="small">
                                    <span>Lễ tân: <strong>{{ $sv->rating_reception }}⭐</strong></span> &bull;
                                    <span>Bác sĩ: <strong>{{ $sv->rating_doctor }}⭐</strong></span><br>
                                    <span>Cơ sở: <strong>{{ $sv->rating_facilities }}⭐</strong></span> &bull;
                                    <span>Chờ: <strong>{{ $sv->rating_waiting_time }}⭐</strong></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $sv->overall_score >= 4 ? 'bg-success' : ($sv->overall_score >= 3 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                                    {{ $sv->overall_score }} ⭐
                                </span>
                                <small class="text-muted d-block mt-1">NPS: <strong>{{ $sv->nps_score }}/10</strong></small>
                            </td>
                            <td style="max-width: 250px;">
                                <p class="small text-dark mb-0 text-truncate" title="{{ $sv->feedback_text }}">{{ $sv->feedback_text ?: 'Không có phản hồi thêm' }}</p>
                            </td>
                            <td>
                                @if($sv->requires_followup)
                                    @if($sv->followup_status === 'pending')
                                        <span class="badge bg-danger animate-pulse"><i class="bi bi-flag-fill me-1"></i>Cần gọi lại</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Đã hỗ trợ</span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border">Bình thường</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sv->requires_followup && $sv->followup_status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resolveModal-{{ $sv->id }}" title="Ghi nhận CSKH đã gọi xử lý">
                                        <i class="bi bi-telephone-outbound-fill me-1"></i>Hỗ trợ
                                    </button>

                                    <!-- Followup Modal -->
                                    <div class="modal fade" id="resolveModal-{{ $sv->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form action="{{ route('reception.surveys.resolve', $sv) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title fw-bold">CSKH Xử Lý Phản Hồi Người Bệnh</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2">Bệnh nhân: <strong>{{ $sv->patientProfile?->full_name ?? $sv->appointment?->patientProfile?->full_name }}</strong> ({{ $sv->patientProfile?->phone ?? $sv->appointment?->patientProfile?->phone }})</p>
                                                        <p class="small text-danger mb-3">Nội dung không hài lòng: <em>"{{ $sv->feedback_text }}"</em></p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Ghi chú kết quả cuộc gọi CSKH:</label>
                                                            <textarea name="followup_notes" class="form-control form-control-sm" rows="3" required>Đã liên hệ trực tiếp người bệnh, giải thích quy trình xét nghiệm và gửi lời xin lỗi về thời gian chờ. Người bệnh đã hài lòng và đồng ý tiếp tục tái khám.</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-success btn-sm">Xác Nhận Đã Xử Lý Xong</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Chưa có ý kiến khảo sát nào từ người bệnh.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($surveys->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $surveys->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
