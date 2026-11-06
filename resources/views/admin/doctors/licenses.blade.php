@extends('layouts.admin')

@section('title', 'Quản Lý Chứng Chỉ Hành Nghề (CCHN) Bác Sĩ')

@section('content')
<x-page-header title="Quản Lý Chứng Chỉ Hành Nghề & Giấy Phép Y Tế" subtitle="Theo dõi tính pháp lý hành nghề khám chữa bệnh, thời hạn giấy phép CCHN và cảnh báo gia hạn CME định kỳ theo quy định Bộ Y Tế">
    <x-slot:actions>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Về danh sách bác sĩ
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Alert Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">CCHN Hợp Lệ An Toàn</span>
                    <h4 class="fw-bold text-success mb-0">{{ $validCount }} Bác sĩ</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <span class="text-muted small">Sắp hết hạn (&le; 30 ngày)</span>
                    <h4 class="fw-bold text-warning mb-0">{{ $expiringSoon->count() }} Bác sĩ</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Đã quá hạn giấy phép</span>
                    <h4 class="fw-bold text-danger mb-0">{{ $expired->count() }} Bác sĩ</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warning Banners If Any Expiry Detected -->
@if($expired->isNotEmpty())
    <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-1 text-danger">CẢNH BÁO PHÁP LÝ: PHÁT HIỆN {{ $expired->count() }} BÁC SĨ ĐÃ QUÁ HẠN CHỨNG CHỈ HÀNH NGHỀ!</h6>
            <p class="small mb-0 text-danger-emphasis">
                Theo Luật Khám bệnh, chữa bệnh số 15/2023/QH15, bác sĩ có giấy phép hết hạn không được phân công ký hồ sơ bệnh án và đơn thuốc.
            </p>
        </div>
    </div>
@endif

@if($expiringSoon->isNotEmpty())
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
        <i class="bi bi-bell-fill fs-2 text-warning"></i>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-1 text-warning-emphasis">NHẮC NHỞ GIA HẠN: CÓ {{ $expiringSoon->count() }} BÁC SĨ SẮP HẾT HẠN CCHN TRONG 30 NGÀY TỚI</h6>
            <p class="small mb-0 text-warning-emphasis">
                Vui lòng đôn đốc bác sĩ hoàn thành tín chỉ Đào tạo y khoa liên tục (CME) và nộp hồ sơ gia hạn lên Bộ Y Tế.
            </p>
        </div>
    </div>
@endif

