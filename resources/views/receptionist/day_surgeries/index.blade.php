@extends('layouts.admin')

@section('title', 'Tiếp Nhận Tiền Phẫu & Phẫu Thuật Trong Ngày')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-hospital-fill me-2"></i>Tiếp Nhận Tiền Phẫu & Phẫu Thuật Trong Ngày (Day Surgery)
            </h4>
            <p class="text-muted small mb-0">Checklist an toàn tiền phẫu WHO, đếm ngược nhịn ăn và điều phối phòng mổ ngoại trú - Bệnh Viện Tâm Anh</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newSurgeryModal">
                <i class="bi bi-calendar-plus"></i> Đặt Lịch Phẫu Thuật Mới
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Ca Phẫu Thuật</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total_surgeries'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-calendar2-week fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đạt Checklist Sẵn Sàng</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['ready'] }} ca</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-clipboard2-check-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đang Trong Phòng Mổ</div>
                            <div class="fs-4 fw-bold text-danger">{{ $stats['in_surgery'] }} ca</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đã Xuất Viện Về Nhà</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['discharged'] }} ca</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-house-check-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Ca Mổ</th>
                            <th>Người Bệnh Phẫu Thuật</th>
                            <th>Tên Thủ Thuật / Phẫu Thuật</th>
                            <th>Bác Sĩ Phẫu Thuật</th>
                            <th>Thời Gian & Phòng Mổ</th>
                            <th>Nhịn Ăn (Fasting)</th>
                            <th>Checklist WHO</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surgeries as $s)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $s->surgery_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $s->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $s->patientProfile->patient_code }} | {{ $s->patientProfile->gender_name }} | {{ $s->patientProfile->age_text }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $s->procedure_name }}">{{ $s->procedure_name }}</div>
                                </td>
                                <td class="small">
                                    <div class="fw-semibold text-dark"><i class="bi bi-person-fill text-primary me-1"></i>{{ $s->surgeon?->user?->name ?? 'Bác sĩ Ngoại' }}</div>
                                </td>
                                <td class="small">
                                    <div class="fw-bold">{{ $s->scheduled_at->format('H:i d/m/Y') }}</div>
                                    <span class="badge bg-light text-dark border">{{ $s->operating_room }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-cup-straw me-1"></i>Nhịn {{ $s->fasting_hours }}h</span>
                                </td>
                                <td>
                                    @if($s->checklist_passed)
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Đạt Chuẩn WHO</span>
                                    @else
                                        <span class="badge bg-secondary">Chưa Kiểm Tra</span>
                                    @endif
                                </td>
                                <td>{!! $s->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#checklistModal{{ $s->id }}" title="WHO Checklist">
                                            <i class="bi bi-clipboard2-check"></i> Checklist
                                        </button>
                                        @if($s->status !== 'discharged' && $s->status !== 'cancelled')
                                            <form action="{{ route('reception.day-surgeries.status', $s) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                    <option value="scheduled" {{ $s->status === 'scheduled' ? 'selected' : '' }}>Đã hẹn</option>
                                                    <option value="in_surgery" {{ $s->status === 'in_surgery' ? 'selected' : '' }}>Đang mổ</option>
                                                    <option value="recovery" {{ $s->status === 'recovery' ? 'selected' : '' }}>Hồi tỉnh</option>
                                                    <option value="discharged">Xuất viện</option>
                                                    <option value="cancelled">Hủy mổ</option>
                                                </select>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Modal Checklist An Toàn WHO -->
                                    <div class="modal fade text-start" id="checklistModal{{ $s->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('reception.day-surgeries.checklist', $s) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-content">
                                                    <div class="modal-header bg-info text-dark">
                                                        <h5 class="modal-title"><i class="bi bi-clipboard2-check me-2"></i>Bảng Kiểm An Toàn Tiền Phẫu WHO (Bước Tiếp Nhận)</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="alert alert-light border small mb-3">
                                                            Bệnh nhân: <strong>{{ $s->patientProfile->full_name }}</strong> | Phẫu thuật: <strong>{{ $s->procedure_name }}</strong>
                                                        </div>

                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="c1{{ $s->id }}" checked required>
                                                            <label class="form-check-label small" for="c1{{ $s->id }}">Đã xác nhận danh tính, vị trí phẫu thuật & thủ thuật dự kiến</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="c2{{ $s->id }}" checked required>
                                                            <label class="form-check-label small" for="c2{{ $s->id }}">Đã nhịn ăn/uống đủ số giờ quy định (≥ {{ $s->fasting_hours }} tiếng)</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="c3{{ $s->id }}" checked required>
                                                            <label class="form-check-label small" for="c3{{ $s->id }}">Đã tháo răng giả, kính áp tròng, đồ trang sức kim loại</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="c4{{ $s->id }}" checked required>
                                                            <label class="form-check-label small" for="c4{{ $s->id }}">Đã ký Bản cam đoan chấp thuận phẫu thuật ngoại trú</label>
                                                        </div>

                                                        <input type="hidden" name="checklist_passed" value="1">

                                                        <div class="mt-3">
                                                            <label class="form-label fw-semibold">Ghi chú tiền phẫu</label>
                                                            <input type="text" name="pre_op_notes" value="{{ $s->pre_op_notes ?: 'Tất cả tiêu chí an toàn tiền phẫu đạt chuẩn WHO.' }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-info fw-bold">Xác Nhận Đạt Chuẩn Vào Mổ</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    Chưa có ca phẫu thuật trong ngày nào trong danh sách.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($surgeries->hasPages())
                <div class="p-3 border-top">{{ $surgeries->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Đặt Lịch Mổ Mới -->
<div class="modal fade" id="newSurgeryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.day-surgeries.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Đặt Lịch Phẫu Thuật Trong Ngày</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh phẫu thuật <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->full_name }} (Mã: {{ $pt->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên thủ thuật / Phẫu thuật ngoại trú <span class="text-danger">*</span></label>
                        <input type="text" name="procedure_name" class="form-control" placeholder="VD: Phẫu thuật Phaco đục thủy tinh thể / Tán sỏi thận qua da..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bác sĩ phẫu thuật chính <span class="text-danger">*</span></label>
                            <select name="surgeon_doctor_id" class="form-select" required>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}">{{ $d->user?->name ?? 'Bác sĩ' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phòng mổ chỉ định <span class="text-danger">*</span></label>
                            <select name="operating_room" class="form-select" required>
                                <option value="Phòng Mổ Ngoại Trú OR-01">Phòng Mổ OR-01 (Mắt/Phaco)</option>
                                <option value="Phòng Mổ Ngoại Trú OR-02">Phòng Mổ OR-02 (Tán sỏi/Tiết niệu)</option>
                                <option value="Phòng Can Thiệp Nội Soi OR-03">Phòng Can Thiệp OR-03 (Nội soi)</option>
                                <option value="Phòng Tiểu Phẫu Sạch OR-04">Phòng Tiểu Phẫu Sạch OR-04</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thời gian phẫu thuật <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_at" value="{{ date('Y-m-d\T08:00', strtotime('+1 day')) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số giờ yêu cầu nhịn ăn (Fasting) <span class="text-danger">*</span></label>
                            <input type="number" name="fasting_hours" value="8" min="4" max="24" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">Xác Nhận Lên Lịch</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
