@extends('kiosk.layout')

@section('title', 'Kiosk Tự Phục Vụ Tiếp Nhận - BV Đa Khoa Tâm Anh')

@section('content')
<div class="container-fluid" style="max-width: 1200px;">

    <!-- Home Welcome & Option Cards View -->
    <div id="viewHome">
        <div class="text-center mb-4">
            <h1 class="fw-bold text-white display-5 mb-2">Kính Chào Quý Khách Đến Khám Bệnh</h1>
            <p class="text-white-50 fs-5 mb-0">Vui lòng chạm chọn dịch vụ quý khách mong muốn thực hiện</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Option 1: Có Hẹn Trước -->
            <div class="col-md-4">
                <div class="kiosk-card-btn" onclick="openStep('stepAppointment')">
                    <div class="kiosk-icon-circle bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <h3 class="fw-bold text-primary mb-2">ĐÃ CÓ HẸN TRƯỚC</h3>
                    <p class="text-muted mb-3 fs-6">Đã đặt lịch qua Website, Hotline hoặc Bác sĩ hẹn tái khám</p>
                    <span class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-sm">
                        Chạm Để Xác Nhận <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                </div>
            </div>

            <!-- Option 2: Tái Khám / Có Hồ Sơ Cũ -->
            <div class="col-md-4">
                <div class="kiosk-card-btn" onclick="openStep('stepReturning')">
                    <div class="kiosk-icon-circle bg-success-subtle text-success">
                        <i class="bi bi-person-bounding-box"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-2">TÁI KHÁM / HỒ SƠ CŨ</h3>
                    <p class="text-muted mb-3 fs-6">Đã từng khám tại Tâm Anh, có số điện thoại hoặc mã bệnh nhân</p>
                    <span class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm">
                        Lấy Số Khám Ngay <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                </div>
            </div>

            <!-- Option 3: Bệnh Nhân Mới -->
            <div class="col-md-4">
                <div class="kiosk-card-btn" onclick="openStep('stepNewPatient')">
                    <div class="kiosk-icon-circle bg-info-subtle text-info">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold text-info mb-2">BỆNH NHÂN MỚI</h3>
                    <p class="text-muted mb-3 fs-6">Lần đầu tiên đến khám tại Bệnh viện Đa khoa Tâm Anh</p>
                    <span class="btn btn-info text-white btn-lg rounded-pill px-4 fw-bold shadow-sm">
                        Đăng Ký Khám Mới <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <span class="badge bg-dark bg-opacity-75 text-white-50 px-3 py-2 fs-6 border border-secondary">
                <i class="bi bi-activity text-success me-1"></i> Đã tiếp nhận <strong>{{ $totalServedToday }}</strong> lượt bệnh nhân hôm nay
            </span>
        </div>
    </div>

    <!-- VIEW 1: ĐÃ CÓ LỊCH HẸN TRƯỚC -->
    <div id="stepAppointment" class="d-none">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-primary mb-1">
                        <i class="bi bi-calendar-check me-2"></i> Xác Nhận Lịch Hẹn Đã Đặt
                    </h3>
                    <div class="text-muted">Nhập Mã lịch hẹn (AP-...) hoặc Số điện thoại đặt khám</div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-4" onclick="backHome()">
                    <i class="bi bi-arrow-left me-1"></i> Quay Lại
                </button>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <label class="form-label fw-bold fs-5">Mã Lịch Hẹn hoặc Số Điện Thoại:</label>
                    <div class="input-group mb-3">
                        <input type="text" id="appSearchInput" class="form-control touch-input text-center text-primary" placeholder="Ví dụ: 0912345678 hoặc AP-..." autofocus>
                        <button class="btn btn-danger px-4" type="button" onclick="clearAppInput()">
                            <i class="bi bi-x-lg fs-4"></i>
                        </button>
                    </div>

                    <div class="d-flex gap-2 mb-4">
                        <button class="btn btn-outline-primary btn-lg flex-grow-1 py-3 fw-bold rounded-3" onclick="startScanner('app')">
                            <i class="bi bi-camera-fill me-2 fs-4"></i> Quét Mã QR Phiếu Hẹn
                        </button>
                        <button class="btn btn-primary btn-lg flex-grow-1 py-3 fw-bold rounded-3" onclick="lookupAppointment()">
                            <i class="bi bi-search me-2 fs-4"></i> Tìm Kiếm & Tiếp Nhận
                        </button>
                    </div>

                    <!-- Virtual Numpad -->
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="small fw-bold text-muted mb-2 text-center text-uppercase">Bàn phím số cảm ứng</div>
                        <div class="row g-2">
                            @foreach([1,2,3,4,5,6,7,8,9] as $n)
                                <div class="col-4">
                                    <button type="button" class="btn numpad-btn w-100" onclick="appendAppKey('{{ $n }}')">{{ $n }}</button>
                                </div>
                            @endforeach
                            <div class="col-4">
                                <button type="button" class="btn numpad-btn w-100" onclick="appendAppKey('AP-')">AP-</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn numpad-btn w-100" onclick="appendAppKey('0')">0</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn numpad-btn w-100 text-danger" onclick="backspaceAppKey()">
                                    <i class="bi bi-backspace"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="h-100 p-4 bg-light rounded-4 border d-flex flex-column justify-content-center text-center" id="appResultBox">
                        <i class="bi bi-qr-code-scan display-1 text-primary opacity-50 mb-3"></i>
                        <h5 class="fw-bold text-dark">Sẵn Sàng Tra Cứu</h5>
                        <p class="text-muted small">Quý khách nhập thông tin bên trái hoặc giơ mã QR trên tin nhắn / phiếu hẹn vào camera quét.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 2: TÁI KHÁM / ĐÃ CÓ HỒ SƠ CŨ -->
    <div id="stepReturning" class="d-none">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-success mb-1">
                        <i class="bi bi-person-check me-2"></i> Bệnh Nhân Tái Khám / Đã Có Hồ Sơ
                    </h3>
                    <div class="text-muted">Nhập Số điện thoại, Số CCCD hoặc Mã bệnh nhân Tâm Anh</div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-4" onclick="backHome()">
                    <i class="bi bi-arrow-left me-1"></i> Quay Lại
                </button>
            </div>

            <!-- Lookup Stage -->
            <div id="returningLookupStage">
                <div class="row g-4">
                    <div class="col-md-7">
                        <label class="form-label fw-bold fs-5">Số Điện Thoại / CCCD / Mã Bệnh Nhân:</label>
                        <div class="input-group mb-3">
                            <input type="text" id="returningSearchInput" class="form-control touch-input text-center text-success" placeholder="Nhập SĐT hoặc quét thẻ..." autofocus>
                            <button class="btn btn-danger px-4" type="button" onclick="document.getElementById('returningSearchInput').value=''">
                                <i class="bi bi-x-lg fs-4"></i>
                            </button>
                        </div>

                        <div class="d-flex gap-2 mb-4">
                            <button class="btn btn-outline-success btn-lg flex-grow-1 py-3 fw-bold rounded-3" onclick="startScanner('returning')">
                                <i class="bi bi-qr-code-scan me-2 fs-4"></i> Quét Thẻ BN / CCCD
                            </button>
                            <button class="btn btn-success btn-lg flex-grow-1 py-3 fw-bold rounded-3" onclick="lookupReturningPatient()">
                                <i class="bi bi-search me-2 fs-4"></i> Tra Cứu Hồ Sơ
                            </button>
                        </div>

                        <!-- Virtual Numpad -->
                        <div class="p-3 bg-light rounded-4 border">
                            <div class="small fw-bold text-muted mb-2 text-center text-uppercase">Bàn phím số cảm ứng</div>
                            <div class="row g-2">
                                @foreach([1,2,3,4,5,6,7,8,9] as $n)
                                    <div class="col-4">
                                        <button type="button" class="btn numpad-btn w-100" onclick="appendRetKey('{{ $n }}')">{{ $n }}</button>
                                    </div>
                                @endforeach
                                <div class="col-4">
                                    <button type="button" class="btn numpad-btn w-100" onclick="appendRetKey('BN-')">BN-</button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn numpad-btn w-100" onclick="appendRetKey('0')">0</button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn numpad-btn w-100 text-danger" onclick="backspaceRetKey()">
                                        <i class="bi bi-backspace"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="h-100 p-4 bg-light rounded-4 border d-flex flex-column justify-content-center text-center">
                            <i class="bi bi-person-vcard display-1 text-success opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">Tra Cứu Hồ Sơ Bệnh Án Cũ</h5>
                            <p class="text-muted small">Sau khi nhận diện hồ sơ, quý khách sẽ được chọn chuyên khoa mong muốn khám và in phiếu số thứ tự ngay.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specialty Select Stage (Show when found patient) -->
            <div id="returningSpecialtyStage" class="d-none">
                <div class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-4 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <div>
                            <div class="fs-5 fw-bold text-dark" id="retPatientName">--</div>
                            <div class="small text-muted" id="retPatientMeta">--</div>
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="resetReturningLookup()">
                        Đổi người khác
                    </button>
                </div>

                <h4 class="fw-bold text-dark mb-3 text-center">Vui Lòng Chọn Chuyên Khoa Quý Khách Muốn Khám:</h4>
                <div class="row g-3">
                    @foreach($specialties as $sp)
                        <div class="col-6 col-md-4 col-lg-3">
                            <button type="button" class="btn btn-outline-primary w-100 p-3 rounded-4 text-start shadow-sm h-100 d-flex flex-column justify-content-between" onclick="confirmReturningSpecialty({{ $sp->id }}, '{{ addslashes($sp->name) }}')">
                                <div class="fw-bold fs-6 text-dark mb-1">{{ $sp->name }}</div>
                                <div class="small text-muted"><i class="bi bi-geo-alt"></i> Khu khám chuyên khoa</div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 3: BỆNH NHÂN MỚI LẦN ĐẦU -->
    <div id="stepNewPatient" class="d-none">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-info mb-1">
                        <i class="bi bi-person-plus me-2"></i> Đăng Ký Khám Mới Lần Đầu
                    </h3>
                    <div class="text-muted">Nhập thông tin cơ bản hoặc quét mã QR CCCD gắn chip để tự động điền</div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-4" onclick="backHome()">
                    <i class="bi bi-arrow-left me-1"></i> Quay Lại
                </button>
            </div>

            <!-- Fast CCCD Scanner Banner -->
            <div class="p-3 mb-4 bg-primary-subtle border border-primary-subtle rounded-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-qr-code fs-1 text-primary"></i>
                    <div>
                        <div class="fw-bold text-primary">Tiết kiệm thời gian bằng Quét CCCD Gắn Chip</div>
                        <div class="small text-muted">Hệ thống tự động điền Họ tên, Ngày sinh, Giới tính, Số CCCD chỉ trong 0.2 giây</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm" onclick="startScanner('new_cccd')">
                    <i class="bi bi-camera-fill me-1"></i> Quét Mã QR CCCD Ngay
                </button>
            </div>

            <form id="newPatientForm" onsubmit="submitNewPatient(event)">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ Và Tên Bệnh Nhân <span class="text-danger">*</span></label>
                        <input type="text" id="newFullName" class="form-control touch-input text-uppercase" placeholder="NGUYỄN VĂN AN" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số Điện Thoại Liên Hệ <span class="text-danger">*</span></label>
                        <input type="tel" id="newPhone" class="form-control touch-input font-monospace" placeholder="0912345678" pattern="[0-9]{10,11}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Năm Sinh <span class="text-danger">*</span></label>
                        <input type="number" id="newBirthYear" class="form-control touch-input font-monospace" placeholder="1990" min="1920" max="{{ date('Y') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Giới Tính <span class="text-danger">*</span></label>
                        <select id="newGender" class="form-select touch-input" required>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Số CCCD / Định Danh (Nếu có)</label>
                        <input type="text" id="newIdentityCard" class="form-control touch-input font-monospace" placeholder="12 chữ số CCCD">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã Thẻ BHYT (Nếu có)</label>
                        <input type="text" id="newInsuranceCode" class="form-control touch-input font-monospace text-uppercase" placeholder="15 ký tự BHYT">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-primary">Chuyên Khoa Đăng Ký Khám <span class="text-danger">*</span></label>
                        <select id="newSpecialtyId" class="form-select touch-input" required>
                            <option value="">-- Chạm để chọn chuyên khoa --</option>
                            @foreach($specialties as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-4" onclick="backHome()">
                        Hủy Bỏ
                    </button>
                    <button type="submit" class="btn btn-info text-white btn-lg rounded-pill px-5 fw-bold shadow-sm" id="btnSubmitNew">
                        <i class="bi bi-printer-fill me-2"></i> Đăng Ký & Lấy Số Thứ Tự
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Camera Scanner Modal -->
<div class="modal fade" id="cameraScannerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="scannerModalTitle">
                    <i class="bi bi-camera me-2 text-primary"></i> Quét Mã Bằng Camera
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="stopScanner()"></button>
            </div>
            <div class="modal-body p-4 text-center bg-dark text-white">
                <p class="text-white-50 mb-3" id="scannerInstruction">
                    Vui lòng hướng mã QR / Barcode trên thẻ hoặc điện thoại vào khung camera bên dưới
                </p>
                <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto; border-radius: 12px; overflow: hidden; background: #000;"></div>
                <div id="scannerFeedback" class="mt-3 small font-monospace text-warning"></div>
            </div>
            <div class="modal-footer bg-dark border-0">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" onclick="stopScanner()">Đóng Camera</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentStep = 'viewHome';
let activeScanner = null;
let currentScanContext = null;
let currentPatientId = null;

function openStep(stepId) {
    document.querySelectorAll('#viewHome, #stepAppointment, #stepReturning, #stepNewPatient').forEach(el => el.classList.add('d-none'));
    const target = document.getElementById(stepId);
    if (target) {
        target.classList.remove('d-none');
        currentStep = stepId;
    }
}

function backHome() {
    stopScanner();
    openStep('viewHome');
}

/* Numpad App Helpers */
function appendAppKey(char) {
    const inp = document.getElementById('appSearchInput');
    inp.value += char;
}
function backspaceAppKey() {
    const inp = document.getElementById('appSearchInput');
    inp.value = inp.value.slice(0, -1);
}
function clearAppInput() {
    document.getElementById('appSearchInput').value = '';
}

/* Numpad Returning Helpers */
function appendRetKey(char) {
    const inp = document.getElementById('returningSearchInput');
    inp.value += char;
}
function backspaceRetKey() {
    const inp = document.getElementById('returningSearchInput');
    inp.value = inp.value.slice(0, -1);
}

/* Luồng 1: Tra cứu lịch hẹn */
async function lookupAppointment(codeVal = null) {
    const query = codeVal || document.getElementById('appSearchInput').value.trim();
    if (!query) {
        alert('Vui lòng nhập Mã lịch hẹn hoặc Số điện thoại.');
        return;
    }

    const box = document.getElementById('appResultBox');
    box.innerHTML = `<div class="spinner-border text-primary my-4" role="status"></div><div class="fw-bold">Đang tra cứu lịch hẹn hôm nay...</div>`;

    try {
        const response = await fetch("{{ route('kiosk.checkin') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });
        const res = await response.json();

        if (res.success) {
            box.innerHTML = `
                <div class="text-success mb-2"><i class="bi bi-check-circle-fill display-3"></i></div>
                <h4 class="fw-bold text-dark mb-1">${res.appointment.patient_name}</h4>
                <div class="badge bg-primary fs-6 mb-3">Mã hẹn: ${res.appointment.code}</div>
                <div class="text-start bg-white p-3 rounded-3 border mb-3 small">
                    <div><strong>Chuyên khoa:</strong> ${res.appointment.specialty}</div>
                    <div><strong>Bác sĩ:</strong> ${res.appointment.doctor}</div>
                    <div><strong>Phòng khám:</strong> ${res.appointment.room}</div>
                    <div><strong>Số thứ tự:</strong> <span class="fs-5 fw-bold text-danger">A-${String(res.appointment.queue_number).padStart(3, '0')}</span></div>
                </div>
                <a href="${res.ticket_url}" class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-pill shadow">
                    <i class="bi bi-printer-fill me-2"></i> In Phiếu Tiếp Nhận Ngay
                </a>
            `;
        } else {
            box.innerHTML = `
                <div class="text-danger mb-2"><i class="bi bi-exclamation-circle-fill display-3"></i></div>
                <h5 class="fw-bold text-danger mb-2">Không Tìm Thấy</h5>
                <p class="text-muted small mb-3">${res.message}</p>
                <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="clearAppInput(); lookupAppointment();">Thử lại</button>
            `;
        }
    } catch (err) {
        box.innerHTML = `<div class="text-danger mb-2"><i class="bi bi-x-circle-fill display-3"></i></div><div class="text-danger fw-bold">Lỗi kết nối máy chủ Kiosk!</div>`;
    }
}

/* Luồng 2: Tra cứu hồ sơ cũ */
async function lookupReturningPatient(identVal = null) {
    const ident = identVal || document.getElementById('returningSearchInput').value.trim();
    if (!ident) {
        alert('Vui lòng nhập SĐT, CCCD hoặc Mã bệnh nhân.');
        return;
    }

    try {
        const response = await fetch("{{ route('kiosk.lookup') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ identifier: ident })
        });
        const res = await response.json();

        if (res.success) {
            currentPatientId = res.patient.id;
            document.getElementById('retPatientName').textContent = res.patient.full_name + ' (' + res.patient.patient_code + ')';
            document.getElementById('retPatientMeta').textContent = `SĐT: ${res.patient.phone} | NS: ${res.patient.dob || '--'} | Giới tính: ${res.patient.gender_name}`;
            
            document.getElementById('returningLookupStage').classList.add('d-none');
            document.getElementById('returningSpecialtyStage').classList.remove('d-none');
        } else {
            alert(res.message);
        }
    } catch (err) {
        alert('Lỗi tra cứu hồ sơ: ' + err.message);
    }
}

function resetReturningLookup() {
    currentPatientId = null;
    document.getElementById('returningSpecialtyStage').classList.add('d-none');
    document.getElementById('returningLookupStage').classList.remove('d-none');
    document.getElementById('returningSearchInput').value = '';
}

async function confirmReturningSpecialty(specialtyId, specialtyName) {
    if (!currentPatientId) return;

    if (!confirm(`Xác nhận đăng ký lấy số khám chuyên khoa: ${specialtyName}?`)) return;

    try {
        const response = await fetch("{{ route('kiosk.register-returning') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                patient_profile_id: currentPatientId,
                specialty_id: specialtyId
            })
        });
        const res = await response.json();

        if (res.success) {
            window.location.href = res.ticket_url;
        } else {
            alert(res.message);
        }
    } catch (err) {
        alert('Lỗi đăng ký khám: ' + err.message);
    }
}

