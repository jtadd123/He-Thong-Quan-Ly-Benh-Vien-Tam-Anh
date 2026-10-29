@extends('layouts.admin')

@section('title', 'Tái Khám Tự Động & Quản Lý Bệnh Mãn Tính')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-alarm-fill me-2"></i>Tái Khám Tự Động & Quản Lý Bệnh Mãn Tính (Recall Automation)
            </h4>
            <p class="text-muted small mb-0">Tự động tính ngày hết thuốc BHYT (28-30 ngày), nhắc hẹn tự động qua Zalo ZNS / SMS - Bệnh Viện Tâm Anh</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newRecallModal">
                <i class="bi bi-plus-circle"></i> Đăng Ký Quản Lý Bệnh Mãn Tính
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Ca Theo Dõi</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total_recalls'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Chờ Đến Ngày Nhắc</div>
                            <div class="fs-4 fw-bold text-warning">{{ $stats['scheduled'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đã Gửi Tin Nhắc Zalo/SMS</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['reminded'] }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-send-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tái Khám Đúng Hạn</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['attended'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Theo Dõi</th>
                            <th>Bệnh Nhân Mãn Tính</th>
                            <th>Bệnh Lý Mãn Tính</th>
                            <th>Lần Khám Trước</th>
                            <th>Cấp Thuốc (Ngày)</th>
                            <th>Ngày Tái Khám Hẹn</th>
                            <th>Kênh Nhắc</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recalls as $r)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $r->recall_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $r->patientProfile->patient_code }} | SĐT: {{ $r->patientProfile->phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-semibold">{{ $r->chronic_condition }}</span>
                                </td>
                                <td class="small">{{ $r->last_visit_date->format('d/m/Y') }}</td>
                                <td class="fw-bold">{{ $r->prescription_days }} ngày</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $r->due_recall_date->format('d/m/Y') }}</div>
                                    @php $rem = $r->days_remaining; @endphp
                                    @if($rem > 0)
                                        <small class="text-muted">Còn {{ $rem }} ngày nữa</small>
                                    @elseif($rem === 0)
                                        <small class="text-danger fw-bold">Hôm nay đến hạn!</small>
                                    @else
                                        <small class="text-danger fw-bold">Quá hạn {{ abs($rem) }} ngày</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace text-uppercase">{{ $r->reminder_channel }}</span>
                                </td>
                                <td>{!! $r->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        @if($r->status !== 'attended')
                                            <form action="{{ route('reception.chronic-recalls.remind', $r) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Gửi tin nhắc Zalo/SMS ngay">
                                                    <i class="bi bi-send"></i> Nhắc Hẹn
                                                </button>
                                            </form>
                                            <form action="{{ route('reception.chronic-recalls.attended', $r) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success" title="Xác nhận BN đã tới khám">
                                                    <i class="bi bi-check-lg"></i> Đã Tới
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-success">Hoàn thành</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-alarm fs-1 d-block mb-2"></i>
                                    Chưa có chương trình tái khám bệnh mãn tính nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recalls->hasPages())
                <div class="p-3 border-top">{{ $recalls->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Đăng Ký Tái Khám Mới -->
<div class="modal fade" id="newRecallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.chronic-recalls.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-alarm-fill me-2"></i>Đăng Ký Quản Lý Tái Khám Bệnh Mãn Tính</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh mãn tính <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->full_name }} (Mã: {{ $pt->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh lý mãn tính theo dõi <span class="text-danger">*</span></label>
                        <select name="chronic_condition" class="form-select" required>
                            <option value="Tăng Huyết Áp Nguyên Phát (I10)">Tăng Huyết Áp Nguyên Phát (I10)</option>
                            <option value="Đái Tháo Đường Type 2 (E11)">Đái Tháo Đường Type 2 (E11)</option>
                            <option value="Bệnh Phổi Tắc Nghẽn Mãn Tính (COPD)">Bệnh Phổi Tắc Nghẽn Mãn Tính (COPD)</option>
                            <option value="Bệnh Tim Thiếu Máu Cục Bộ">Bệnh Tim Thiếu Máu Cục Bộ</option>
                            <option value="Viêm Gan B Mãn Tính">Viêm Gan B Mãn Tính</option>
                            <option value="Suy Giáp / Bệnh Tuyến Giáp">Suy Giáp / Bệnh Tuyến Giáp</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày khám / Cấp thuốc gần nhất <span class="text-danger">*</span></label>
                            <input type="date" name="last_visit_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số ngày cấp thuốc BHYT <span class="text-danger">*</span></label>
                            <input type="number" name="prescription_days" value="28" min="7" max="90" class="form-control" required>
                            <small class="text-muted">Chuẩn BHYT thường là 28 hoặc 30 ngày</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kênh gửi tin nhắc tự động <span class="text-danger">*</span></label>
                        <select name="reminder_channel" class="form-select" required>
                            <option value="zalo_zns" selected>Zalo ZNS Thông Báo Chính Thức</option>
                            <option value="sms">SMS Brandname Bệnh Viện Tâm Anh</option>
                            <option value="phone_call">Cuộc Gọi Tổng Đài AI Tự Động</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Ghi chú điều trị</label>
                        <input type="text" name="notes" class="form-control" placeholder="VD: Nhắc bệnh nhân xét nghiệm đường huyết đói khi đến...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">Kích Hoạt Chương Trình</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
