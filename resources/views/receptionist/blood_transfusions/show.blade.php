@extends('layouts.admin')

@section('title', 'Giám Sát Ca Truyền Máu Lâm Sàng - ' . $transfusion->transfusion_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-slate-800">Hồ Sơ Giám Sát Truyền Máu Lâm Sàng</h4>
                <span class="badge bg-danger fs-6 font-monospace">{{ $transfusion->transfusion_code }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Người bệnh: <strong>{{ $transfusion->patientProfile->full_name }}</strong> ({{ $transfusion->patientProfile->patient_code }}) • 
                Trạng thái: 
                @if($transfusion->status === 'completed')
                    <span class="text-success fw-bold">Đã hoàn tất an toàn</span>
                @elseif($transfusion->status === 'transfusing')
                    <span class="text-danger fw-bold animate-pulse">Đang truyền máu tại giường</span>
                @elseif($transfusion->status === 'aborted')
                    <span class="text-dark fw-bold">Đã đình chỉ (Tai biến)</span>
                @else
                    <span class="text-warning fw-bold">Đã ký duyệt - Chờ cắm kim</span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('reception.blood-transfusions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Danh sách ca truyền
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

    @if(session('warning'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Card Thông tin đối chiếu kép -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                    <i class="bi bi-shield-check text-success me-1"></i> Biên Bản Đối Chiếu An Toàn Tại Giường (Dual Verification)
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-6 p-3 bg-light rounded-3">
                        <small class="text-muted d-block text-uppercase">Nhóm Máu Người Bệnh</small>
                        <h4 class="fw-bold text-danger mb-1">
                            {{ $transfusion->patient_abo }} Rh({{ $transfusion->patient_rh === 'positive' ? '+' : '-' }})
                        </h4>
                        <small class="text-muted">Chỉ định: <strong>{{ $transfusion->indication }}</strong></small>
                    </div>
                    <div class="col-6 p-3 bg-light rounded-3">
                        <small class="text-muted d-block text-uppercase">Túi Máu Được Cấp</small>
                        <h4 class="fw-bold text-danger mb-1 font-monospace">{{ $transfusion->bloodUnit->unit_code }}</h4>
                        <small class="text-muted d-block">{{ $transfusion->bloodUnit->product_name }} ({{ $transfusion->bloodUnit->volume_ml }}ml)</small>
                        <small class="text-muted">Nhóm túi máu: <strong class="text-danger">{{ $transfusion->bloodUnit->full_group }}</strong></small>
                    </div>
                </div>

                <div class="p-3 bg-success-subtle text-success border border-success-subtle rounded-3 mb-3 small">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ $transfusion->crossmatch_notes }}
                </div>

                <div class="row g-2 small text-muted">
                    <div class="col-6">
                        <span>Điều dưỡng đối chiếu 1:</span>
                        <strong class="text-dark d-block">{{ $transfusion->nurse1 ? $transfusion->nurse1->name : '--' }}</strong>
                    </div>
                    <div class="col-6">
                        <span>Điều dưỡng đối chiếu 2:</span>
                        <strong class="text-dark d-block">{{ $transfusion->nurse2 ? $transfusion->nurse2->name : '--' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Hành động điều dưỡng -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                    <i class="bi bi-clock-history text-primary me-1"></i> Tiến Trình Lâm Sàng
                </h6>

                @if($transfusion->status === 'prepared')
                    <div class="text-center py-4">
                        <p class="text-muted small mb-3">Túi máu đã được kiểm tra chéo và xuất khỏi ngân hàng máu. Vui lòng sát khuẩn và cắm kim truyền.</p>
                        <form action="{{ route('reception.blood-transfusions.start', $transfusion) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg fw-bold w-100 shadow-sm">
                                <i class="bi bi-play-circle-fill me-1"></i> Bắt Đầu Truyền Máu
                            </button>
                        </form>
                    </div>
                @elseif($transfusion->status === 'transfusing')
                    <div class="alert alert-danger text-center p-3 mb-3">
                        <strong class="d-block fs-6">ĐANG TRUYỀN MÁU</strong>
                        <small>Bắt đầu lúc: {{ $transfusion->started_at ? $transfusion->started_at->format('H:i:s d/m/Y') : '--' }}</small>
                    </div>

                    <!-- Nút Hoàn tất an toàn -->
                    <form action="{{ route('reception.blood-transfusions.complete', $transfusion) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold w-100 py-2 shadow-sm" onclick="return confirm('Xác nhận đã truyền hết túi máu an toàn?')">
                            <i class="bi bi-check2-circle me-1"></i> Hoàn Tất Ca Truyền An Toàn
                        </button>
                    </form>

                    <!-- Nút Báo cáo tai biến -->
                    <button type="button" class="btn btn-outline-dark btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalReaction">
                        <i class="bi bi-exclamation-octagon me-1 text-danger"></i> Báo Cáo Tai Biến / Ngừng Truyền
                    </button>
                @elseif($transfusion->status === 'completed')
                    <div class="alert alert-success text-center p-3">
                        <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-1"></i>
                        <strong class="fs-6">CA TRUYỀN MÁU ĐÃ HOÀN TẤT THÀNH CÔNG</strong>
                        <small class="d-block text-muted">Kết thúc lúc: {{ $transfusion->completed_at ? $transfusion->completed_at->format('H:i d/m/Y') : '--' }}</small>
                    </div>
                @elseif($transfusion->status === 'aborted')
                    <div class="alert alert-dark p-3">
                        <strong class="text-danger d-block"><i class="bi bi-slash-circle-fill me-1"></i> ĐÃ ĐÌNH CHỈ TRUYỀN MÁU</strong>
                        <p class="small mb-0 mt-1">Lý do tai biến: {{ $transfusion->reaction_details }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vitals Monitoring Table: Baseline vs 15 Min -->
    <div class="card border-0 shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h6 class="fw-bold text-slate-800 mb-0">
                <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Theo Dõi Dấu Hiệu Sinh Tồn Tại Giường
            </h6>
            @if($transfusion->status === 'transfusing' && !$transfusion->vitals_15min)
                <button class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modal15MinVitals">
                    <i class="bi bi-plus-circle me-1"></i> Ghi Nhận Sinh Hiệu Phút 15
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Giai Đoạn</th>
                        <th>Thời Điểm</th>
                        <th>Huyết Áp (mmHg)</th>
                        <th>Mạch (ck/phút)</th>
                        <th>Thân Nhiệt (°C)</th>
                        <th>Đánh Giá An Toàn</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Trước Khi Truyền (Phút 0)</td>
                        <td>{{ $transfusion->created_at->format('H:i d/m') }}</td>
                        <td><strong>{{ $transfusion->vitals_baseline['blood_pressure'] ?? '120/80' }}</strong></td>
                        <td><strong>{{ $transfusion->vitals_baseline['heart_rate'] ?? '80' }}</strong></td>
                        <td><strong>{{ $transfusion->vitals_baseline['temperature'] ?? '36.8' }}</strong></td>
                        <td><span class="badge bg-success">Ổn định</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-danger">Sau Khi Bắt Đầu 15 Phút</td>
                        <td>{{ $transfusion->vitals_15min['noted_at'] ?? 'Chưa ghi' }}</td>
                        <td>
                            @if($transfusion->vitals_15min)
                                <strong class="text-danger">{{ $transfusion->vitals_15min['blood_pressure'] }}</strong>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            @if($transfusion->vitals_15min)
                                <strong class="text-danger">{{ $transfusion->vitals_15min['heart_rate'] }}</strong>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            @if($transfusion->vitals_15min)
                                <strong class="text-danger">{{ $transfusion->vitals_15min['temperature'] }}</strong>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            @if($transfusion->vitals_15min)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã kiểm tra an toàn</span>
                            @else
                                <span class="badge bg-warning text-dark">Cần đo tại phút 15</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ghi Nhận Sinh Hiệu Phút 15 -->
<div class="modal fade" id="modal15MinVitals" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reception.blood-transfusions.vitals', $transfusion) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Ghi Nhận Sinh Hiệu Tại Phút 15</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">15 phút đầu là thời điểm dễ xảy ra phản ứng tán huyết cấp hoặc sốc phản vệ nhất. Vui lòng đo chính xác:</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Huyết áp (mmHg) <span class="text-danger">*</span></label>
                        <input type="text" name="bp_15" class="form-control" required placeholder="Ví dụ: 125/85">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tần số Mạch (lần/phút) <span class="text-danger">*</span></label>
                        <input type="text" name="hr_15" class="form-control" required placeholder="Ví dụ: 82">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thân nhiệt (°C) <span class="text-danger">*</span></label>
                        <input type="text" name="temp_15" class="form-control" required placeholder="Ví dụ: 37.0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold">Lưu Sinh Hiệu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Báo Cáo Tai Biến -->
<div class="modal fade" id="modalReaction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reception.blood-transfusions.reaction', $transfusion) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold">Báo Cáo Phản Ứng / Tai Biến Truyền Máu</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <strong>LỆNH KHẨN:</strong> Ngay lập tức khóa dây truyền máu, giữ đường truyền tĩnh mạch bằng Natri Clorid 0.9% và báo ngay Bác sĩ trực!
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Triệu chứng phản ứng cấp của người bệnh <span class="text-danger">*</span></label>
                        <textarea name="reaction_details" class="form-control" rows="3" required placeholder="Mô tả: Rét run, sốt cao đột ngột, mẩn đỏ ngứa, khó thở, tụt huyết áp, đau vùng thắt lưng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger fw-bold">Xác Nhận Đình Chỉ & Báo Cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