/* Luồng 3: Đăng ký bệnh nhân mới */
async function submitNewPatient(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitNew');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Đang tạo hồ sơ...`;

    const data = {
        full_name: document.getElementById('newFullName').value.trim(),
        phone: document.getElementById('newPhone').value.trim(),
        birth_year: parseInt(document.getElementById('newBirthYear').value),
        gender: document.getElementById('newGender').value,
        identity_card: document.getElementById('newIdentityCard').value.trim(),
        insurance_code: document.getElementById('newInsuranceCode').value.trim(),
        specialty_id: parseInt(document.getElementById('newSpecialtyId').value),
    };

    try {
        const response = await fetch("{{ route('kiosk.register-new') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const res = await response.json();

        if (res.success) {
            window.location.href = res.ticket_url;
        } else {
            alert(res.message || 'Lỗi đăng ký hồ sơ bệnh nhân mới');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-printer-fill me-2"></i> Đăng Ký & Lấy Số Thứ Tự`;
        }
    } catch (err) {
        alert('Lỗi kết nối: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-printer-fill me-2"></i> Đăng Ký & Lấy Số Thứ Tự`;
    }
}

/* Camera Scanner Logic (Hạng mục 2: Webcam Barcode/QR Scanner) */
function startScanner(context) {
    currentScanContext = context;
    const modalEl = document.getElementById('cameraScannerModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const titleEl = document.getElementById('scannerModalTitle');
    const instEl = document.getElementById('scannerInstruction');
    const feedEl = document.getElementById('scannerFeedback');
    feedEl.textContent = 'Đang khởi động camera...';

    if (context === 'new_cccd') {
        titleEl.innerHTML = '<i class="bi bi-person-vcard text-primary me-2"></i> Quét Mã QR Căn Cước Công Dân Gắn Chip';
        instEl.textContent = 'Hướng mã QR ở góc trên bên phải mặt trước CCCD vào khung camera bên dưới';
    } else {
        titleEl.innerHTML = '<i class="bi bi-qr-code text-primary me-2"></i> Quét Mã QR / Barcode Tiếp Nhận';
        instEl.textContent = 'Hướng mã QR phiếu hẹn hoặc mã vạch thẻ bệnh nhân vào khung camera';
    }

    if (activeScanner) {
        activeScanner.clear();
    }

    activeScanner = new Html5Qrcode("reader");
    activeScanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 260, height: 260 }
        },
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        feedEl.textContent = 'Không thể mở camera hoặc quyền bị từ chối: ' + err;
    });
}

