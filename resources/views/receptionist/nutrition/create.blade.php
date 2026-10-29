@extends('layouts.admin')

@section('title', 'Đánh Giá Dinh Dưỡng & Tư Vấn Thực Đơn - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-calculator text-success me-2"></i>Đánh Giá Dinh Dưỡng & Thiết Lập Thực Đơn
            </h4>
            <p class="text-muted small mb-0">Tính toán tự động chỉ số nhân trắc BMI, BMR, TDEE và chỉ định phác đồ ăn uống điều trị.</p>
        </div>
        <a href="{{ route('reception.nutrition.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('reception.nutrition.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Left: Thông tin bệnh nhân & số đo -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-person-fill text-success me-1"></i> 1. Thông Tin Bệnh Nhân & Số Đo Nhân Trắc
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_code }} - {{ $p->full_name }} ({{ $p->gender === 'male' ? 'Nam' : 'Nữ' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Chiều cao (cm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="height_cm" id="inputHeight" class="form-control" required placeholder="Ví dụ: 168" value="{{ old('height_cm', '168') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Cân nặng (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="weight_kg" id="inputWeight" class="form-control" required placeholder="Ví dụ: 65" value="{{ old('weight_kg', '65') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bác sĩ / Chuyên gia dinh dưỡng</label>
                        <select name="doctor_id" class="form-select">
                            <option value="">-- Khoa Dinh Dưỡng Ngoại Trú --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->user->name }} - {{ $d->specialty->name ?? 'Khoa Khám Bệnh' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Interactive Results Box -->
                <div class="card border-0 shadow-sm p-4 bg-light border-start border-success border-4">
                    <h6 class="fw-bold text-slate-800 mb-3">
                        <i class="bi bi-speedometer2 text-success me-1"></i> Kết Quả Tính Toán Tức Thì:
                    </h6>
                    <div class="row g-2 mb-3">
                        <div class="col-6 p-2 bg-white rounded border text-center">
                            <small class="text-muted d-block">Chỉ số khối BMI</small>
                            <h3 class="fw-bold text-success mb-0" id="displayBmi">23.0</h3>
                            <span class="badge bg-warning text-dark" id="displayCategory">Tiền béo phì</span>
                        </div>
                        <div class="col-6 p-2 bg-white rounded border text-center">
                            <small class="text-muted d-block">Nhu cầu năng lượng TDEE</small>
                            <h3 class="fw-bold text-primary mb-0" id="displayTdee">1,850</h3>
                            <small class="text-muted">kcal / ngày</small>
                        </div>
                    </div>
                    <div class="p-2 bg-white rounded border small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Chuyển hóa cơ bản (BMR):</span>
                            <strong id="displayBmr" class="text-dark">1,450 kcal</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Lượng nước khuyến nghị:</span>
                            <strong id="displayWater" class="text-info">2,275 ml/ngày</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Chọn phác đồ & Hướng dẫn -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-journal-check text-success me-1"></i> 2. Chọn Phác Đồ Thực Đơn Điều Trị Bệnh Lý
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phác đồ thực đơn mẫu Viện Dinh Dưỡng</label>
                        <select name="diet_regimen_id" id="regimenSelect" class="form-select">
                            <option value="">-- Chế độ ăn lành mạnh tổng quát (Không bệnh lý đặc thù) --</option>
                            @foreach($regimens as $reg)
                                <option value="{{ $reg->id }}" 
                                    data-title="{{ $reg->title }}"
                                    data-disease="{{ $reg->target_disease }}"
                                    data-rec="{{ $reg->foods_recommended }}"
                                    data-res="{{ $reg->foods_restricted }}"
                                    data-avo="{{ $reg->foods_avoided }}"
                                    {{ old('diet_regimen_id') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->title }} ({{ $reg->daily_calorie_target }} kcal)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Regimen Guide Preview Box -->
                    <div id="regimenDetailsBox" class="p-3 bg-light rounded-3 mb-3 d-none">
                        <h6 class="fw-bold text-success mb-2" id="regimenTitle">--</h6>
                        <small class="text-muted d-block mb-3" id="regimenDisease">--</small>
                        
                        <div class="mb-2">
                            <span class="badge bg-success me-1">NÊN ĂN</span>
                            <small class="text-dark" id="regimenRec">--</small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark me-1">HẠN CHẾ</span>
                            <small class="text-dark" id="regimenRes">--</small>
                        </div>
                        <div>
                            <span class="badge bg-danger me-1">KIÊNG TUYỆT ĐỐI</span>
                            <small class="text-dark" id="regimenAvo">--</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lời dặn dò & Lịch tái khám dinh dưỡng</label>
                        <textarea name="custom_instructions" class="form-control" rows="4" placeholder="Ví dụ: Hạn chế ăn mặn, chia nhỏ 5 bữa ăn trong ngày, uống đủ 2 lít nước, tái khám sau 30 ngày cùng xét nghiệm HbA1c...">{{ old('custom_instructions') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold w-100 py-2 mt-auto shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Phiếu Đánh Giá & Xuất Thực Đơn
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hInput = document.getElementById('inputHeight');
        const wInput = document.getElementById('inputWeight');
        const bmiEl = document.getElementById('displayBmi');
        const catEl = document.getElementById('displayCategory');
        const bmrEl = document.getElementById('displayBmr');
        const tdeeEl = document.getElementById('displayTdee');
        const waterEl = document.getElementById('displayWater');

        function calc() {
            const h = parseFloat(hInput.value) || 0;
            const w = parseFloat(wInput.value) || 0;
            if (h > 0 && w > 0) {
                const hm = h / 100.0;
                const bmi = (w / (hm * hm)).toFixed(1);
                bmiEl.innerText = bmi;

                let cat = 'Bình thường';
                let catClass = 'badge bg-success';
                if (bmi < 18.5) {
                    cat = 'Thiếu cân (Gầy)';
                    catClass = 'badge bg-secondary';
                } else if (bmi <= 22.9) {
                    cat = 'Thể trạng Bình thường';
                    catClass = 'badge bg-success';
                } else if (bmi <= 24.9) {
                    cat = 'Tiền béo phì (Thừa cân)';
                    catClass = 'badge bg-warning text-dark';
                } else if (bmi <= 29.9) {
                    cat = 'Béo phì độ I';
                    catClass = 'badge bg-danger';
                } else {
                    cat = 'Béo phì độ II';
                    catClass = 'badge bg-danger';
                }
                catEl.innerText = cat;
                catEl.className = catClass;

                // BMR Mifflin-St Jeor (estimate age 35, male)
                const bmr = Math.round((10 * w) + (6.25 * h) - (5 * 35) + 5);
                const tdee = Math.round(bmr * 1.375);
                const water = Math.round(w * 35);

                bmrEl.innerText = bmr.toLocaleString('vi-VN') + ' kcal';
                tdeeEl.innerText = tdee.toLocaleString('vi-VN');
                waterEl.innerText = water.toLocaleString('vi-VN') + ' ml/ngày';
            }
        }

        hInput.addEventListener('input', calc);
        wInput.addEventListener('input', calc);
        calc();

        // Regimen Preview Change
        const regSelect = document.getElementById('regimenSelect');
        const regBox = document.getElementById('regimenDetailsBox');
        const regTitle = document.getElementById('regimenTitle');
        const regDisease = document.getElementById('regimenDisease');
        const regRec = document.getElementById('regimenRec');
        const regRes = document.getElementById('regimenRes');
        const regAvo = document.getElementById('regimenAvo');

        regSelect.addEventListener('change', function () {
            const opt = regSelect.options[regSelect.selectedIndex];
            if (opt && opt.value) {
                regTitle.innerText = opt.dataset.title;
                regDisease.innerText = 'Chỉ định: ' + opt.dataset.disease;
                regRec.innerText = opt.dataset.rec;
                regRes.innerText = opt.dataset.res;
                regAvo.innerText = opt.dataset.avo;
                regBox.classList.remove('d-none');
            } else {
                regBox.classList.add('d-none');
            }
        });
    });
</script>
@endsection
