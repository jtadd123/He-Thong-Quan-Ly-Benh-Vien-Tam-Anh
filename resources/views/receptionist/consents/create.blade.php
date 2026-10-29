@extends('layouts.admin')

@section('title', 'Tạo Bản Cam Đoan Phẫu Thuật / Thủ Thuật - ' . $patient->full_name)

@section('styles')
<style>
    .signature-wrapper {
        border: 2px dashed #0284c7;
        border-radius: 12px;
        background-color: #f8fafc;
        position: relative;
        touch-action: none;
    }
    #signatureCanvas {
        width: 100%;
        height: 200px;
        cursor: crosshair;
        background-color: #ffffff;
        border-radius: 10px;
    }
    .quick-template-btn {
        font-size: 0.78rem;
        border-radius: 20px;
    }
</style>
@endsection

@section('content')
<x-page-header title="Ký Điện Tử Bản Cam Đoan Chấp Nhận Phẫu Thuật / Thủ Thuật" subtitle="Biểu mẫu pháp lý tuân thủ Thông tư 46/2018/TT-BYT và Luật Khám bệnh, chữa bệnh Việt Nam.">
    <x-slot:actions>
        <a href="{{ route('reception.patients.show', $patient) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Hồ sơ bệnh nhân
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-pen-fill me-2"></i> Thông Tin Cam Kết & Khung Ký Số Trực Tuyến
                </h6>
                <span class="badge bg-white text-primary font-monospace">{{ $patient->patient_code }}</span>
            </div>

            <div class="card-body p-4">
                <!-- Patient Demographics Summary -->
                <div class="alert alert-light border p-3 rounded-3 d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                        <div>
                            <div class="fs-5 fw-bold text-dark text-uppercase">{{ $patient->full_name }}</div>
                            <div class="small text-muted">
                                <span>Giới tính: <strong>{{ $patient->gender_name }}</strong></span> &bull;
                                <span>Năm sinh: <strong>{{ $patient->dob ? $patient->dob->format('d/m/Y') : '--' }}</strong> ({{ $patient->age_text }})</span> &bull;
                                <span>SĐT: <strong>{{ $patient->phone }}</strong></span> &bull;
                                <span>CCCD: <strong>{{ $patient->identity_card ?: 'Chưa cập nhật' }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-primary px-3 py-2 fs-6">BV Đa Khoa Tâm Anh</span>
                    </div>
                </div>

                <!-- Fast Procedure Templates -->
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="small fw-bold text-dark mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Chọn nhanh thủ thuật mẫu thường gặp:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm quick-template-btn" onclick="applyTemplate('Nội soi thực quản - dạ dày - tá tràng gây mê', 'Khoa Nội Soi Tiêu Hóa', 'Chảy máu tiêu hóa, sốc phản vệ với thuốc mê, tụt huyết áp thoáng qua, trầy xước niêm mạc')">
                            Nội soi dạ dày gây mê
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm quick-template-btn" onclick="applyTemplate('Tiểu phẫu cắt bỏ u bã đậu / u mỡ dưới da', 'Khoa Ngoại Tổng Quát', 'Chảy máu tại vết mổ, tụ dịch, nhiễm trùng vết mổ, dị ứng thuốc tê Lidocain')">
                            Tiểu phẫu u bã đậu
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm quick-template-btn" onclick="applyTemplate('Tiêm nội khớp khớp gối / khớp vai dưới hướng dẫn siêu âm', 'Khoa Cơ Xương Khớp', 'Đau tăng nhẹ sau tiêm, nóng đỏ khớp, nhiễm trùng khớp vô khuẩn, dị ứng thuốc')">
                            Tiêm nội khớp
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm quick-template-btn" onclick="applyTemplate('Khâu xử trí vết thương phần mềm vùng đầu / mặt', 'Khoa Cấp Cứu', 'Đau, sưng nề vết thương, nhiễm trùng phần mềm, để lại sẹo co kéo')">
                            Khâu vết thương
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm quick-template-btn" onclick="applyTemplate('Chọc hút tế bào kim nhỏ tuyến giáp / tuyến vú (FNA)', 'Khoa Chẩn Đoán Hình Ảnh', 'Tụ máu dưới da nhẹ tại vị trí chọc, đau tức nhẹ, choáng ngất do lo âu')">
                            Chọc hút kim nhỏ (FNA)
                        </button>
                    </div>
                </div>

                <form action="{{ route('reception.consents.store') }}" method="POST" id="consentForm" onsubmit="return validateAndSubmitConsent(event)">
                    @csrf
                    <input type="hidden" name="patient_profile_id" value="{{ $patient->id }}">
                    <input type="hidden" name="appointment_id" value="{{ $recentAppointment?->id }}">
                    <input type="hidden" name="signature_data" id="signatureDataInput">

                    <div class="row g-3">
                        <!-- Tên thủ thuật -->
                        <div class="col-md-7">
                            <label class="form-label fw-bold small">Tên Thủ Thuật / Phẫu Thuật Y Khoa <span class="text-danger">*</span></label>
                            <input type="text" name="procedure_name" id="procedureNameInput" class="form-control" placeholder="Ví dụ: Nội soi dạ dày - đại tràng tiền mê" required>
                        </div>

                        <!-- Khoa phòng -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">Khoa Phòng Thực Hiện <span class="text-danger">*</span></label>
                            <input type="text" name="department_name" id="departmentNameInput" class="form-control" value="Khoa Ngoại Tổng Quát" required>
                        </div>

                        <!-- Bác sĩ phụ trách -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Bác Sĩ Trực Tiếp Thực Hiện / Giải Thích</label>
                            <select name="doctor_id" class="form-select">
                                <option value="">-- Chọn bác sĩ phụ trách --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ $recentAppointment?->doctor_id == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->full_name_with_title }} - {{ $doc->specialty?->name }} (Phòng {{ $doc->room_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Mối quan hệ người ký -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Người Đại Diện Ký Cam Đoan <span class="text-danger">*</span></label>
                            <select name="signer_relationship" id="signerRelSelect" class="form-select" onchange="updateSignerFields()">
                                <option value="patient">Chính người bệnh ký cam kết</option>
                                <option value="family">Người nhà / Thân nhân (bố, mẹ, vợ, chồng, con)</option>
                                <option value="guardian">Người giám hộ hợp pháp</option>
                            </select>
                        </div>

                        <!-- Thông tin người ký -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Họ Và Tên Người Ký <span class="text-danger">*</span></label>
                            <input type="text" name="signer_name" id="signerNameInput" class="form-control text-uppercase" value="{{ $patient->full_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Số Điện Thoại Người Ký</label>
                            <input type="text" name="signer_phone" id="signerPhoneInput" class="form-control font-monospace" value="{{ $patient->phone }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Số CCCD Người Ký</label>
                            <input type="text" name="signer_id_card" id="signerIdCardInput" class="form-control font-monospace" value="{{ $patient->identity_card }}">
                        </div>

                        <!-- Nội dung rủi ro đã giải thích -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Các Tai Biến & Rủi Ro Tiềm Ẩn Đã Được Bác Sĩ Giải Thích Rõ Ràng:</label>
                            <textarea name="risks_explained" id="risksExplainedInput" class="form-control small" rows="2">Đã được bác sĩ giải thích rõ ràng về mục đích, phương pháp kỹ thuật, nguy cơ tai biến có thể xảy ra (chảy máu, dị ứng thuốc gây mê/tê, tụt huyết áp, nhiễm trùng) và các biện pháp y khoa dự phòng sẵn sàng xử trí.</textarea>
                        </div>

                        <!-- Lời cam đoan của người bệnh -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Nội Dung Cam Đoan Của Người Bệnh / Người Nhà:</label>
                            <textarea name="patient_undertaking" class="form-control small" rows="2">Sau khi nghe giải thích đầy đủ, tôi hoàn toàn tự nguyện đồng ý thực hiện thủ thuật/phẫu thuật trên. Tôi cam kết tuân thủ nghiêm túc các y lệnh và hướng dẫn của bác sĩ điều trị.</textarea>
                        </div>

                        <!-- Khung Ký Điện Tử Canvas -->
                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small text-primary mb-0">
                                    <i class="bi bi-vector-pen me-1"></i> Khung Chữ Ký Điện Tử (Dùng ngón tay chạm ký hoặc chuột máy tính) <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="clearSignature()">
                                    <i class="bi bi-eraser"></i> Xóa ký lại
                                </button>
                            </div>

                            <div class="signature-wrapper p-2">
                                <canvas id="signatureCanvas"></canvas>
                                <div class="text-center text-muted small mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle me-1"></i> Quý khách vui lòng ký tên rõ ràng trong khung viền trên. Chữ ký có giá trị pháp lý tương đương văn bản giấy.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('reception.patients.show', $patient) }}" class="btn btn-outline-secondary">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitConsent">
                            <i class="bi bi-check2-circle me-1"></i> Hoàn Tất & Ký Số Điện Tử
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');
let isDrawing = false;
let hasSigned = false;

// Resize canvas properly for high-DPI screens
function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = 200;
    ctx.strokeStyle = '#005baa';
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}
window.addEventListener('resize', resizeCanvas);
setTimeout(resizeCanvas, 100);

