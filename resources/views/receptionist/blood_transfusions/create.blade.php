@extends('layouts.admin')

@section('title', 'Chuẩn Bị Ca Truyền Máu & Kiểm Tra Hòa Hợp Chéo - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-shield-check text-danger me-2"></i>Quy Trình Chuẩn Bị Truyền Máu An Toàn
            </h4>
            <p class="text-muted small mb-0">Thực hiện kiểm tra hòa hợp nhóm máu ABO / Rh và đối chiếu kép (Dual Verification) theo tiêu chuẩn Bộ Y Tế.</p>
        </div>
        <a href="{{ route('reception.blood-transfusions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    <form action="{{ route('reception.blood-transfusions.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Left: Bệnh nhân & Chỉ định -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-person-fill text-danger me-1"></i> 1. Thông Tin Người Bệnh & Nhóm Máu
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh tiếp nhận <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->patient_code }} - {{ $p->full_name }} ({{ $p->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Nhóm máu ABO của BN <span class="text-danger">*</span></label>
                            <select name="patient_abo" id="patientAboSelect" class="form-select" required>
                                <option value="O" {{ old('patient_abo') === 'O' ? 'selected' : '' }}>Nhóm O</option>
                                <option value="A" {{ old('patient_abo') === 'A' ? 'selected' : '' }}>Nhóm A</option>
                                <option value="B" {{ old('patient_abo') === 'B' ? 'selected' : '' }}>Nhóm B</option>
                                <option value="AB" {{ old('patient_abo') === 'AB' ? 'selected' : '' }}>Nhóm AB</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Yếu tố Rhesus (Rh) <span class="text-danger">*</span></label>
                            <select name="patient_rh" id="patientRhSelect" class="form-select" required>
                                <option value="positive" {{ old('patient_rh') === 'positive' ? 'selected' : '' }}>Rh Dương (+)</option>
                                <option value="negative" {{ old('patient_rh') === 'negative' ? 'selected' : '' }}>Rh Âm (-) [Máu Hiếm]</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chỉ Định Truyền Máu Lâm Sàng <span class="text-danger">*</span></label>
                        <input type="text" name="indication" class="form-control" required placeholder="Ví dụ: Thiếu máu cấp do xuất huyết tiêu hóa / Hb &lt; 70g/L..." value="{{ old('indication') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bác sĩ chỉ định</label>
                        <select name="doctor_id" class="form-select">
                            <option value="">-- Chọn bác sĩ điều trị --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->user->name }} - {{ $d->specialty->name ?? 'Khoa Cấp Cứu' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Sinh hiệu ban đầu -->
                <div class="card border-0 shadow-sm p-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-activity text-danger me-1"></i> 2. Sinh Hiệu Trước Khi Truyền (Baseline Vitals)
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label small text-muted">Huyết áp (mmHg)</label>
                            <input type="text" name="blood_pressure" class="form-control" placeholder="120/80" value="{{ old('blood_pressure', '120/80') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Mạch (ck/p)</label>
                            <input type="text" name="heart_rate" class="form-control" placeholder="80" value="{{ old('heart_rate', '80') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Thân nhiệt (°C)</label>
                            <input type="text" name="temperature" class="form-control" placeholder="36.8" value="{{ old('temperature', '36.8') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Chọn túi máu & Đối chiếu kép 2 Điều dưỡng -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-box2-heart text-danger me-1"></i> 3. Chọn Túi Máu Trong Ngân Hàng Máu
                    </h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh sách túi máu sẵn có <span class="text-danger">*</span></label>
                        <select name="blood_unit_id" id="bloodUnitSelect" class="form-select" required>
                            <option value="">-- Chọn túi máu từ kho --</option>
                            @foreach($availableUnits as $unit)
                                <option value="{{ $unit->id }}" 
                                    data-abo="{{ $unit->blood_type_abo }}" 
                                    data-rh="{{ $unit->blood_type_rh }}"
                                    data-product="{{ $unit->product_type }}"
                                    {{ old('blood_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->unit_code }} | {{ $unit->full_group }} | {{ $unit->product_name }} ({{ $unit->volume_ml }}ml) - HSD: {{ $unit->expiry_date->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Live Crossmatch Box -->
                    <div id="crossmatchBox" class="p-3 rounded-3 border mb-3 bg-light">
                        <strong class="d-block mb-1 text-secondary small text-uppercase">Kết Quả Đối Chiếu Miễn Dịch Nhanh:</strong>
                        <div id="crossmatchMessage" class="fw-bold text-muted small">
                            Vui lòng chọn bệnh nhân và túi máu để đối chiếu.
                        </div>
                    </div>
                </div>

                <!-- Dual Sign-off Section -->
                <div class="card border-0 shadow-sm p-4 border-start border-warning border-4">
                    <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                        <i class="bi bi-people-fill text-warning me-1"></i> 4. Xác Thực Kép Tại Giường (Dual Sign-Off)
                    </h6>
                    <p class="small text-muted mb-3">
                        Quy định Bệnh Viện Tâm Anh: Bắt buộc 2 nhân viên y tế độc lập đối chiếu chéo thông tin trên túi máu và thẻ định danh bệnh nhân.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Điều Dưỡng Đối Chiếu 1 <span class="text-danger">*</span></label>
                        <select name="nurse_user_id_1" class="form-select" required>
                            <option value="">-- Chọn điều dưỡng 1 --</option>
                            @foreach($nurses as $nurse)
                                <option value="{{ $nurse->id }}" {{ old('nurse_user_id_1') == $nurse->id ? 'selected' : '' }}>
                                    {{ $nurse->name }} ({{ $nurse->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Điều Dưỡng Đối Chiếu 2 <span class="text-danger">*</span></label>
                        <select name="nurse_user_id_2" class="form-select" required>
                            <option value="">-- Chọn điều dưỡng 2 (Người thứ hai độc lập) --</option>
                            @foreach($nurses as $nurse)
                                <option value="{{ $nurse->id }}" {{ old('nurse_user_id_2') == $nurse->id ? 'selected' : '' }}>
                                    {{ $nurse->name }} ({{ $nurse->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-bold py-2 mt-2 shadow-sm">
                        <i class="bi bi-shield-lock-fill me-1"></i> Ký Duyệt & Xuất Máu Lâm Sàng
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const patientAbo = document.getElementById('patientAboSelect');
        const patientRh = document.getElementById('patientRhSelect');
        const bloodSelect = document.getElementById('bloodUnitSelect');
        const box = document.getElementById('crossmatchBox');
        const msg = document.getElementById('crossmatchMessage');

        function checkCompat() {
            const pAbo = patientAbo.value;
            const pRh = patientRh.value;
            const selectedOpt = bloodSelect.options[bloodSelect.selectedIndex];

            if (!selectedOpt || !selectedOpt.value) {
                box.className = 'p-3 rounded-3 border mb-3 bg-light';
                msg.innerText = 'Vui lòng chọn túi máu để kiểm tra tương thích.';
                msg.className = 'fw-bold text-muted small';
                return;
            }

            const dAbo = selectedOpt.dataset.abo;
            const dRh = selectedOpt.dataset.rh;
            const prod = selectedOpt.dataset.product;

            // Rh check
            if (pRh === 'negative' && dRh === 'positive') {
                box.className = 'p-3 rounded-3 border mb-3 bg-danger-subtle border-danger';
                msg.className = 'fw-bold text-danger small';
                msg.innerText = '❌ BẤT ĐỒNG RHESUS: Người nhận Rh(-) KHÔNG ĐƯỢC nhận túi máu Rh(+) vì nguy cơ ngưng kết tiêu huyết cấp!';
                return;
            }

            // ABO check for PRBC
            let ok = false;
            if (prod === 'prbc' || prod === 'platelet') {
                if (pAbo === 'O' && dAbo === 'O') ok = true;
                if (pAbo === 'A' && (dAbo === 'A' || dAbo === 'O')) ok = true;
                if (pAbo === 'B' && (dAbo === 'B' || dAbo === 'O')) ok = true;
                if (pAbo === 'AB') ok = true;
            } else {
                // FFP
                if (pAbo === 'O') ok = true;
                if (pAbo === 'A' && (dAbo === 'A' || dAbo === 'AB')) ok = true;
                if (pAbo === 'B' && (dAbo === 'B' || dAbo === 'AB')) ok = true;
                if (pAbo === 'AB' && dAbo === 'AB') ok = true;
            }

            if (ok) {
                box.className = 'p-3 rounded-3 border mb-3 bg-success-subtle border-success';
                msg.className = 'fw-bold text-success small';
                msg.innerText = `✅ TƯƠNG THÍCH HOÀN HẢO: Bệnh nhân nhóm ${pAbo} Rh(${pRh === 'positive' ? '+' : '-'}) nhận an toàn túi máu ${dAbo} Rh(${dRh === 'positive' ? '+' : '-'}).`;
            } else {
                box.className = 'p-3 rounded-3 border mb-3 bg-danger-subtle border-danger';
                msg.className = 'fw-bold text-danger small';
                msg.innerText = `❌ BẤT ĐỒNG ABO NGUY HIỂM: Bệnh nhân nhóm ${pAbo} KHÔNG THỂ nhận túi máu nhóm ${dAbo}!`;
            }
        }

        patientAbo.addEventListener('change', checkCompat);
        patientRh.addEventListener('change', checkCompat);
        bloodSelect.addEventListener('change', checkCompat);
    });
</script>
@endsection
