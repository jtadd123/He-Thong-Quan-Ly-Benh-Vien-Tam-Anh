@extends('layouts.admin')

@section('title', 'Lịch Sử Khám Bệnh Của Tôi')

@section('content')
<x-page-header title="Lịch Sử Khám Bệnh" subtitle="Theo dõi danh sách các cuộc hẹn, tiến trình khám và kết quả điều trị của bạn">
    <x-slot:actions>
        <a href="{{ route('patient.family.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-people-fill"></i> Hồ sơ người thân
        </a>
        <a href="{{ route('booking.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-calendar-plus-fill"></i> Đặt lịch khám mới
        </a>
    </x-slot:actions>
</x-page-header>

@if(!$patientProfile)
    <div class="alert alert-info d-flex align-items-center gap-3 p-4">
        <i class="bi bi-info-circle-fill fs-2"></i>
        <div>
            <h5 class="fw-bold mb-1">Chưa có hồ sơ bệnh nhân liên kết</h5>
            <p class="mb-0 small text-muted">
                Bạn chưa thực hiện cuộc hẹn khám nào trên hệ thống. Hãy bấm <strong>Đặt lịch khám mới</strong> để bắt đầu tạo hồ sơ y tế.
            </p>
        </div>
    </div>
@else
    <!-- Patient Profile Header Card -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 58px; height: 58px;">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1 text-dark">{{ $patientProfile->full_name }}</h5>
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span>Mã BN: <strong class="text-primary font-monospace">{{ $patientProfile->patient_code }}</strong></span>
                        <span>Ngày sinh: <strong>{{ $patientProfile->dob?->format('d/m/Y') ?? 'Chưa cập nhật' }}</strong></span>
                        <span>Giới tính: <strong>{{ $patientProfile->gender_name }}</strong></span>
                        <span>SĐT: <strong>{{ $patientProfile->phone }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('patient.history') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
            Tất cả
        </a>
        <a href="{{ route('patient.history', ['status' => 'waitlist']) }}" class="btn btn-sm {{ request('status') === 'waitlist' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} rounded-pill px-3">
            Danh sách chờ (Waitlist)
        </a>
        <a href="{{ route('patient.history', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} rounded-pill px-3">
            Chờ tiếp nhận
        </a>
        <a href="{{ route('patient.history', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') === 'confirmed' ? 'btn-info text-dark' : 'btn-outline-info text-dark' }} rounded-pill px-3">
            Đã tiếp nhận (Chờ khám)
        </a>
        <a href="{{ route('patient.history', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-success text-white' : 'btn-outline-success' }} rounded-pill px-3">
            Đã khám xong
        </a>
        <a href="{{ route('patient.history', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger text-white' : 'btn-outline-danger' }} rounded-pill px-3">
            Đã hủy
        </a>
    </div>

    <!-- Appointments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Mã lịch hẹn</th>
                            <th>Chuyên khoa</th>
                            <th>Bác sĩ phụ trách</th>
                            <th>Thời gian khám</th>
                            <th>Triệu chứng</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3" style="width: 140px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $app)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">
                                    {{ $app->appointment_code }}
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $app->specialty->name }}</span>
                                </td>
                                <td>
                                    <div>{{ $app->doctor->full_name_with_title }}</div>
                                    <small class="text-muted">Phòng {{ $app->doctor->room_number }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $app->appointment_date->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $app->shift_name }}</small>
                                </td>
                                <td class="small text-truncate" style="max-width: 180px;" title="{{ $app->symptom_description }}">
                                    {{ $app->symptom_description }}
                                </td>
                                <td>
                                    <x-status-badge :status="$app->status" />
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('patient.appointments.show', $app) }}" class="btn btn-outline-primary" title="Xem kết quả & đơn thuốc">
                                            <i class="bi bi-eye"></i> Xem
                                        </a>
                                        @if(in_array($app->status, ['pending', 'confirmed']))
                                            <a href="{{ route('patient.appointments.reschedule', $app) }}" class="btn btn-outline-warning text-dark" title="Đổi ngày / ca khám">
                                                <i class="bi bi-calendar2-range"></i> Đổi
                                            </a>
                                        @endif
                                        @if(in_array($app->status, ['pending', 'confirmed', 'waitlist']))
                                            <button type="button" class="btn btn-outline-danger" title="Hủy lịch hẹn" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $app->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Modal Cancel Appointment -->
                                    @if(in_array($app->status, ['pending', 'confirmed', 'waitlist']))
                                        <div class="modal fade" id="cancelModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <form method="POST" action="{{ route('patient.appointments.cancel', $app) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold text-danger">Hủy lịch hẹn: {{ $app->appointment_code }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">
                                                                Bạn có chắc chắn muốn hủy ca khám này? Sau khi hủy, khung giờ sẽ được giải phóng cho bệnh nhân khác.
                                                            </p>
                                                            <label class="form-label fw-semibold small">Lý do hủy lịch: <span class="text-danger">*</span></label>
                                                            <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Ví dụ: Bận đột xuất, chuyển đổi lịch công tác..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                            <button type="submit" class="btn btn-danger btn-sm">Xác nhận hủy hẹn</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Bạn chưa có lịch hẹn nào phù hợp với bộ lọc hiện tại.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($appointments->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
