<div class="row g-4 mb-4">
    @forelse($doctors as $doctor)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden doctor-card-hover transition">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold fs-3 flex-shrink-0 overflow-hidden" style="width: 64px; height: 64px; border: 2px solid #e2e8f0;">
                            @if($doctor->avatar_url)
                                <img src="{{ $doctor->avatar_url }}" alt="{{ $doctor->full_name_with_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-person-badge"></i>
                            @endif
                        </div>
                        <div>
                            @if($doctor->specialty)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small mb-1">
                                    <i class="{{ $doctor->specialty->icon ?: 'bi bi-tag' }} me-1"></i> {{ $doctor->specialty->name }}
                                </span>
                            @endif
                            <h5 class="fw-bold text-dark mb-0 lh-sm">{{ $doctor->full_name_with_title }}</h5>
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $doctor->room_number }}</small>
                            <div class="mt-1">
                                @if($doctor->is_on_duty_today)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar2-check-fill me-1"></i> Trực hôm nay: {{ $doctor->today_shifts_text }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar3 me-1"></i> Nhận đặt theo hẹn
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $doctor->bio ?: 'Bác sĩ chuyên khoa giàu kinh nghiệm tại Bệnh viện Đa khoa Tâm Anh, tận tâm chăm sóc sức khỏe toàn diện cho bệnh nhân.' }}
                    </p>

                    <div class="border-top pt-3 mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Phí khám ban đầu:</span>
                            <span class="fw-bold text-primary fs-6">{{ number_format($doctor->consultation_fee, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('booking.create') }}?doctor_id={{ $doctor->id }}&specialty_id={{ $doctor->specialty_id }}" class="btn btn-primary rounded-pill fw-semibold">
                                <i class="bi bi-calendar-plus me-1"></i> Đặt lịch khám
                            </a>
                            <a href="{{ route('public.doctors.show', $doctor) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                Xem lý lịch chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center text-muted rounded-4">
                <i class="bi bi-search fs-1 d-block mb-3 text-secondary opacity-50"></i>
                <h5 class="text-dark fw-bold">Không tìm thấy bác sĩ nào phù hợp</h5>
                <p class="small text-muted mb-3">Vui lòng thử tìm với từ khóa khác hoặc điều chỉnh bộ lọc chuyên khoa.</p>
                <div>
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Xem tất cả bác sĩ
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($doctors->hasPages())
    <div class="d-flex justify-content-center mb-4">
        {{ $doctors->links() }}
    </div>
@endif
