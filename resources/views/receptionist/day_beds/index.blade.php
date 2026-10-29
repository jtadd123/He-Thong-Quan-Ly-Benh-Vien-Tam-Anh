@extends('layouts.admin')

@section('title', 'Sơ Đồ Giường Ngoại Trú & Lưu Bệnh Ban Ngày')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-hospital text-primary me-2"></i>Sơ Đồ Giường Ngoại Trú & Khu Lưu Bệnh Ban Ngày
            </h4>
            <p class="text-muted small mb-0">Quản lý trực quan thời gian thực (Interactive Bed Map), theo dõi truyền dịch tại giường và cảnh báo thời gian lưu bệnh.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#admitPatientModal">
                <i class="bi bi-person-plus-fill"></i> Tiếp Nhận Vào Giường
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stat Summary Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Tổng số giường</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="bi bi-hospital fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Giường sẵn sàng</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $stats['available'] }}</h3>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đang có bệnh nhân</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $stats['occupied'] }}</h3>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đang khử khuẩn</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['cleaning'] }}</h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="bi bi-brush fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.day-beds.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="room" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả khu vực / phòng bệnh --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r }}" {{ request('room') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Loại giường --</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>Giường lưu thông thường</option>
                        <option value="infusion" {{ request('type') == 'infusion' ? 'selected' : '' }}>Giường truyền dịch</option>
                        <option value="recovery" {{ request('type') == 'recovery' ? 'selected' : '' }}>Giường hồi tỉnh sau nội soi</option>
                        <option value="observation" {{ request('type') == 'observation' ? 'selected' : '' }}>Giường theo dõi cấp cứu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Trạng thái giường --</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Sẵn sàng tiếp nhận</option>
                        <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Đang có bệnh nhân</option>
                        <option value="cleaning" {{ request('status') == 'cleaning' ? 'selected' : '' }}>Đang khử khuẩn / dọn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('reception.day-beds.index') }}" class="btn btn-sm btn-outline-secondary w-100">Làm mới</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Group Beds by Room -->
    @php
        $groupedBeds = $beds->groupBy('room_name');
    @endphp

    @forelse($groupedBeds as $roomName => $roomBeds)
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <h5 class="fw-bold mb-0 text-slate-800">
                    <i class="bi bi-door-open-fill text-primary me-1"></i>{{ $roomName }}
                </h5>
                <span class="badge bg-light text-dark border">{{ $roomBeds->count() }} giường</span>
            </div>

            <div class="row g-3">
                @foreach($roomBeds as $bed)
                    @php
                        $admission = $bed->currentAdmission;
                        $borderColor = match($bed->status) {
                            'available' => 'border-success',
                            'occupied' => 'border-danger',
                            'cleaning' => 'border-warning',
                            default => 'border-secondary',
                        };
                        $bgColor = match($bed->status) {
                            'available' => 'bg-white',
                            'occupied' => 'bg-danger bg-opacity-10',
                            'cleaning' => 'bg-warning bg-opacity-10',
                            default => 'bg-light',
                        };
                    @endphp

                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm border-2 {{ $borderColor }} {{ $bgColor }} transition-all">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-0 text-slate-900 font-monospace">{{ $bed->bed_code }}</h5>
                                        <small class="text-muted">{{ $bed->bed_type_label }}</small>
                                    </div>
                                    <div>
                                        {!! $bed->status_badge !!}
                                    </div>
                                </div>

                                @if($bed->status === 'occupied' && $admission)
                                    <div class="p-2 bg-white rounded-3 border mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-primary fs-6">{{ $admission->patientProfile->full_name }}</strong>
                                            <span class="badge bg-primary-subtle text-primary">{{ $admission->patientProfile->patient_code }}</span>
                                        </div>
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-clock me-1"></i>Đã nằm: <strong>{{ $admission->duration_hours }} giờ</strong> (Vào: {{ $admission->admitted_at->format('H:i d/m') }})
                                        </div>
                                        <div class="small text-dark mb-2">
                                            <i class="bi bi-activity text-danger me-1"></i>Lý do: <em>{{ Str::limit($admission->admission_reason, 45) }}</em>
                                        </div>

                                        @if($admission->is_overstay)
                                            <div class="badge bg-danger text-white w-100 text-wrap mb-2">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Cảnh báo: Lưu viện > 4 giờ ngoại trú!
                                            </div>
                                        @endif

                                        <!-- Active Infusions -->
                                        @php $activeInfusion = $admission->ivInfusions->where('status', 'running')->first(); @endphp
                                        @if($activeInfusion)
                                            <div class="bg-light p-2 rounded small mt-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-semibold text-info-emphasis"><i class="bi bi-droplet-half text-info me-1"></i>{{ $activeInfusion->fluid_name }}</span>
                                                    <span class="badge bg-info text-dark">{{ $activeInfusion->drip_rate_drops_per_min }} g/p</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ $activeInfusion->progress_percent }}%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.7rem;">
                                                    <span>Tiến độ: {{ $activeInfusion->progress_percent }}%</span>
                                                    <span>Dự kiến xong: {{ $activeInfusion->estimated_finish_at?->format('H:i') }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reception.day-beds.show', $admission) }}" class="btn btn-sm btn-primary flex-grow-1">
                                            <i class="bi bi-file-earmark-medical me-1"></i>Bảng Theo Dõi
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#dischargeModal-{{ $admission->id }}" title="Xuất giường">
                                            <i class="bi bi-box-arrow-right"></i> Xuất viện
                                        </button>
                                    </div>

                                    <!-- Discharge Modal -->
                                    <div class="modal fade" id="dischargeModal-{{ $admission->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('reception.day-beds.discharge', $admission) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-danger">
                                                            <i class="bi bi-box-arrow-right me-1"></i> Xuất Giường Bệnh Nhân
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p class="mb-2">Bệnh nhân: <strong>{{ $admission->patientProfile->full_name }}</strong> (Mã: {{ $admission->patientProfile->patient_code }})</p>
                                                        <p class="small text-muted mb-3">Thời gian lưu: {{ $admission->duration_hours }} giờ tại giường {{ $bed->bed_code }}.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Ghi chú xuất viện / Tình trạng ra viện:</label>
                                                            <textarea name="discharge_notes" class="form-control form-control-sm" rows="3" placeholder="Sinh hiệu ổn định, hết sốt, dặn dò uống thuốc theo đơn...">Sinh hiệu ổn định, các triệu chứng thuyên giảm tốt, cho về ngoại trú tiếp tục theo dõi.</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-sm btn-danger">Xác Nhận Xuất Giường</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @elseif($bed->status === 'available')
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success opacity-50 d-block mb-1"></i>
                                        <span class="small">Giường sẵn sàng tiếp nhận bệnh nhân</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#admitPatientModal" onclick="preselectBed({{ $bed->id }})">
                                        <i class="bi bi-plus-circle me-1"></i>Tiếp nhận vào giường này
                                    </button>

                                @elseif($bed->status === 'cleaning')
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-brush fs-1 text-warning opacity-50 d-block mb-1"></i>
                                        <span class="small">Đang trong quy trình khử trùng vô khuẩn</span>
                                    </div>
                                    <form action="{{ route('reception.day-beds.cleaning-done', $bed) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning w-100">
                                            <i class="bi bi-check-all me-1"></i>Báo Đã Khử Trùng Xong
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm p-5 text-center text-muted">
            <i class="bi bi-hospital fs-1 d-block mb-2"></i>
            Chưa có giường bệnh nào được cấu hình trong khu vực này.
        </div>
    @endforelse
</div>

<!-- Modal Tiếp Nhận Bệnh Nhân Vào Giường -->
<div class="modal fade" id="admitPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('reception.day-beds.admit') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Tiếp Nhận Bệnh Nhân Vào Giường Ngoại Trú
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Chọn giường bệnh <span class="text-danger">*</span></label>
                            <select name="day_bed_id" id="modalBedSelect" class="form-select" required>
                                <option value="">-- Chọn giường trống --</option>
                                @foreach($beds->where('status', 'available') as $availBed)
                                    <option value="{{ $availBed->id }}">{{ $availBed->bed_code }} ({{ $availBed->room_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Chọn bệnh nhân <span class="text-danger">*</span></label>
                            <select name="patient_profile_id" class="form-select" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }} - {{ $p->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bác sĩ chỉ định lưu bệnh</label>
                            <select name="attending_doctor_id" class="form-select">
                                <option value="">-- Không chỉ định cụ thể --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->user->name }} ({{ $doc->specialty->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Điều dưỡng phụ trách theo dõi</label>
                            <input type="text" name="attending_nurse_name" class="form-control" value="{{ auth()->user()->name }}" placeholder="Họ tên điều dưỡng">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Lý do lưu bệnh / Chẩn đoán sơ bộ <span class="text-danger">*</span></label>
                            <input type="text" name="admission_reason" class="form-control" placeholder="Truyền dịch bù điện giải, sốt siêu vi, theo dõi sau nội soi gây mê..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Dự kiến lưu (Giờ)</label>
                            <input type="number" name="stay_hours" class="form-control" value="4" min="1" max="24">
                        </div>

                        <!-- Kèm chỉ định truyền dịch ban đầu (Tùy chọn) -->
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-primary small mb-2"><i class="bi bi-droplet-half me-1"></i>Chỉ định truyền dịch tĩnh mạch (Tùy chọn)</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Tên dịch truyền</label>
                            <input type="text" name="fluid_name" class="form-control form-control-sm" placeholder="VD: Natri Clorid 0.9% 500ml hoặc Glucose 5%">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Thể tích (ml)</label>
                            <input type="number" name="volume_ml" class="form-control form-control-sm" value="500">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tốc độ (giọt/phút)</label>
                            <input type="number" name="drip_rate_drops_per_min" class="form-control form-control-sm" value="30">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Xác Nhận Tiếp Nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function preselectBed(bedId) {
        const select = document.getElementById('modalBedSelect');
        if (select) {
            select.value = bedId;
        }
    }
</script>
@endsection
