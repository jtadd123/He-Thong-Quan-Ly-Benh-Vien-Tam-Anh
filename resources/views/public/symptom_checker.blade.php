@extends('layouts.master')

@section('title', 'Trợ Lý Gợi Ý Chuyên Khoa & Bác Sĩ Thông Minh | Bệnh Viện Tâm Anh')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('public.doctors.index') }}" class="text-decoration-none">Đội ngũ Bác sĩ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Trợ lý Triệu chứng Thông minh</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 text-primary fw-semibold small mb-2">
                <i class="bi bi-robot"></i> Công nghệ Hỗ trợ Chẩn đoán Sơ bộ
            </div>
            <h1 class="fw-bold text-dark display-6 mb-2">Trợ Lý Gợi Ý Chuyên Khoa & Bác Sĩ Thông Minh</h1>
            <p class="text-muted lead fs-6 mb-0">
                Nhập các triệu chứng hoặc cảm giác khó chịu của bạn. Hệ thống sẽ phân tích từ khóa y khoa để gợi ý chuyên khoa phù hợp và đề xuất bác sĩ chuyên khoa sẵn sàng khám.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Xem danh bạ tất cả bác sĩ
            </a>
        </div>
    </div>

    <!-- Main Card Input & Assistant -->
    <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-body p-4 p-md-5 bg-white">
            <div class="row g-4">
                <div class="col-lg-8">
                    <label for="symptomInput" class="form-label fw-bold text-dark fs-5 mb-2">
                        <i class="bi bi-chat-heart text-danger me-2"></i>Bạn đang cảm thấy không khỏe như thế nào?
                    </label>
                    <div class="position-relative">
                        <textarea id="symptomInput" rows="4" class="form-control form-control-lg rounded-4 shadow-none border-2" 
                            placeholder="Ví dụ: Tôi bị đau thắt ngực khi leo cầu thang, kèm hồi hộp khó thở và tim đập nhanh liên tục..."></textarea>
                    </div>

                    <!-- Quick Sample Pills -->
                    <div class="mt-3">
                        <span class="text-muted small fw-semibold me-2"><i class="bi bi-lightning-charge-fill text-warning"></i> Triệu chứng thường gặp (bấm để thử):</span>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Tôi bị đau thắt ngực khi gắng sức, hồi hộp và tim đập nhanh">
                                💔 Đau ngực, tim đập nhanh
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Bé nhà tôi bị sốt cao kèm co giật, biếng ăn và nôn trớ">
                                👶 Trẻ em sốt, nôn trớ
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Tôi bị đau khớp gối dữ dội khi đi lại, cứng khớp buổi sáng">
                                🦴 Đau khớp gối, cứng khớp
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Đau họng, nghẹt mũi, khàn tiếng và nuốt vướng">
                                🗣️ Đau họng, khàn tiếng
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Đau dạ dày, ợ chua, trào ngược sau khi ăn, đau thượng vị">
                                🍽️ Đau dạ dày, ợ chua
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill sample-pill" data-text="Mắt nhìn mờ đột ngột, đỏ mắt và cộm ngứa chảy nước mắt">
                                👁️ Mắt mờ, đỏ mắt
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 d-flex flex-column justify-content-between border-start-lg ps-lg-4">
                    <div class="p-3 rounded-4 bg-light">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-1"></i> Lưu ý y khoa</h6>
                        <p class="text-muted small mb-0">
                            Kết quả mang tính chất tham vấn định hướng sơ bộ giúp bệnh nhân chọn đúng chuyên khoa. Không thay thế cho quá trình thăm khám lâm sàng trực tiếp bởi bác sĩ chuyên khoa.
                        </p>
                    </div>
                    <div class="mt-4">
                        <button type="button" id="btnAnalyze" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-magic fs-5"></i>
                            <span>Phân Tích Triệu Chứng Ngay</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner (Hidden by default) -->
    <div id="loadingIndicator" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
            <span class="visually-hidden">Đang phân tích...</span>
        </div>
        <p class="text-muted mt-3 fw-semibold">Hệ thống đang đối soát từ khóa y khoa và tìm kiếm bác sĩ chuyên khoa...</p>
    </div>

    <!-- Match Result Section (Hidden initially) -->
    <div id="resultSection" class="d-none">
        <!-- Emergency Alert Banner (Shown only if emergency trigger detected) -->
        <div id="emergencyBanner" class="alert alert-danger rounded-4 border-0 shadow-sm p-4 mb-4 d-none">
            <div class="d-flex align-items-start gap-3">
                <div class="fs-1 text-danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">CẢNH BÁO TRIỆU CHỨNG CẦN CẤP CỨU KHẨN CẤP!</h5>
                    <p id="emergencyText" class="mb-2"></p>
                    <a href="tel:18006858" class="btn btn-danger btn-sm rounded-pill fw-bold">
                        <i class="bi bi-telephone-fill me-1"></i> Gọi Hotline Cấp cứu: 1800 6858
                    </a>
                </div>
            </div>
        </div>

        <!-- Matched Specialty Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-primary text-white p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-2">
                            <i class="bi bi-check2-circle me-1"></i> Gợi Ý Hàng Đầu
                        </span>
                        <h3 class="fw-bold mb-0 text-white" id="matchedSpecialtyName"></h3>
                    </div>
                    <div class="text-end">
                        <span class="small text-white-50 d-block">Độ phù hợp ước tính</span>
                        <span class="display-6 fw-bold text-white" id="matchedConfidence"></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row g-4 align-items-center">
                    <div class="col-md-8">
                        <h6 class="text-muted small fw-bold text-uppercase mb-2">Từ khóa triệu chứng đã nhận diện:</h6>
                        <div id="matchedKeywordsList" class="d-flex flex-wrap gap-2 mb-3"></div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Lời khuyên chuyên môn:</h6>
                        <p id="matchedAdvice" class="text-dark mb-0"></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a id="btnFilterBySpecialty" href="#" class="btn btn-outline-primary rounded-pill px-4">
                            Xem tất cả bác sĩ khoa này <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Doctors Grid -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-check-fill text-primary me-2"></i>Bác Sĩ Chuyên Khoa Đề Xuất
                </h4>
                <span class="text-muted small">Sẵn sàng tiếp nhận bệnh nhân</span>
            </div>

            <div id="doctorsListContainer" class="row g-4">
                <!-- Injected via JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const symptomInput = document.getElementById('symptomInput');
    const btnAnalyze = document.getElementById('btnAnalyze');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultSection = document.getElementById('resultSection');
    const emergencyBanner = document.getElementById('emergencyBanner');
    const emergencyText = document.getElementById('emergencyText');
    const matchedSpecialtyName = document.getElementById('matchedSpecialtyName');
    const matchedConfidence = document.getElementById('matchedConfidence');
    const matchedKeywordsList = document.getElementById('matchedKeywordsList');
    const matchedAdvice = document.getElementById('matchedAdvice');
    const btnFilterBySpecialty = document.getElementById('btnFilterBySpecialty');
    const doctorsListContainer = document.getElementById('doctorsListContainer');

    // Click sample pill
    document.querySelectorAll('.sample-pill').forEach(pill => {
        pill.addEventListener('click', function () {
            symptomInput.value = this.getAttribute('data-text');
            runAnalysis();
        });
    });

    // Click button
    btnAnalyze.addEventListener('click', function () {
        runAnalysis();
    });

    function runAnalysis() {
        const text = symptomInput.value.trim();
        if (!text) {
            alert('Vui lòng nhập mô tả triệu chứng của bạn để bắt đầu phân tích.');
            symptomInput.focus();
            return;
        }

        loadingIndicator.classList.remove('d-none');
        resultSection.classList.add('d-none');
        btnAnalyze.disabled = true;

        fetch('{{ route('public.doctors.symptom_match') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ symptoms: text })
        })
        .then(res => res.json())
        .then(data => {
            loadingIndicator.classList.add('d-none');
            btnAnalyze.disabled = false;
            resultSection.classList.remove('d-none');

            if (!data.has_match) {
                matchedSpecialtyName.textContent = data.fallback_specialty ? data.fallback_specialty.name : 'Nội Tổng Quát';
                matchedConfidence.textContent = '70%';
                matchedKeywordsList.innerHTML = '<span class="badge bg-secondary rounded-pill">Triệu chứng chung</span>';
                matchedAdvice.textContent = data.message;
                emergencyBanner.classList.add('d-none');
                doctorsListContainer.innerHTML = '<div class="col-12"><div class="alert alert-light border rounded-4 text-center p-4">Vui lòng đăng ký khám Tổng quát để được bác sĩ chỉ định cận lâm sàng phù hợp.</div></div>';
                return;
            }

            // Emergency alert
            if (data.is_emergency && data.emergency_alert) {
                emergencyText.textContent = data.emergency_alert;
                emergencyBanner.classList.remove('d-none');
            } else {
                emergencyBanner.classList.add('d-none');
            }

            // Specialty data
            matchedSpecialtyName.textContent = data.specialty.name;
            matchedConfidence.textContent = data.confidence_score + '%';
            matchedAdvice.textContent = data.advice;

            btnFilterBySpecialty.href = '{{ route('public.doctors.index') }}?specialty_id=' + data.specialty.id;

            // Keywords badges
            matchedKeywordsList.innerHTML = '';
            if (data.matched_keywords && data.matched_keywords.length > 0) {
                data.matched_keywords.forEach(kw => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill';
                    span.innerHTML = '<i class="bi bi-tag-fill me-1"></i> ' + kw;
                    matchedKeywordsList.appendChild(span);
                });
            }

            // Doctors list
            doctorsListContainer.innerHTML = '';
            if (data.recommended_doctors && data.recommended_doctors.length > 0) {
                data.recommended_doctors.forEach(doc => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-3';
                    col.innerHTML = `
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden doctor-card">
                            <div class="position-relative">
                                <img src="${doc.avatar_url}" class="card-img-top object-fit-cover" style="height: 200px;" alt="${doc.name}">
                                ${doc.is_on_duty_today ? '<span class="position-absolute top-0 end-0 m-2 badge bg-success shadow-sm rounded-pill"><i class="bi bi-clock-fill me-1"></i> Có lịch hôm nay</span>' : ''}
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill align-self-start mb-2 px-2 py-1 small">${doc.qualification}</span>
                                <h6 class="fw-bold text-dark mb-1">${doc.name}</h6>
                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-primary"></i> ${doc.room_number || 'Phòng khám đa khoa'}</p>
                                <div class="mt-auto pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Phí khám:</span>
                                        <span class="fw-bold text-primary small">${doc.consultation_fee}</span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="${doc.booking_url}" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                                            <i class="bi bi-calendar-check me-1"></i> Đặt lịch ngay
                                        </a>
                                        <a href="${doc.profile_url}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                            Xem hồ sơ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    doctorsListContainer.appendChild(col);
                });
            } else {
                doctorsListContainer.innerHTML = '<div class="col-12"><div class="alert alert-light border rounded-4 text-center p-4">Hiện chuyên khoa đang cập nhật thêm bác sĩ. Bạn có thể chọn chuyên khoa khác hoặc liên hệ hotline.</div></div>';
            }

            // Scroll to results smoothly
            resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(err => {
            loadingIndicator.classList.add('d-none');
            btnAnalyze.disabled = false;
            alert('Đã xảy ra lỗi trong quá trình phân tích. Vui lòng thử lại sau.');
            console.error(err);
        });
    }
});
</script>
@endpush
@endsection
