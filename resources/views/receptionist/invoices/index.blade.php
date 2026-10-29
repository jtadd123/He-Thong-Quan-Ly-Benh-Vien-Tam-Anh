@extends('layouts.admin')

@section('title', 'Sổ Hóa Đơn Viện Phí & Thu Phí Ngoại Trú')

@section('content')
<x-page-header title="Sổ Hóa Đơn Viện Phí & Thu Phí Ngoại Trú (Smart Billing)" subtitle="Quản lý viện phí, đồng chi trả BHYT và thanh toán không tiền mặt qua mã Dynamic VietQR.">
    <x-slot:actions>
        <a href="{{ route('public.billing.lookup') }}" target="_blank" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
            <i class="bi bi-qr-code-scan"></i> Cổng tra cứu viện phí online
        </a>
        <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-calendar-check"></i> Danh sách tiếp nhận
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Quick Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-success-subtle border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Thực Thu Hôm Nay</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($todayStats['total_revenue'], 0, ',', '.') }} VNĐ</h4>
                </div>
                <div class="bg-success text-white p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-primary-subtle border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Hóa Đơn Đã Thu Tiền</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1">{{ $todayStats['paid_count'] }} lượt</h4>
                </div>
                <div class="bg-primary text-white p-3 rounded-circle">
                    <i class="bi bi-receipt-cutoff fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-warning-subtle border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Hóa Đơn Chờ Thanh Toán</span>
                    <h4 class="fw-bold text-warning-emphasis mb-0 mt-1">{{ $todayStats['unpaid_count'] }} đơn</h4>
                </div>
                <div class="bg-warning text-dark p-3 rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reception.invoices.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo mã HĐ, mã BN, tên hoặc SĐT bệnh nhân..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Chờ thanh toán</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="refunded" {{ $status === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Lọc</button>
                @if($search || $status || $date)
                    <a href="{{ route('reception.invoices.index') }}" class="btn btn-outline-secondary btn-sm" title="Xóa lọc">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Mã Hóa Đơn</th>
                        <th>Bệnh Nhân & BHYT</th>
                        <th>Dịch Vụ & Bác Sĩ</th>
                        <th class="text-end">Tổng Viện Phí</th>
                        <th class="text-end">BHYT Chi Trả</th>
                        <th class="text-end">Bệnh Nhân Trả</th>
                        <th class="text-center">Trạng Thái</th>
                        <th class="text-end pe-3" style="width: 130px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php $statusBadge = $inv->payment_status_badge; @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('reception.invoices.show', $inv) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $inv->invoice_code }}
                                </a>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $inv->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $inv->patientProfile->full_name }}</strong>
                                <span class="badge bg-light text-muted border font-monospace">{{ $inv->patientProfile->patient_code }}</span>
                                @if($inv->patientProfile->insurance_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.7rem;">
                                        BHYT: {{ $inv->patientProfile->insurance_code }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($inv->appointment && $inv->appointment->doctor)
                                    <div class="text-dark">{{ $inv->appointment->doctor->full_name_with_title }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $inv->appointment->specialty ? $inv->appointment->specialty->name : 'Ngoại trú' }}</div>
                                @else
                                    <span class="text-muted">Viện phí ngoại trú</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-muted">
                                {{ number_format($inv->total_amount, 0, ',', '.') }} đ
                            </td>
                            <td class="text-end font-monospace text-success fw-semibold">
                                -{{ number_format($inv->insurance_covered_amount, 0, ',', '.') }} đ
                            </td>
                            <td class="text-end font-monospace fw-bold text-danger fs-6">
                                {{ number_format($inv->patient_co_pay_amount, 0, ',', '.') }} đ
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadge['badge_class'] }} px-2 py-1">
                                    <i class="bi {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
                                </span>
                                @if($inv->isPaid())
                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                        {{ $inv->payment_method_text }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('reception.invoices.show', $inv) }}" class="btn btn-outline-primary btn-sm py-1 px-2" title="Xem chi tiết & Thanh toán VietQR">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('reception.invoices.print', $inv) }}" target="_blank" class="btn btn-outline-dark btn-sm py-1 px-2" title="In phiếu thu">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Không tìm thấy hóa đơn viện phí nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
