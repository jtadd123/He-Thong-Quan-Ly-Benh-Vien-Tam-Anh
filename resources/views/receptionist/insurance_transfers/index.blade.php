@extends('layouts.admin')

@section('title', 'Quản Lý Giấy Chuyển Tuyến BHYT - Mẫu 40/BYT')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-file-earmark-medical me-2"></i>Cổng Quản Lý Giấy Chuyển Tuyến BHYT
            </h4>
            <p class="text-muted small mb-0">Theo dõi, thẩm định và liên thông Giấy chuyển viện BHYT (Mẫu số 40/BYT) - Bệnh Viện Tâm Anh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.insurance-transfers.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Tiếp Nhận Giấy Chuyển Tuyến
            </a>
        </div>
    </div>

    <!-- Stats KPI -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Giấy Tiếp Nhận</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-files fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Còn Hiệu Lực</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['valid'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-shield-check fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đã Sử Dụng KCB</div>
                            <div class="fs-4 fw-bold text-secondary">{{ $stats['used'] }}</div>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
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
                            <div class="text-muted small fw-semibold">Hết Hạn / Quá Hạn</div>
                            <div class="fs-4 fw-bold text-danger">{{ $stats['expired'] }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.insurance-transfers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Tìm theo Mã giấy, Tên BN, Mã thẻ BHYT, Bệnh viện chuyển...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="valid" {{ $status === 'valid' ? 'selected' : '' }}>Còn hiệu lực</option>
                        <option value="used" {{ $status === 'used' ? 'selected' : '' }}>Đã sử dụng</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Hết hiệu lực</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    @if($search || $status)
                        <a href="{{ route('reception.insurance-transfers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Chuyển Tuyến</th>
                            <th>Bệnh Nhân</th>
                            <th>Mã Thẻ BHYT</th>
                            <th>Nơi Chuyển Đi</th>
                            <th>Chẩn Đoán Nơi Đi</th>
                            <th>Thời Hạn Hiệu Lực</th>
                            <th>Mức Hưởng</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $t)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $t->transfer_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $t->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $t->patientProfile->patient_code }} | {{ $t->patientProfile->gender_name }} | {{ $t->patientProfile->age_text }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace border">{{ $t->patientProfile->insurance_code }}</span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-truncate" style="max-width: 180px;" title="{{ $t->issuing_hospital_name }}">{{ $t->issuing_hospital_name }}</div>
                                    <small class="text-muted">Mã KCB: {{ $t->issuing_hospital_code }}</small>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 180px;" title="{{ $t->icd10_diagnosis }}">{{ $t->icd10_diagnosis }}</div>
                                    @if($t->icd10_code)
                                        <span class="badge bg-secondary font-monospace" style="font-size: 0.7rem;">ICD: {{ $t->icd10_code }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    <div>{{ $t->valid_from->format('d/m/Y') }} &rarr; {{ $t->valid_to->format('d/m/Y') }}</div>
                                    @if($t->is_valid)
                                        <small class="text-success fw-semibold"><i class="bi bi-clock me-1"></i>Hợp lệ</small>
                                    @else
                                        <small class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Hết hạn</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark fw-bold">{{ $t->bhyt_reimbursement_rate }}%</span>
                                </td>
                                <td>{!! $t->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('reception.insurance-transfers.show', $t) }}" class="btn btn-outline-primary" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('reception.insurance-transfers.print', $t) }}" target="_blank" class="btn btn-outline-secondary" title="In phiếu xác nhận">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Chưa có giấy chuyển tuyến nào được ghi nhận.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
                <div class="p-3 border-top">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