function stopScanner() {
    if (activeScanner) {
        activeScanner.stop().then(() => {
            activeScanner.clear();
            activeScanner = null;
        }).catch(err => {
            activeScanner = null;
        });
    }
    const modalEl = document.getElementById('cameraScannerModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}

function onScanFailure(error) {
    // silently pass frame scans
}

function onScanSuccess(decodedText, decodedResult) {
    const feedEl = document.getElementById('scannerFeedback');
    feedEl.innerHTML = `<span class="text-success"><i class="bi bi-check2-circle"></i> Đã nhận diện: ${decodedText}</span>`;

    // Âm thanh Beep báo hiệu đã quét thành công
    playBeepSound();

    setTimeout(() => {
        stopScanner();
        handleScannedCode(decodedText, currentScanContext);
    }, 400);
}

function playBeepSound() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch (e) {}
}

/**
 * Xử lý dữ liệu sau khi quét:
 * Hỗ trợ định dạng QR CCCD Bộ Công An: [CCCD]|[CMND]|[Họ tên]|[ddmmyyyy]|[Giới tính]|[Địa chỉ]|[Ngày cấp]
 */
function handleScannedCode(code, context) {
    const text = code.trim();

    // Kiểm tra có phải chuỗi CCCD gắn chip không
    const isCccd = text.includes('|');
    let cccdData = null;

    if (isCccd) {
        const parts = text.split('|');
        if (parts.length >= 5) {
            const rawDob = parts[3]; // ddmmyyyy
            let birthYear = '';
            if (rawDob && rawDob.length >= 8) {
                birthYear = rawDob.substring(4, 8);
            }
            cccdData = {
                id_card: parts[0],
                full_name: parts[2],
                birth_year: birthYear,
                gender: parts[4].toLowerCase().includes('nam') ? 'male' : 'female',
                address: parts[5] || ''
            };
        }
    }

    if (context === 'new_cccd' && cccdData) {
        // Tự động điền form bệnh nhân mới
        document.getElementById('newFullName').value = cccdData.full_name;
        document.getElementById('newIdentityCard').value = cccdData.id_card;
        if (cccdData.birth_year) document.getElementById('newBirthYear').value = cccdData.birth_year;
        document.getElementById('newGender').value = cccdData.gender;
        alert(`✅ Đã trích xuất thông tin CCCD gắn chip thành công!\nHọ tên: ${cccdData.full_name}\nSố CCCD: ${cccdData.id_card}\nNăm sinh: ${cccdData.birth_year}`);
        return;
    }

    if (context === 'app') {
        // Điền vào ô tìm kiếm lịch hẹn và tự động tra cứu
        const queryVal = isCccd && cccdData ? cccdData.id_card : text;
        document.getElementById('appSearchInput').value = queryVal;
        lookupAppointment(queryVal);
        return;
    }

    if (context === 'returning') {
        // Điền vào ô tra cứu hồ sơ cũ và tự động tra cứu
        const queryVal = isCccd && cccdData ? cccdData.id_card : text;
        document.getElementById('returningSearchInput').value = queryVal;
        lookupReturningPatient(queryVal);
        return;
    }
}
</script>
@endsection
