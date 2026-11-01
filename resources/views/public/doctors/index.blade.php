@extends('layouts.master')

@section('title', 'Đội Ngũ Bác Sĩ Chuyên Khoa - Bệnh Viện Đa Khoa Tâm Anh')

@section('content')
<!-- Header Banner -->
<div class="bg-primary text-white p-4 p-md-5 rounded-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold mb-2">Đội ngũ Chuyên gia Hàng đầu</span>
            <h1 class="fw-bold mb-2 display-6">Danh Bạ Bác Sĩ Bệnh Viện Tâm Anh</h1>
            <p class="mb-0 text-white-50 lead fs-6">
                Quy tụ các Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa giàu kinh nghiệm, tận tâm vì sức khỏe người bệnh và ứng dụng các kỹ thuật y khoa hiện đại.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('booking.create') }}" class="btn btn-light btn-lg px-4 rounded-pill fw-bold text-primary shadow-sm">
                <i class="bi bi-calendar-check me-1"></i> Đặt lịch hẹn ngay
            </a>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card shadow-sm border-0 mb-4 rounded-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('public.doctors.index') }}" class="row g-3" id="doctor-filter-form">
            <div class="col-md-7 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="doctor-search-input" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Nhập tên bác sĩ, học vị (PGS, TS, ThS), phòng khám..." autocomplete="off">
                    <span class="input-group-text bg-white border-start-0 d-none" id="doctor-search-spinner">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    </span>
                </div>
            </div>
            <div class="col-md-5 col-lg-4 d-flex gap-2">
                <select name="specialty_id" id="doctor-specialty-select" class="form-select">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>
                            {{ $sp->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1 flex-shrink-0">
                    <i class="bi bi-search"></i> Tìm
                </button>
                @if(request('search') || request('specialty_id') || request()->boolean('on_duty_today'))
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
            <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="on_duty_today" value="1" id="on_duty_today" {{ request()->boolean('on_duty_today') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-dark small" for="on_duty_today">
                        <i class="bi bi-clock-history text-success me-1"></i> Chỉ hiển thị bác sĩ có lịch trực hôm nay
                    </label>
                </div>
                <span class="text-muted small" id="doctor-count-badge">
                    <i class="bi bi-info-circle me-1"></i> Tìm thấy <strong>{{ $doctors->total() }}</strong> bác sĩ phù hợp
                </span>
            </div>
        </form>
    </div>
</div>

<!-- AJAX Dynamic Doctor Grid Container -->
<div id="doctor-grid-container" class="position-relative">
    @include('public.doctors.partials.doctor_grid')
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('doctor-search-input');
    const specialtySelect = document.getElementById('doctor-specialty-select');
    const onDutyCheckbox = document.getElementById('on_duty_today');
    const container = document.getElementById('doctor-grid-container');
    const spinner = document.getElementById('doctor-search-spinner');
    const form = document.getElementById('doctor-filter-form');

    let debounceTimer = null;

    function fetchDoctorsAjax(url) {
        if (spinner) spinner.classList.remove('d-none');
        if (container) container.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            console.error('AJAX Live Search Error:', err);
        })
        .finally(() => {
            if (spinner) spinner.classList.add('d-none');
            if (container) container.style.opacity = '1';
        });
    }

    function triggerSearch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const params = new URLSearchParams();
            if (searchInput && searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }
            if (specialtySelect && specialtySelect.value) {
                params.set('specialty_id', specialtySelect.value);
            }
            if (onDutyCheckbox && onDutyCheckbox.checked) {
                params.set('on_duty_today', '1');
            }

            const targetUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', targetUrl);
            fetchDoctorsAjax(targetUrl);
        }, 300);
    }

    if (searchInput) {
        searchInput.addEventListener('input', triggerSearch);
    }
    if (specialtySelect) {
        specialtySelect.addEventListener('change', triggerSearch);
    }
    if (onDutyCheckbox) {
        onDutyCheckbox.addEventListener('change', triggerSearch);
    }

    // Intercept pagination clicks for smooth AJAX page switching
    document.addEventListener('click', function (e) {
        const pageLink = e.target.closest('#doctor-grid-container .pagination a');
        if (pageLink) {
            e.preventDefault();
            const pageUrl = pageLink.getAttribute('href');
            if (pageUrl) {
                window.history.replaceState({}, '', pageUrl);
                fetchDoctorsAjax(pageUrl);
                window.scrollTo({
                    top: container.offsetTop - 120,
                    behavior: 'smooth'
                });
            }
        }
    });
});
</script>
@endpush
@endsection
