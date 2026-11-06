@extends('layouts.master')

@section('title', 'Hồ Sơ Sức Khỏe Gia Đình & Người Thân - Bệnh Viện Tâm Anh')

@section('content')
<div class="container py-4">
    <!-- Quick Patient Portal Navigation Bar -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('patient.history') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-journal-medical me-1"></i> Lịch sử khám của tôi
        </a>
        <a href="{{ route('patient.family.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-people-fill me-1"></i> Hồ sơ người thân & gia đình
        </a>
        <a href="{{ route('booking.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-calendar-plus me-1"></i> Đặt lịch khám mới
        </a>
        <a href="{{ route('two-factor.setup') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-shield-lock me-1"></i> Bảo mật 2FA
        </a>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Hồ Sơ Sức Khỏe Người Thân & Gia Đình</h3>
            <p class="text-muted mb-0">Quản lý danh sách thành viên gia đình để đặt lịch khám nhanh chóng và theo dõi lịch sử điều trị</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="bi bi-person-plus-fill me-1"></i>Thêm thành viên mới
        </button>
    </div>

    <div class="row g-4">
        <!-- Primary Patient Card -->
        <div class="col-md-4">
            <div class="card h-100 border-2 border-primary shadow-sm bg-primary-subtle bg-opacity-25">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary px-3 py-2">Chủ tài khoản (Bản thân)</span>
                        <code class="fw-bold">{{ $profile->patient_code }}</code>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $profile->full_name }}</h5>
                    <div class="text-muted small mb-3">
                        <i class="bi bi-calendar-event me-1"></i>{{ ($profile->date_of_birth ?? $profile->dob) ? \Carbon\Carbon::parse($profile->date_of_birth ?? $profile->dob)->format('d/m/Y') : 'Chưa cập nhật' }} 
                        &bull; {{ ($profile->gender == 'male' ? 'Nam' : ($profile->gender == 'female' ? 'Nữ' : 'Khác')) }}
                    </div>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><i class="bi bi-telephone me-2 text-primary"></i>{{ $profile->phone }}</li>
                        @if($profile->identity_card)
                            <li class="mb-1"><i class="bi bi-card-heading me-2 text-primary"></i>CCCD: {{ $profile->identity_card }}</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Family Members Cards -->
        @forelse($members as $m)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 fw-bold">
                                {{ $m->relationship_label }}
                            </span>
                            <form action="{{ route('patient.family.destroy', $m) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ người thân này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Xóa người thân">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $m->full_name }}</h5>
                        <div class="text-muted small mb-3">
                            <i class="bi bi-calendar-event me-1"></i>{{ $m->date_of_birth?->format('d/m/Y') ?? 'Chưa cập nhật' }} ({{ $m->age }} tuổi) &bull; {{ $m->gender == 'male' ? 'Nam' : ($m->gender == 'female' ? 'Nữ' : 'Khác') }}
                        </div>
                        <ul class="list-unstyled small mb-3">
                            @if($m->phone)
                                <li class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i>{{ $m->phone }}</li>
                            @endif
                            @if($m->health_insurance_code)
                                <li class="mb-1"><i class="bi bi-shield-plus me-2 text-success"></i>BHYT: {{ $m->health_insurance_code }}</li>
                            @endif
                            @if($m->medical_notes)
                                <li class="mb-1 text-secondary fst-italic"><i class="bi bi-clipboard-pulse me-2 text-warning"></i>{{ $m->medical_notes }}</li>
                            @endif
                        </ul>

                        <div class="pt-2 border-top">
                            <a href="{{ route('booking.create') }}?family_member_id={{ $m->id }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-calendar2-plus me-1"></i>Đặt lịch khám cho người này
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-8">
                <div class="card h-100 border-0 bg-light p-4 text-center justify-content-center">
                    <i class="bi bi-people display-5 text-muted mb-2"></i>
                    <h6 class="fw-bold text-secondary">Bạn chưa thêm người thân nào vào danh sách</h6>
                    <p class="small text-muted mb-3">Thêm người thân để dễ dàng đặt lịch khám cho Con cái, Bố mẹ hoặc Người phụ thuộc trong gia đình.</p>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="bi bi-plus-lg me-1"></i>Thêm người thân ngay
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addMemberModalLabel"><i class="bi bi-person-plus-fill me-2"></i>Thêm Hồ Sơ Người Thân</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patient.family.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Họ và tên người thân <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="Ví dụ: Nguyễn Văn An" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mối quan hệ <span class="text-danger">*</span></label>
                            <select name="relationship" class="form-select" required>
                                <option value="child">Con cái</option>
                                <option value="spouse">Vợ / Chồng</option>
                                <option value="parent">Cha / Mẹ</option>
                                <option value="sibling">Anh / Chị / Em</option>
                                <option value="other">Người thân khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Giới tính <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại liên hệ</label>
                            <input type="tel" name="phone" class="form-control" placeholder="0xxxxxxxxx">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số CCCD / Định danh</label>
                            <input type="text" name="id_card_number" class="form-control" placeholder="12 chữ số">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số thẻ BHYT (nếu có)</label>
                            <input type="text" name="health_insurance_code" class="form-control" placeholder="15 ký tự">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Ghi chú y tế / Tiền sử dị ứng đặc biệt</label>
                        <textarea name="medical_notes" class="form-control" rows="2" placeholder="Ví dụ: Dị ứng Penicillin, hen suyễn nhẹ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Lưu hồ sơ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
