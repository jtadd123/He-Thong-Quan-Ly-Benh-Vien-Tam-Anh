@extends('layouts.admin')

@section('title', 'Đăng Ký Mũi Tiêm Vắc-Xin Mới')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('reception.vaccinations.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Trở lại danh sách
        </a>
        <h4 class="fw-bold mb-0 text-slate-800">
            Đăng Ký Mũi Tiêm & Khám Sàng Lọc Trước Tiêm
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-shield-plus me-2"></i>Phiếu Chỉ Định Tiêm Chủng (Quy Chuẩn An Toàn Bộ Y Tế)
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('reception.vaccinations.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Bệnh nhân -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Người tiêm chủng <span class="text-danger">*</span></label>
                        @if($patient)
                            <input type="hidden" name="patient_profile_id" value="{{ $patient->id }}">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-primary mb-1">{{ $patient->full_name }}</h6>
                                <p class="small text-muted mb-0">
                                    Mã: <strong>{{ $patient->patient_code }}</strong> • {{ $patient->age_text }} • {{ $patient->gender_name }} • SĐT: {{ $patient->phone }}
                                </p>
                            </div>
                        @else
                            <select name="patient_profile_id" class="form-select" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->full_name }} ({{ $p->patient_code }} - {{ $p->phone }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Loại vắc-xin -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Loại Vắc-xin chỉ định <span class="text-danger">*</span></label>
                        <select name="vaccine_id" class="form-select" required>
                            <option value="">-- Chọn loại vắc-xin --</option>
                            @foreach($vaccines as $vac)
                                <option value="{{ $vac->id }}" {{ old('vaccine_id') == $vac->id ? 'selected' : '' }}>
                                    {{ $vac->name }} ({{ $vac->origin_country }} - {{ number_format($vac->price) }}đ)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mũi thứ mấy -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mũi tiêm số <span class="text-danger">*</span></label>
                        <input type="number" name="dose_number" class="form-control" value="{{ old('dose_number', 1) }}" min="1" max="10" required>
                    </div>

                    <!-- Số lô -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số lô vắc-xin (Batch/Lot) <span class="text-danger">*</span></label>
                        <input type="text" name="batch_number" class="form-control" value="{{ old('batch_number', 'LOT-' . date('Ym') . '-V1') }}" required>
                    </div>

                    <!-- Hạn dùng lô -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Hạn sử dụng của lô</label>
                        <input type="date" name="batch_expiry_date" class="form-control" value="{{ old('batch_expiry_date', date('Y-m-d', strtotime('+1 year'))) }}">
                    </div>

                    <!-- Vị trí tiêm -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Đường tiêm & Vị trí tiêm <span class="text-danger">*</span></label>
                        <select name="injection_site" class="form-select" required>
                            <option value="Cơ delta cánh tay trái">Cơ delta cánh tay trái (Bắp)</option>
                            <option value="Cơ delta cánh tay phải">Cơ delta cánh tay phải (Bắp)</option>
                            <option value="Mặt trước ngoài đùi trái">Mặt trước ngoài đùi trái (Trẻ em)</option>
                            <option value="Mặt trước ngoài đùi phải">Mặt trước ngoài đùi phải (Trẻ em)</option>
                            <option value="Dưới da cánh tay trái">Dưới da cánh tay trái (Varivax)</option>
                            <option value="Đường uống">Đường uống (Rotavirus)</option>
                        </select>
                    </div>

                    <!-- Khám sàng lọc: Thân nhiệt -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Thân nhiệt đo được (°C) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="body_temperature" class="form-control" value="{{ old('body_temperature', 36.5) }}" min="35.0" max="42.0" required>
                            <span class="input-group-text">°C</span>
                        </div>
                        <small class="text-muted">Bộ Y Tế: Bệnh nhân sốt $\ge 38.0^\circ\text{C}$ phải hoãn tiêm.</small>
                    </div>

                    <!-- Ghi chú sàng lọc -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Ghi chú khám sàng lọc trước tiêm</label>
                        <textarea name="screening_notes" class="form-control" rows="3" placeholder="Không sốt, tim phổi bình thường, không tiền sử phản vệ vắc-xin, đủ điều kiện tiêm chủng.">{{ old('screening_notes', 'Khám sàng lọc: Thân nhiệt ổn định, nhịp thở đều, không có dấu hiệu dị ứng cấp tính, đủ điều kiện tiêm chủng.') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('reception.vaccinations.index') }}" class="btn btn-secondary px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-eyedropper me-1"></i> Xác Nhận Tiêm & Chuyển Theo Dõi 30 Phút
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
