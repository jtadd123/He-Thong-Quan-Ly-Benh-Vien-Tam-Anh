@extends('layouts.admin')

@section('title', 'Danh Sách Đoàn Khám: ' . $contract->company_name)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.corporate-screening.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách hợp đồng
            </a>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-buildings me-2"></i>Đoàn Khám: {{ $contract->company_name }}
            </h4>
            <p class="text-muted small mb-0">Mã HĐ: <strong>{{ $contract->contract_code }}</strong> | Gói khám: <strong>{{ $contract->package_name }}</strong></p>
        </div>
        <div>
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newEmpModal">
                <i class="bi bi-person-plus-fill"></i> Tiếp Nhận Nhân Viên Mới
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-people-fill text-primary me-2"></i>Danh Sách Cán Bộ Nhân Viên ({{ $contract->employees->count() }} người)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Nhân Viên</th>
                            <th>Họ Và Tên</th>
                            <th>Phòng Ban</th>
                            <th>Ngày Sinh / Tuổi</th>
                            <th>Giới Tính</th>
                            <th>Số Điện Thoại</th>
                            <th>Trạng Thái Khám</th>
                            <th class="text-end pe-3">Phân Loại Sức Khỏe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $emp->employee_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $emp->full_name }}</div>
                                    @if($emp->patientProfile)
                                        <small class="text-muted">Mã BN: {{ $emp->patientProfile->patient_code }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $emp->department ?: 'Văn phòng' }}</span></td>
                                <td class="small">{{ $emp->dob->format('d/m/Y') }}</td>
                                <td>{{ $emp->gender === 'male' ? 'Nam' : 'Nữ' }}</td>
                                <td>{{ $emp->phone ?: '---' }}</td>
                                <td>{!! $emp->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#evalModal{{ $emp->id }}">
                                        <i class="bi bi-pencil-square me-1"></i> Kết Luận
                                    </button>

                                    <!-- Modal Đánh Giá Sức Khỏe -->
                                    <div class="modal fade text-start" id="evalModal{{ $emp->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="{{ route('reception.corporate-screening.screening', $emp) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Kết Luận Phân Loại Sức Khỏe</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Trạng thái khám</label>
                                                            <select name="screening_status" class="form-select" required>
                                                                <option value="in_progress" {{ $emp->screening_status === 'in_progress' ? 'selected' : '' }}>Đang thực hiện các chỉ định cận lâm sàng</option>
                                                                <option value="completed" {{ $emp->screening_status === 'completed' ? 'selected' : '' }}>Đã hoàn tất toàn bộ quy trình khám</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Xếp loại sức khỏe (Chuẩn TT 32/2023/TT-BYT) <span class="text-danger">*</span></label>
                                                            <select name="health_classification" class="form-select" required>
                                                                <option value="Loại I" {{ $emp->health_classification === 'Loại I' ? 'selected' : '' }}>Loại I (Rất khỏe mạnh)</option>
                                                                <option value="Loại II" {{ $emp->health_classification === 'Loại II' ? 'selected' : '' }}>Loại II (Khỏe mạnh bình thường)</option>
                                                                <option value="Loại III" {{ $emp->health_classification === 'Loại III' ? 'selected' : '' }}>Loại III (Trung bình / Bệnh mãn tính nhẹ)</option>
                                                                <option value="Loại IV" {{ $emp->health_classification === 'Loại IV' ? 'selected' : '' }}>Loại IV (Yếu / Cần can thiệp điều trị)</option>
                                                                <option value="Loại V" {{ $emp->health_classification === 'Loại V' ? 'selected' : '' }}>Loại V (Rất yếu / Không đủ sức khỏe lao động)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Kết luận của bác sĩ trưởng đoàn</label>
                                                            <textarea name="conclusion" class="form-control" rows="3" placeholder="Ghi nhận các bất thường lâm sàng cần theo dõi...">{{ $emp->conclusion }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-primary fw-bold">Lưu Kết Quả</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Chưa có nhân viên nào trong đoàn khám này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="p-3 border-top">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Thêm Nhân Viên -->
<div class="modal fade" id="newEmpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.corporate-screening.employees', $contract) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Tiếp Nhận Nhân Viên Vào Đoàn Khám</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã nhân viên (Staff ID) <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" class="form-control" placeholder="VD: VT-0988" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phòng ban</label>
                            <input type="text" name="department" class="form-control" placeholder="VD: Khối Kỹ Thuật...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên nhân viên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="VD: Hoàng Văn Nam" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="dob" value="1990-05-15" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giới tính <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0981234567">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success fw-bold">Tiếp Nhận Vào Đoàn</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
