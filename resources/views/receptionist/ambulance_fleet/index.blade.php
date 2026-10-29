@extends('layouts.admin')

@section('title', 'Điều Phối Đội Xe Cấp Cứu Ngoại Viện')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-danger">
                <i class="bi bi-truck-flatbed me-2"></i>Điều Phối Đội Xe Cấp Cứu Ngoại Viện & Chuyển Tuyến
            </h4>
            <p class="text-muted small mb-0">Hệ thống điều phối xe cứu thương Mobile ICU, theo dõi lộ trình thời gian thực - Bệnh Viện Tâm Anh</p>
        </div>
        <div>
            <button type="button" class="btn btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newMissionModal">
                <i class="bi bi-bell-fill"></i> Điều Động Xe Cấp Cứu Mới
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
                            <div class="text-muted small fw-semibold">Tổng Đội Xe Cấp Cứu</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total_ambulances'] }} xe</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-truck fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Xe Sẵn Sàng Xuất Bến</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['available_ambulances'] }} xe</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Ca Đang Làm Nhiệm Vụ</div>
                            <div class="fs-4 fw-bold text-danger">{{ $stats['active_missions'] }} ca</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-secondary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Ca Hoàn Thành Xuất Sắc</div>
                            <div class="fs-4 fw-bold text-secondary">{{ $stats['completed_missions'] }}</div>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fleet Status Visual Cards -->
    <div class="row g-3 mb-4">
        @foreach($ambulances as $amb)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm {{ $amb->status === 'dispatched' ? 'border-start border-danger border-4' : 'border-start border-success border-4' }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold font-monospace fs-5 text-dark">{{ $amb->license_plate }}</span>
                            {!! $amb->status_badge !!}
                        </div>
                        <div class="small text-muted mb-2">
                            Loại xe: <strong class="text-dark">{{ $amb->ambulance_type === 'mobile_icu' ? 'Hồi Sức Cấp Cứu Di Động (Mobile ICU)' : 'Cấp Cứu Tiêu Chuẩn' }}</strong>
                        </div>
                        <div class="small text-muted mb-2">
                            Tài xế trực: <strong>{{ $amb->driver_name }}</strong> (SĐT: {{ $amb->driver_phone }})
                        </div>
                        <div class="small text-secondary">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>Vị trí: {{ $amb->current_location }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Active Missions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Lịch Sử Điều Phối Lộ Trình Cấp Cứu Ngoại Viện
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Chuyến</th>
                            <th>Xe Đảm Nhận</th>
                            <th>Người Gọi / Người Bệnh</th>
                            <th>Điểm Đón & Điểm Đến</th>
                            <th>Cự Ly & Chi Phí</th>
                            <th>Kíp Cấp Cứu Đi Kèm</th>
                            <th>Trạng Thái Lộ Trình</th>
                            <th class="text-end pe-3">Cập Nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($missions as $m)
                            <tr>
                                <td class="ps-3 fw-bold text-danger font-monospace">{{ $m->mission_code }}</td>
                                <td>
                                    <span class="badge bg-dark font-monospace">{{ $m->ambulance->license_plate }}</span>
                                    <div class="small text-muted">{{ $m->ambulance->driver_name }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $m->caller_name }}</div>
                                    <small class="text-muted">SĐT: {{ $m->caller_phone }}</small>
                                    @if($m->patientProfile)
                                        <div class="small text-primary">BN: {{ $m->patientProfile->full_name }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    <div><strong>Đón:</strong> {{ $m->pickup_address }}</div>
                                    <div class="text-muted"><strong>Đến:</strong> {{ $m->destination_address }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->distance_km }} km</div>
                                    <div class="small text-success fw-bold">{{ number_format($m->total_cost, 0, ',', '.') }} đ</div>
                                </td>
                                <td class="small">
                                    <div>BS: {{ $m->doctor_in_charge ?: 'BS Cấp Cứu Trực' }}</div>
                                    <div>ĐD: {{ $m->nurse_in_charge ?: 'ĐD Hồi Sức Trực' }}</div>
                                </td>
                                <td>{!! $m->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    @if($m->status !== 'completed' && $m->status !== 'cancelled')
                                        <form action="{{ route('reception.ambulances.status', $m) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="dispatched" {{ $m->status === 'dispatched' ? 'selected' : '' }}>Vừa xuất bến</option>
                                                <option value="en_route" {{ $m->status === 'en_route' ? 'selected' : '' }}>Đang di chuyển</option>
                                                <option value="on_scene" {{ $m->status === 'on_scene' ? 'selected' : '' }}>Tại hiện trường</option>
                                                <option value="transporting" {{ $m->status === 'transporting' ? 'selected' : '' }}>Đang chuyển viện</option>
                                                <option value="completed">Đã hoàn thành ca</option>
                                                <option value="cancelled">Hủy chuyến</option>
                                            </select>
                                        </form>
                                    @else
                                        <span class="text-muted small">Hoàn tất lúc {{ $m->completed_at?->format('H:i d/m') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Chưa có chuyến xe cấp cứu nào được điều phối.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($missions->hasPages())
                <div class="p-3 border-top">{{ $missions->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Điều Động Xe Cấp Cứu Mới -->
<div class="modal fade" id="newMissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.ambulances.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-truck me-2"></i>Lệnh Điều Động Xe Cấp Cứu Ngoại Viện</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn xe cấp cứu trực chiến <span class="text-danger">*</span></label>
                        <select name="ambulance_id" class="form-select" required>
                            @foreach($ambulances->where('status', 'available') as $a)
                                <option value="{{ $a->id }}">{{ $a->license_plate }} ({{ $a->ambulance_type === 'mobile_icu' ? 'Mobile ICU' : 'Tiêu chuẩn' }} - TX: {{ $a->driver_name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên người gọi cấp cứu <span class="text-danger">*</span></label>
                            <input type="text" name="caller_name" class="form-control" placeholder="VD: Anh Tuấn" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SĐT người gọi <span class="text-danger">*</span></label>
                            <input type="text" name="caller_phone" class="form-control" placeholder="VD: 0912999888" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ đón người bệnh <span class="text-danger">*</span></label>
                        <input type="text" name="pickup_address" class="form-control" placeholder="VD: Số 12 phố Huế, Hai Bà Trưng..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Điểm chuyển đến <span class="text-danger">*</span></label>
                        <input type="text" name="destination_address" value="Khoa Cấp Cứu - Bệnh Viện Tâm Anh" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mục đích điều xe <span class="text-danger">*</span></label>
                            <select name="mission_type" class="form-select" required>
                                <option value="home_pickup">Đón cấp cứu tại nhà</option>
                                <option value="transfer_out">Chuyển viện tuyến trên</option>
                                <option value="repatriation">Đưa bệnh nhân xuất viện về tỉnh</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ước tính cự ly (km) <span class="text-danger">*</span></label>
                            <input type="number" name="distance_km" value="15" min="1" step="0.5" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bác sĩ cấp cứu theo xe</label>
                            <input type="text" name="doctor_in_charge" class="form-control" placeholder="VD: BS. Nguyễn Hoàng Nam">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Điều dưỡng theo xe</label>
                            <input type="text" name="nurse_in_charge" class="form-control" placeholder="VD: ĐD. Phạm Mai Linh">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold">Xuất Bến Khẩn Cấp</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