// Mouse & Touch events
function getCoordinates(e) {
    const rect = canvas.getBoundingClientRect();
    if (e.touches && e.touches.length > 0) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    }
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function startDrawing(e) {
    isDrawing = true;
    hasSigned = true;
    const { x, y } = getCoordinates(e);
    ctx.beginPath();
    ctx.moveTo(x, y);
    e.preventDefault();
}

function draw(e) {
    if (!isDrawing) return;
    const { x, y } = getCoordinates(e);
    ctx.lineTo(x, y);
    ctx.stroke();
    e.preventDefault();
}

function stopDrawing(e) {
    if (isDrawing) {
        ctx.closePath();
        isDrawing = false;
    }
    e.preventDefault();
}

canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseleave', stopDrawing);

canvas.addEventListener('touchstart', startDrawing, { passive: false });
canvas.addEventListener('touchmove', draw, { passive: false });
canvas.addEventListener('touchend', stopDrawing, { passive: false });

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasSigned = false;
    document.getElementById('signatureDataInput').value = '';
}

function applyTemplate(procedure, department, risks) {
    document.getElementById('procedureNameInput').value = procedure;
    document.getElementById('departmentNameInput').value = department;
    document.getElementById('risksExplainedInput').value = `Đã được bác sĩ giải thích rõ ràng về mục đích, phương pháp thực hiện thủ thuật [${procedure}], các tai biến nguy cơ tiềm ẩn: ${risks} và các phương án y khoa dự phòng sẵn sàng xử trí.`;
}

function updateSignerFields() {
    const rel = document.getElementById('signerRelSelect').value;
    const nameInput = document.getElementById('signerNameInput');
    const phoneInput = document.getElementById('signerPhoneInput');
    const idCardInput = document.getElementById('signerIdCardInput');

    if (rel === 'patient') {
        nameInput.value = "{{ $patient->full_name }}";
        phoneInput.value = "{{ $patient->phone }}";
        idCardInput.value = "{{ $patient->identity_card }}";
    } else {
        nameInput.value = "";
        phoneInput.value = "";
        idCardInput.value = "";
        nameInput.placeholder = "Nhập họ tên người nhà...";
    }
}

function validateAndSubmitConsent(e) {
    if (!hasSigned) {
        e.preventDefault();
        alert('Vui lòng ký tên vào khung chữ ký điện tử trước khi hoàn tất bản cam đoan!');
        return false;
    }

    const dataUrl = canvas.toDataURL('image/png');
    document.getElementById('signatureDataInput').value = dataUrl;
    return true;
}
</script>
@endsection
