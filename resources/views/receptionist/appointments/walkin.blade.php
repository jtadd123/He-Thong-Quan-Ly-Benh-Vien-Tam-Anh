@extends('layouts.admin')

@section('title', 'Tiếp Nhận Bệnh Nhân Tại Quầy (Walk-in) - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Tiếp Nhận Bệnh Nhân Trực Tiếp Tại Quầy (Walk-in)" subtitle="Dành cho bệnh nhân đến trực tiếp bệnh viện chưa đăng ký trước qua mạng. Hệ thống tự động Check-in vào phòng khám sau khi lưu.">
    <x-slot:actions>
        <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Bàn tiếp nhận
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <form method="POST" action="{{ route('reception.appointments.store-walkin') }}" id="walkinForm" novalidate>
            @csrf

            <!-- 1. Patient Selection / Registration -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="card-title fw-bold text-primary mb-0">
                        <i class="bi bi-person-bounding-box me-1"></i> 1. Thông Tin Bệnh Nhân
                    </h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="is_existing_patient" id="patientTypeNew" value="0" {{ old('is_existing_patient', $selectedPatient ? '1' : '0') == '0' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary" for="patientTypeNew">Bệnh nhân mới đến lần đầu</label>

                        <input type="radio" class="btn-check" name="is_existing_patient" id="patientTypeExisting" value="1" {{ old('is_existing_patient', $selectedPatient ? '1' : '0') == '1' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary" for="patientTypeExisting">Đã có hồ sơ tại viện</label>
                    </div>
                </div>
                <div class="card-body p-4">

                    <!-- Existing Patient Section -->
                    <div id="existingPatientSection" class="{{ old('is_existing_patient', $selectedPatient ? '1' : '0') == '1' ? '' : 'd-none' }}">
                        @if($selectedPatient)
                            <div class="alert alert-primary d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> Đang tiếp nhận bệnh nhân: {{ $selectedPatient->full_name }}</h6>
                                    <div class="small">
                                        Mã BN: <strong>{{ $selectedPatient->patient_code }}</strong> &bull; SĐT: <strong>{{ $selectedPatient->phone }}</strong> &bull; Ngày sinh: <strong>{{ $selectedPatient->dob ? $selectedPatient->dob->format('d/m/Y') : '--' }}</strong>
                                    </div>
                                </div>
                                <input type="hidden" name="patient_profile_id" value="{{ $selectedPatient->id }}">
                                <a href="{{ route('reception.appointments.walkin') }}" class="btn btn-outline-primary btn-sm bg-white">Chọn người khác</a>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Chọn bệnh nhân đã có hồ sơ trong hệ thống: <span class="text-danger">*</span></label>
                                <select name="patient_profile_id" id="patientProfileSelect" class="form-select @error('patient_profile_id') is-invalid @enderror">
                                    <option value="">-- Tìm kiếm theo Tên, SĐT, Mã BN --</option>
                                    @foreach(\App\Models\PatientProfile::latest('id')->limit(50)->get() as $p)
                                        <option value="{{ $p->id }}" {{ old('patient_profile_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->patient_code }} - {{ $p->full_name }} (SĐT: {{ $p->phone }} - {{ $p->dob ? $p->dob->format('d/m/Y') : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_profile_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Nếu không tìm thấy, vui lòng chuyển sang tab <strong>"Bệnh nhân mới đến lần đầu"</strong> để tạo hồ sơ ngay.</div>
                            </div>
                        @endif
                    </div>

                    <!-- New Patient Section -->
                    <div id="newPatientSection" class="{{ old('is_existing_patient', $selectedPatient ? '1' : '0') == '0' ? '' : 'd-none' }}">
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#qrCccdModal">
                                <i class="bi bi-qr-code-scan"></i> Quét mã QR CCCD / BHYT điền nhanh
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Họ và tên bệnh nhân: <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror" placeholder="VD: Trần Thị Mai">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Số điện thoại liên hệ: <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="VD: 0912345678">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Ngày sinh: <span class="text-danger">*</span></label>
                                <input type="date" name="dob" value="{{ old('dob') }}" max="{{ date('Y-m-d') }}" class="form-control @error('dob') is-invalid @enderror">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Giới tính: <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Số CCCD / CMND:</label>
                                <input type="text" name="identity_card" value="{{ old('identity_card') }}" class="form-control @error('identity_card') is-invalid @enderror" placeholder="12 chữ số">
                                @error('identity_card')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Mã thẻ BHYT (nếu có):</label>
                                <input type="text" name="insurance_code" value="{{ old('insurance_code') }}" class="form-control @error('insurance_code') is-invalid @enderror" placeholder="VD: DN401...">
                                @error('insurance_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email (nếu có):</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="benhnhan@gmail.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Địa chỉ nơi cư trú:</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror" placeholder="Số nhà, đường, phường, quận, tỉnh/thành phố">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Nhóm máu:</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">-- Chưa rõ --</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>Nhóm {{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Tiền sử Dị ứng (nếu có):</label>
                                <input type="text" name="allergies" value="{{ old('allergies') }}" class="form-control" placeholder="VD: Dị ứng Penicillin, Aspirin...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Appointment Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title fw-bold text-primary mb-0">
                        <i class="bi bi-hospital me-1"></i> 2. Thông Tin Khám Chữa Bệnh
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Specialty -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Chuyên khoa khám: <span class="text-danger">*</span></label>
                            <select name="specialty_id" id="specialtySelect" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                <option value="">-- Chọn chuyên khoa --</option>
                                @foreach($specialties as $sp)
                                    <option value="{{ $sp->id }}" {{ old('specialty_id') == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Doctor -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bác sĩ phụ trách: <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctorSelect" class="form-select @error('doctor_id') is-invalid @enderror" required>
                                <option value="">-- Chọn bác sĩ --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" data-specialty="{{ $doc->specialty_id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->full_name_with_title }} - {{ $doc->specialty ? $doc->specialty->name : '' }} ({{ $doc->room_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ngày khám: <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" value="{{ old('appointment_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control @error('appointment_date') is-invalid @enderror" required>
                            @error('appointment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shift -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Ca khám: <span class="text-danger">*</span></label>
                            @php
                                $currentHour = (int) date('H');
                                $defaultShift = $currentHour < 12 ? 'morning' : 'afternoon';
                            @endphp
                            <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                <option value="morning" {{ old('shift', $defaultShift) === 'morning' ? 'selected' : '' }}>Ca Sáng (07:30 - 11:30)</option>
                                <option value="afternoon" {{ old('shift', $defaultShift) === 'afternoon' ? 'selected' : '' }}>Ca Chiều (13:30 - 17:00)</option>
                            </select>
                            @error('shift')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Giờ tiếp nhận tại quầy:</label>
                            <input type="text" name="appointment_time" value="{{ old('appointment_time', date('H:i')) }}" class="form-control font-monospace" readonly>
                        </div>

                        <!-- Symptoms & Priority -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded border mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="prioritySwitch" name="is_priority" value="1">
                                    <label class="form-check-label fw-bold text-danger" for="prioritySwitch">
                                        <i class="bi bi-star-fill text-warning me-1"></i> Bệnh nhân diện ưu tiên đặc biệt (Khẩn cấp / Người cao tuổi / Trẻ em / Phụ nữ mang thai / Khuyết tật)
                                    </label>
                                </div>
                                <div class="small text-muted">Đánh dấu trường hợp cần hỗ trợ tiếp đón nhanh và phân luồng khám ưu tiên.</div>
                            </div>

                            <!-- Vital Signs Intake (Chỉ số sinh tồn ban đầu) -->
                            <div class="card border-info mb-3">
                                <div class="card-header bg-info-subtle py-2 d-flex align-items-center justify-content-between">
                                    <span class="fw-bold small text-info-emphasis">
                                        <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Đo chỉ số sinh tồn ban đầu tại quầy tiếp đón (Tùy chọn)
                                    </span>
                                    <span class="badge bg-light text-dark border small" id="bmiBadge">Chưa đo BMI</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted mb-1">Huyết áp (mmHg):</label>
                                            <input type="text" id="vital_bp" class="form-control form-control-sm" placeholder="VD: 120/80">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small text-muted mb-1">Mạch (lần/phút):</label>
                                            <input type="number" id="vital_pulse" class="form-control form-control-sm" placeholder="75">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small text-muted mb-1">Thân nhiệt (°C):</label>
                                            <input type="number" step="0.1" id="vital_temp" class="form-control form-control-sm" placeholder="36.8">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small text-muted mb-1">Cân nặng (kg):</label>
                                            <input type="number" step="0.5" id="vital_weight" class="form-control form-control-sm" placeholder="60">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted mb-1">Chiều cao (cm):</label>
                                            <input type="number" id="vital_height" class="form-control form-control-sm" placeholder="165">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <label class="form-label fw-semibold small">Mô tả lý do khám / Triệu chứng ban đầu: <span class="text-danger">*</span></label>
                            <textarea name="symptom_description" id="symptomTextarea" class="form-control @error('symptom_description') is-invalid @enderror" rows="3" placeholder="VD: Sốt cao, đau họng 2 ngày nay, ho có đờm..." required>{{ old('symptom_description', ($isFollowUp ?? false) ? '[TÁI KHÁM] Tái khám theo lịch hẹn của bác sĩ điều trị' : '') }}</textarea>
                            @error('symptom_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('reception.appointments.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
                <button type="submit" class="btn btn-success px-4 py-2 d-inline-flex align-items-center gap-2 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle fs-5"></i> Tiếp nhận & Check-in ngay
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Quét / Trích xuất QR CCCD & BHYT -->
<div class="modal fade" id="qrCccdModal" tabindex="-1" aria-labelledby="qrCccdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold fs-6" id="qrCccdModalLabel">
                    <i class="bi bi-qr-code-scan me-1"></i> Quét & Bóc Tách Dữ Liệu QR CCCD / BHYT Bằng Camera
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopWalkinCamera()"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Camera Scanner Container -->
                <div class="p-3 bg-light rounded-3 border text-center mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark">
                            <i class="bi bi-camera-fill text-primary me-1"></i> Quét Bằng Webcam / Camera Trình Duyệt:
                        </span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="btnToggleWalkinCam" onclick="toggleWalkinCamera()">
                                <i class="bi bi-camera-video me-1"></i> Bật Camera Quét
                            </button>
                        </div>
                    </div>
                    <div id="walkinCameraReader" class="d-none mx-auto rounded overflow-hidden" style="max-width: 420px; background: #000;"></div>
                    <div id="walkinCameraFeedback" class="small text-muted mt-2">
                        Nhấn nút "Bật Camera Quét" để đưa thẻ Căn cước công dân hoặc thẻ BHYT trước camera.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Hoặc Dán / Bắn Mã QR Từ Máy Quét Cầm Tay:</label>
                    <textarea id="rawQrInput" class="form-control font-monospace small" rows="2" placeholder="Dán hoặc bắn mã QR vào đây..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillSampleCccd()">
                        <i class="bi bi-magic me-1"></i> Dùng thử CCCD mẫu
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="fillSampleBhyt()">
                        <i class="bi bi-shield-check me-1"></i> Dùng thử BHYT mẫu
                    </button>
                </div>

                <div id="qrAlertBox" class="alert alert-danger py-2 px-3 small d-none mt-3"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="stopWalkinCamera()">Đóng</button>
                <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="parseAndFillQrData()">
                    <i class="bi bi-arrow-down-square-fill me-1"></i> Trích xuất & Điền vào form
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let walkinScanner = null;

    function toggleWalkinCamera() {
        if (walkinScanner) {
            stopWalkinCamera();
        } else {
            startWalkinCamera();
        }
    }

    function startWalkinCamera() {
        const readerEl = document.getElementById('walkinCameraReader');
        const feedbackEl = document.getElementById('walkinCameraFeedback');
        const btn = document.getElementById('btnToggleWalkinCam');

        readerEl.classList.remove('d-none');
        feedbackEl.textContent = 'Đang kích hoạt camera...';
        btn.innerHTML = '<i class="bi bi-camera-video-off me-1"></i> Tắt Camera';
        btn.className = 'btn btn-sm btn-outline-danger';

        walkinScanner = new Html5Qrcode("walkinCameraReader");
        walkinScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            (decodedText) => {
                feedbackEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check2-circle"></i> Đã quét thành công!</span>`;
                document.getElementById('rawQrInput').value = decodedText;
                stopWalkinCamera();
                parseAndFillQrData(decodedText);
            },
            () => {}
        ).catch(err => {
            feedbackEl.textContent = 'Không thể mở camera: ' + err;
            stopWalkinCamera();
        });
    }

    function stopWalkinCamera() {
        if (walkinScanner) {
            walkinScanner.stop().then(() => {
                walkinScanner.clear();
                walkinScanner = null;
            }).catch(() => {
                walkinScanner = null;
            });
        }
        const readerEl = document.getElementById('walkinCameraReader');
        const btn = document.getElementById('btnToggleWalkinCam');
        if (readerEl) readerEl.classList.add('d-none');
        if (btn) {
            btn.innerHTML = '<i class="bi bi-camera-video me-1"></i> Bật Camera Quét';
            btn.className = 'btn btn-sm btn-primary';
        }
    }

    function fillSampleCccd() {
        document.getElementById('rawQrInput').value = '001092018888||Nguyễn Văn Hưng|15081992|Nam|108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội|10102021';
    }

    function fillSampleBhyt() {
        document.getElementById('rawQrInput').value = 'DN4010123456789|Nguyễn Bệnh Nhân BHYT|20/05/1990|1|108 Hoàng Như Tiếp, Long Biên, Hà Nội|01001|01/01/2026-31/12/2026';
    }

    function parseAndFillQrData(directText = null) {
        const raw = (directText || document.getElementById('rawQrInput').value).trim();
        const alertBox = document.getElementById('qrAlertBox');
        if (alertBox) alertBox.classList.add('d-none');

        if (!raw) {
            if (alertBox) {
                alertBox.textContent = 'Vui lòng nhập hoặc quét chuỗi QR trước khi trích xuất.';
                alertBox.classList.remove('d-none');
            }
            return;
        }

        const parts = raw.split('|');

        // Case 1: Thẻ CCCD gắn chip (7 trường: Số_CCCD | Số_CMND_cũ | Họ_Tên | NgàySinh_ddMMyyyy | GiớiTính | ĐịaChỉ | NgàyCấp)
        if (parts.length >= 6 && /^\d{12}$/.test(parts[0])) {
            const cccd = parts[0];
            const fullName = parts[2];
            const rawDob = parts[3]; // ddmmyyyy
            const genderStr = parts[4]; // Nam / Nữ
            const address = parts[5];

            if (fullName) document.querySelector('input[name="full_name"]').value = fullName;
            if (cccd) document.querySelector('input[name="identity_card"]').value = cccd;
            if (address) document.querySelector('textarea[name="address"]').value = address;

            if (rawDob && rawDob.length === 8) {
                const day = rawDob.substring(0, 2);
                const month = rawDob.substring(2, 4);
                const year = rawDob.substring(4, 8);
                document.querySelector('input[name="dob"]').value = `${year}-${month}-${day}`;
            }

            const genderSelect = document.querySelector('select[name="gender"]');
            if (genderSelect) {
                if (genderStr.toLowerCase().includes('nam')) genderSelect.value = 'male';
                else if (genderStr.toLowerCase().includes('nữ')) genderSelect.value = 'female';
            }

            stopWalkinCamera();
            const modalEl = document.getElementById('qrCccdModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            alert('Đã trích xuất thành công thông tin công dân từ thẻ CCCD gắn chip!');
            return;
        }

        // Case 2: Thẻ BHYT (Mã_thẻ | Họ_Tên | dd/mm/yyyy | Giới_tính (1=Nam, 2=Nữ) | Địa_chỉ | ...)
        if (parts.length >= 5 && /^[A-Z]{2}\d/.test(parts[0])) {
            const insuranceCode = parts[0];
            const fullName = parts[1];
            const rawDob = parts[2]; // dd/mm/yyyy
            const genderCode = parts[3]; // 1: Nam, 2: Nữ
            const address = parts[4];

            if (insuranceCode) document.querySelector('input[name="insurance_code"]').value = insuranceCode;
            if (fullName) document.querySelector('input[name="full_name"]').value = fullName;
            if (address) document.querySelector('textarea[name="address"]').value = address;

            if (rawDob && rawDob.includes('/')) {
                const [d, m, y] = rawDob.split('/');
                document.querySelector('input[name="dob"]').value = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
            }

            const genderSelect = document.querySelector('select[name="gender"]');
            if (genderSelect) {
                genderSelect.value = (genderCode === '1') ? 'male' : 'female';
            }

            stopWalkinCamera();
            const modalEl = document.getElementById('qrCccdModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            alert('Đã trích xuất thành công thông tin thẻ Bảo hiểm Y tế (BHYT)!');
            return;
        }

        if (alertBox) {
            alertBox.textContent = 'Định dạng mã QR không hợp lệ hoặc chưa được hỗ trợ. Vui lòng kiểm tra lại dữ liệu.';
            alertBox.classList.remove('d-none');
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const typeNew = document.getElementById('patientTypeNew');
        const typeExisting = document.getElementById('patientTypeExisting');
        const newSection = document.getElementById('newPatientSection');
        const existingSection = document.getElementById('existingPatientSection');

        function togglePatientType() {
            if (typeExisting && typeExisting.checked) {
                existingSection.classList.remove('d-none');
                newSection.classList.add('d-none');
            } else if (typeNew) {
                newSection.classList.remove('d-none');
                existingSection.classList.add('d-none');
            }
        }

        if (typeNew && typeExisting) {
            typeNew.addEventListener('change', togglePatientType);
            typeExisting.addEventListener('change', togglePatientType);
        }

        // Filter doctors by selected specialty
        const specialtySelect = document.getElementById('specialtySelect');
        const doctorSelect = document.getElementById('doctorSelect');

        if (specialtySelect && doctorSelect) {
            specialtySelect.addEventListener('change', function () {
                const selectedSpecialty = this.value;
                const options = doctorSelect.querySelectorAll('option');

                options.forEach(opt => {
                    if (!opt.value) return; // Keep the placeholder
                    const docSpecialty = opt.getAttribute('data-specialty');
                    if (!selectedSpecialty || docSpecialty === selectedSpecialty) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });

                // Reset doctor selection if current selected does not match
                const currentDoc = doctorSelect.selectedOptions[0];
                if (currentDoc && currentDoc.value && currentDoc.getAttribute('data-specialty') !== selectedSpecialty) {
                    doctorSelect.value = '';
                }
            });
        }

        // --- Live BMI Calculation & Asian Standard Classification ---
        const weightInput = document.getElementById('vital_weight');
        const heightInput = document.getElementById('vital_height');
        const bmiBadge = document.getElementById('bmiBadge');

        function calculateBMI() {
            const weight = parseFloat(weightInput?.value);
            const heightCm = parseFloat(heightInput?.value);

            if (weight > 0 && heightCm > 0) {
                const heightM = heightCm / 100;
                const bmi = (weight / (heightM * heightM)).toFixed(1);
                let classification = '';
                let badgeClass = 'bg-info';

                if (bmi < 18.5) {
                    classification = 'Thiếu cân / Gầy';
                    badgeClass = 'bg-info text-dark';
                } else if (bmi < 23) {
                    classification = 'Chuẩn bình thường';
                    badgeClass = 'bg-success text-white';
                } else if (bmi < 25) {
                    classification = 'Thừa cân (Tiền béo phì)';
                    badgeClass = 'bg-warning text-dark';
                } else {
                    classification = 'Béo phì';
                    badgeClass = 'bg-danger text-white';
                }

                bmiBadge.className = `badge ${badgeClass} border small fw-bold px-2 py-1`;
                bmiBadge.innerHTML = `<i class="bi bi-activity me-1"></i> BMI: ${bmi} (${classification})`;
                return { bmi, classification };
            } else {
                bmiBadge.className = 'badge bg-light text-dark border small';
                bmiBadge.textContent = 'Chưa đo BMI';
                return null;
            }
        }

        if (weightInput && heightInput) {
            weightInput.addEventListener('input', calculateBMI);
            heightInput.addEventListener('input', calculateBMI);
        }

        // --- Package Vital Signs into Symptom on Submit ---
        const form = document.getElementById('walkinForm');
        const symptomTextarea = document.getElementById('symptomTextarea');
        const prioritySwitch = document.getElementById('prioritySwitch');

        if (form && symptomTextarea) {
            form.addEventListener('submit', function () {
                const bp = document.getElementById('vital_bp')?.value.trim();
                const pulse = document.getElementById('vital_pulse')?.value.trim();
                const temp = document.getElementById('vital_temp')?.value.trim();
                const weight = weightInput?.value.trim();
                const height = heightInput?.value.trim();
                const bmiData = calculateBMI();

                const vitalsList = [];
                if (bp) vitalsList.push(`HA: ${bp} mmHg`);
                if (pulse) vitalsList.push(`Mạch: ${pulse} l/p`);
                if (temp) vitalsList.push(`Nhiệt độ: ${temp}°C`);
                if (weight && height && bmiData) {
                    vitalsList.push(`Thể trạng: ${weight}kg / ${height}cm (BMI ${bmiData.bmi} - ${bmiData.classification})`);
                } else if (weight) {
                    vitalsList.push(`Cân nặng: ${weight}kg`);
                }

                let currentSymptom = symptomTextarea.value.trim();

                // Append vital signs header if any vitals recorded
                if (vitalsList.length > 0 && !currentSymptom.includes('[Sinh hiệu:')) {
                    const vitalsString = `[Sinh hiệu: ${vitalsList.join(', ')}]`;
                    currentSymptom = `${vitalsString} ${currentSymptom}`;
                }

                // Append Priority Tag if switched
                if (prioritySwitch && prioritySwitch.checked && !currentSymptom.includes('[ƯU TIÊN]')) {
                    currentSymptom = `[ƯU TIÊN] ${currentSymptom}`;
                }

                symptomTextarea.value = currentSymptom;
            });
        }
    });
</script>
@endpush
@endsection