<!-- Licenses Table -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-card-checklist text-primary me-2"></i> Danh Sách Chi Tiết Giấy Phép CCHN Bác Sĩ
        </h6>
        <span class="badge bg-light text-muted border">Tổng số: {{ $doctors->count() }} hồ sơ</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 60px;">ID</th>
                        <th>Bác sĩ</th>
                        <th>Chuyên khoa</th>
                        <th>Số CCHN</th>
                        <th>Cơ quan cấp</th>
                        <th>Ngày cấp</th>
                        <th>Ngày hết hạn</th>
                        <th class="text-center">Tình trạng pháp lý</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($doctors as $d)
                        @php
                            $isExpiring = $d->is_cchn_expiring_soon;
                            $isExpired = $d->is_cchn_expired;
                        @endphp
                        <tr class="{{ $isExpired ? 'table-danger' : ($isExpiring ? 'table-warning' : '') }}">
                            <td class="ps-3 fw-bold text-muted">#{{ $d->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $d->full_name_with_title }}</div>
                                <small class="text-muted">{{ $d->user?->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                    {{ $d->specialty?->name }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold font-monospace text-dark">{{ $d->cchn_number ?: 'Chưa cập nhật' }}</span>
                            </td>
                            <td class="text-muted small">
                                {{ $d->cchn_issuer ?: 'Bộ Y Tế' }}
                            </td>
                            <td class="text-muted small">
                                {{ $d->cchn_issued_date ? $d->cchn_issued_date->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                @if($d->cchn_expiry_date)
                                    <span class="fw-semibold {{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : 'text-dark') }}">
                                        {{ $d->cchn_expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($isExpiring)
                                        <small class="d-block text-warning fw-bold">Còn {{ $d->days_until_cchn_expiry }} ngày</small>
                                    @elseif($isExpired)
                                        <small class="d-block text-danger fw-bold">Quá hạn {{ abs($d->days_until_cchn_expiry) }} ngày</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isExpired)
                                    <span class="badge bg-danger px-2 py-1 rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i> ĐÃ HẾT HẠN
                                    </span>
                                @elseif($isExpiring)
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">
                                        <i class="bi bi-clock-history me-1"></i> SẮP HẾT HẠN
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> HỢP LỆ
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-renew-license shadow-xs" 
                                    data-doctor-id="{{ $d->id }}" 
                                    data-doctor-name="{{ $d->full_name_with_title }}" 
                                    data-cchn="{{ $d->cchn_number }}" 
                                    data-issuer="{{ $d->cchn_issuer ?: 'Bộ Y Tế' }}" 
                                    data-issued="{{ $d->cchn_issued_date ? $d->cchn_issued_date->format('Y-m-d') : date('Y-m-d') }}" 
                                    data-expiry="{{ $d->cchn_expiry_date ? $d->cchn_expiry_date->format('Y-m-d') : date('Y-m-d', strtotime('+5 years')) }}">
                                    <i class="bi bi-shield-check me-1"></i> Gia hạn
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Gia Hạn & Cập Nhật CCHN Bác Sĩ -->
<div class="modal fade" id="renewLicenseModal" tabindex="-1" aria-labelledby="renewLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="renewLicenseModalLabel">
                    <i class="bi bi-shield-check me-2"></i> Gia Hạn Chứng Chỉ Hành Nghề (CCHN)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="renewLicenseForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <span class="text-muted small d-block">Bác sĩ tiếp nhận gia hạn:</span>
                        <h6 class="fw-bold text-primary mb-0" id="modalDoctorName">...</h6>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Số Chứng Chỉ Hành Nghề (CCHN) <span class="text-danger">*</span></label>
                        <input type="text" name="cchn_number" id="modalCchnNumber" class="form-control font-monospace" placeholder="Ví dụ: 001245/BYT-CCHN" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Cơ quan cấp phép <span class="text-danger">*</span></label>
                        <select name="cchn_issuer" id="modalCchnIssuer" class="form-select" required>
                            <option value="Bộ Y Tế">Bộ Y Tế</option>
                            <option value="Sở Y Tế TP. Hà Nội">Sở Y Tế TP. Hà Nội</option>
                            <option value="Sở Y Tế TP. Hồ Chí Minh">Sở Y Tế TP. Hồ Chí Minh</option>
                            <option value="Tổng Cục Hậu Cần - Bộ Quốc Phòng">Tổng Cục Hậu Cần - Bộ Quốc Phòng</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-dark">Ngày cấp CCHN <span class="text-danger">*</span></label>
                            <input type="date" name="cchn_issued_date" id="modalCchnIssued" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-dark">Ngày hết hạn CCHN <span class="text-danger">*</span></label>
                            <input type="date" name="cchn_expiry_date" id="modalCchnExpiry" class="form-control" required>
                        </div>
                    </div>

                    <!-- Quick Extend Buttons -->
                    <div class="mb-3 p-3 bg-light bg-opacity-50 border rounded-3">
                        <span class="d-block small text-muted mb-2 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Gia hạn nhanh thời hạn hiệu lực:</span>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-add-years" data-years="1">+1 Năm</button>
                            <button type="button" class="btn btn-outline-secondary btn-add-years" data-years="2">+2 Năm</button>
                            <button type="button" class="btn btn-outline-primary btn-add-years" data-years="5">+5 Năm (Chuẩn Bộ Y Tế)</button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark">Số giờ đào tạo y khoa liên tục (CME) tích lũy</label>
                        <div class="input-group">
                            <input type="number" name="cme_hours" id="modalCmeHours" class="form-control" value="48" min="0" max="500">
                            <span class="input-group-text bg-light text-muted small">giờ / 48 giờ tối thiểu</span>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">* Thông tư 32/2023/TT-BYT: Bác sĩ cần tối thiểu 120 tiết / 5 năm để đủ điều kiện gia hạn giấy phép hành nghề.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Xác Nhận Lưu & Gia Hạn CCHN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('renewLicenseModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('renewLicenseForm');
    const nameEl = document.getElementById('modalDoctorName');
    const cchnInput = document.getElementById('modalCchnNumber');
    const issuerInput = document.getElementById('modalCchnIssuer');
    const issuedInput = document.getElementById('modalCchnIssued');
    const expiryInput = document.getElementById('modalCchnExpiry');

    document.querySelectorAll('.btn-renew-license').forEach(btn => {
        btn.addEventListener('click', function () {
            const docId = this.dataset.doctorId;
            const docName = this.dataset.doctorName;
            const cchn = this.dataset.cchn;
            const issuer = this.dataset.issuer;
            const issued = this.dataset.issued;
            const expiry = this.dataset.expiry;

            form.action = `{{ url('admin/doctors') }}/${docId}/licenses`;
            nameEl.textContent = docName;
            cchnInput.value = (cchn && cchn !== 'Chưa cập nhật') ? cchn : `00${1000 + parseInt(docId)}/BYT-CCHN`;
            if (issuer) issuerInput.value = issuer;
            if (issued) issuedInput.value = issued;
            if (expiry) expiryInput.value = expiry;

            modal.show();
        });
    });

    document.querySelectorAll('.btn-add-years').forEach(btn => {
        btn.addEventListener('click', function () {
            const addYears = parseInt(this.dataset.years) || 1;
            let baseDate = new Date();
            if (expiryInput.value) {
                const cur = new Date(expiryInput.value);
                if (!isNaN(cur.getTime()) && cur > baseDate) {
                    baseDate = cur;
                }
            }
            baseDate.setFullYear(baseDate.getFullYear() + addYears);
            const yyyy = baseDate.getFullYear();
            const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
            const dd = String(baseDate.getDate()).padStart(2, '0');
            expiryInput.value = `${yyyy}-${mm}-${dd}`;
        });
    });
});
</script>
@endpush
@endsection
