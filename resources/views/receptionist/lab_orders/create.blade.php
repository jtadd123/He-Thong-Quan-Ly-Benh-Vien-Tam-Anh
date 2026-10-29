@extends('layouts.admin')

@section('title', 'Tạo Phiếu Chỉ Định Cận Lâm Sàng & Xét Nghiệm')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-file-earmark-medical text-primary me-2"></i>Tạo Phiếu Chỉ Định Cận Lâm Sàng
            </h4>
            <p class="text-muted small mb-0">Chỉ định các danh mục xét nghiệm sinh hóa, huyết học, nước tiểu và chẩn đoán hình ảnh kỹ thuật số.</p>
        </div>
        <a href="{{ route('reception.lab-orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reception.lab-orders.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Left: Thông tin bệnh nhân & chẩn đoán -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-3 mb-3">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-person-fill text-primary me-1"></i> 1. Thông Tin Người Bệnh
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn Bệnh Nhân <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn hồ sơ bệnh nhân --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_code }} - {{ $p->full_name }} ({{ $p->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chẩn Đoán Lâm Sàng / Lý Do Khám <span class="text-danger">*</span></label>
                        <textarea name="clinical_diagnosis" class="form-control" rows="3" required placeholder="Ví dụ: Theo dõi sốt xuất huyết Dengue ngày 3 / Đau ngực trái nghi thiếu máu cơ tim...">{{ old('clinical_diagnosis') }}</textarea>
                    </div>

                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Số dịch vụ đã chọn:</span>
                            <strong id="selectedCountText" class="text-primary">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ước tính viện phí:</span>
                            <strong id="estimatedTotalText" class="text-success fs-6">0 VNĐ</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Tạo Chỉ Định
                    </button>
                </div>
            </div>

            <!-- Right: Danh mục Cận lâm sàng -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-card-checklist text-primary me-1"></i> 2. Chọn Dịch Vụ Cận Lâm Sàng & Xét Nghiệm
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Mã / Tên Dịch Vụ</th>
                                    <th>Khoa / Phòng Thực Hiện</th>
                                    <th>Mẫu Bệnh Phẩm</th>
                                    <th class="text-end">Đơn Giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $srv)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" data-price="{{ $srv->price }}" class="form-check-input srv-checkbox" id="srv_{{ $srv->id }}">
                                        </td>
                                        <td>
                                            <label for="srv_{{ $srv->id }}" class="fw-bold text-dark d-block mb-0 cursor-pointer">
                                                {{ $srv->name }}
                                            </label>
                                            <small class="text-muted font-monospace">{{ $srv->code }}</small>
                                            @if($srv->panic_low !== null || $srv->panic_high !== null)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 0.7rem;">Có Ngưỡng Panic</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-secondary">{{ $srv->execution_room }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $srv->sample_type ?: 'Chẩn đoán hình ảnh' }}</small>
                                        </td>
                                        <td class="text-end fw-semibold text-success">
                                            {{ number_format($srv->price) }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.srv-checkbox');
        const countText = document.getElementById('selectedCountText');
        const totalText = document.getElementById('estimatedTotalText');

        function updateTotals() {
            let count = 0;
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    count++;
                    total += parseFloat(cb.dataset.price || 0);
                }
            });
            countText.innerText = count;
            totalText.innerText = total.toLocaleString('vi-VN') + ' VNĐ';
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateTotals));
    });
</script>
@endsection
