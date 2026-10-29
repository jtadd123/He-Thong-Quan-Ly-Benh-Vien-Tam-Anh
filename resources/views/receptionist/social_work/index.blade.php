@extends('layouts.admin')

@section('title', 'Công Tác Xã Hội & Thông Dịch Viên Y Khoa')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-heart-pulse-fill me-2"></i>Công Tác Xã Hội & Thông Dịch Viên Y Khoa
            </h4>
            <p class="text-muted small mb-0">Hỗ trợ bệnh nhân khó khăn từ Quỹ Từ thiện Tâm Anh Heart & Điều phối phiên dịch đa ngôn ngữ</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newSocialModal">
                <i class="bi bi-gift-fill"></i> Đề Xuất Trợ Cấp Viện Phí
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newInterModal">
                <i class="bi bi-translate"></i> Điều Động Thông Dịch Viên
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Ca Được Trợ Cấp</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['total_social_cases'] }} ca</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-heart-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Tiền Đã Hỗ Trợ</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($stats['total_aid_granted'], 0, ',', '.') }} đ</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-cash-heart fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Thông Dịch Đang Hỗ Trợ</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['active_interpreters'] }} ca</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-translate fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-secondary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Ca Phiên Dịch</div>
                            <div class="fs-4 fw-bold text-secondary">{{ $stats['total_interpreters'] }}</div>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-chat-quote fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs border-bottom mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'social' ? 'active fw-bold text-primary' : 'text-secondary' }}" href="{{ route('reception.social-work.index', ['tab' => 'social']) }}">
                <i class="bi bi-heart-fill text-danger me-1"></i> Hồ Sơ Miễn Giảm Viện Phí Từ Thiện ({{ $socialCases->total() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'interpreter' ? 'active fw-bold text-primary' : 'text-secondary' }}" href="{{ route('reception.social-work.index', ['tab' => 'interpreter']) }}">
                <i class="bi bi-translate text-primary me-1"></i> Thông Dịch Viên Y Khoa & Ngôn Ngữ Ký Hiệu ({{ $interpreters->total() }})
            </a>
        </li>
    </ul>

    @if($tab === 'social')
        <!-- Tab 1: Công Tác Xã Hội -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Mã Hồ Sơ</th>
                                <th>Người Bệnh Khó Khăn</th>
                                <th>Diện Hoàn Cảnh</th>
                                <th>Nguồn Quỹ Tài Trợ</th>
                                <th>Mức Đề Xuất</th>
                                <th>Mức Duyệt Hỗ Trợ</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($socialCases as $sc)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary font-monospace">{{ $sc->case_code }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $sc->patientProfile->full_name }}</div>
                                        <small class="text-muted">{{ $sc->patientProfile->patient_code }} | SĐT: {{ $sc->patientProfile->phone }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $sc->difficulty_type }}</span></td>
                                    <td class="small fw-semibold text-secondary">{{ $sc->funding_source }}</td>
                                    <td class="fw-semibold">{{ number_format($sc->proposed_discount_amount, 0, ',', '.') }} đ</td>
                                    <td class="fw-bold text-success">{{ number_format($sc->approved_discount_amount, 0, ',', '.') }} đ</td>
                                    <td>{!! $sc->status_badge !!}</td>
                                    <td class="text-end pe-3">
                                        @if($sc->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $sc->id }}">
                                                <i class="bi bi-check2-circle"></i> Duyệt Hỗ Trợ
                                            </button>

                                            <!-- Modal Duyệt -->
                                            <div class="modal fade text-start" id="approveModal{{ $sc->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('reception.social-work.approve', $sc) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title">Xét Duyệt Trợ Cấp Viện Phí</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Quyết định xét duyệt</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="approved" selected>Chấp thuận hỗ trợ (Approved)</option>
                                                                        <option value="rejected">Không đạt điều kiện hỗ trợ (Rejected)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Số tiền phê duyệt hỗ trợ (VNĐ)</label>
                                                                    <input type="number" name="approved_discount_amount" value="{{ $sc->proposed_discount_amount }}" min="0" step="50000" class="form-control fw-bold text-success" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                <button type="submit" class="btn btn-success fw-bold">Xác Nhận Phê Duyệt</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted">Đã duyệt bởi: <strong>{{ $sc->approvedBy?->name ?? 'Lãnh đạo Quỹ' }}</strong></small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Chưa có hồ sơ xin trợ cấp công tác xã hội nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($socialCases->hasPages())
                    <div class="p-3 border-top">{{ $socialCases->links() }}</div>
                @endif
            </div>
        </div>
    @else
        <!-- Tab 2: Thông Dịch Viên Y Khoa -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Mã Yêu Cầu</th>
                                <th>Người Bệnh Cần Phiên Dịch</th>
                                <th>Ngôn Ngữ Yêu Cầu</th>
                                <th>Thông Dịch Viên Phụ Trách</th>
                                <th>Số Điện Thoại</th>
                                <th>Thời Điểm Tiếp Nhận</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($interpreters as $int)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary font-monospace">{{ $int->request_code }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $int->patientProfile->full_name }}</div>
                                        <small class="text-muted">{{ $int->patientProfile->patient_code }} | QG/Quốc tịch: Ngoại kiều</small>
                                    </td>
                                    <td><span class="badge bg-primary fs-6">{{ $int->language }}</span></td>
                                    <td class="fw-semibold text-dark"><i class="bi bi-person-fill text-primary me-1"></i>{{ $int->interpreter_name }}</td>
                                    <td>{{ $int->interpreter_phone ?: 'Nội bộ' }}</td>
                                    <td class="small">{{ $int->created_at->format('H:i d/m/Y') }}</td>
                                    <td>{!! $int->status_badge !!}</td>
                                    <td class="text-end pe-3">
                                        @if($int->status === 'in_progress')
                                            <form action="{{ route('reception.interpreters.complete', $int) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check-lg me-1"></i> Hoàn Thành
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Chưa có yêu cầu thông dịch viên y khoa nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($interpreters->hasPages())
                    <div class="p-3 border-top">{{ $interpreters->links() }}</div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Modal Đề Xuất Trợ Cấp Mới -->
<div class="modal fade" id="newSocialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.social-work.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-gift-fill me-2"></i>Đề Xuất Hỗ Trợ Viện Phí Từ Thiện</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh cần hỗ trợ <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->full_name }} (Mã: {{ $pt->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Diện hoàn cảnh khó khăn <span class="text-danger">*</span></label>
                        <select name="difficulty_type" class="form-select" required>
                            <option value="Hộ nghèo / Cận nghèo có chứng nhận">Hộ nghèo / Cận nghèo có chứng nhận</option>
                            <option value="Người già neo đơn không nơi nương tựa">Người già neo đơn không nơi nương tựa</option>
                            <option value="Trẻ em mồ côi / Bệnh hiểm nghèo">Trẻ em mồ côi / Bệnh hiểm nghèo</option>
                            <option value="Tai nạn giao thông vô thừa nhận">Tai nạn giao thông vô thừa nhận</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nguồn quỹ tài trợ <span class="text-danger">*</span></label>
                        <select name="funding_source" class="form-select" required>
                            <option value="Quỹ Từ Thiện Tâm Anh Heart">Quỹ Từ Thiện Tâm Anh Heart</option>
                            <option value="Quỹ Trợ Cấp Xã Hội Bệnh Viện">Quỹ Trợ Cấp Xã Hội Bệnh Viện</option>
                            <option value="Nhà Hảo Tâm & Mạnh Thường Quân Đồng Hành">Nhà Hảo Tâm & Mạnh Thường Quân</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số tiền đề xuất hỗ trợ (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="proposed_discount_amount" value="5000000" min="100000" step="100000" class="form-control fw-bold text-success" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đánh giá hoàn cảnh người bệnh <span class="text-danger">*</span></label>
                        <textarea name="situation_assessment" class="form-control" rows="3" placeholder="Ghi nhận biên bản khảo sát thực tế hoàn cảnh khó khăn của người bệnh..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success fw-bold">Nộp Hồ Sơ Đề Xuất</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Điều Động Thông Dịch Mới -->
<div class="modal fade" id="newInterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.interpreters.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-translate me-2"></i>Điều Động Thông Dịch Viên Y Khoa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh nhân cần phiên dịch <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân ngoại kiều --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->full_name }} (Mã: {{ $pt->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ngôn ngữ yêu cầu <span class="text-danger">*</span></label>
                        <select name="language" class="form-select" required>
                            <option value="Tiếng Anh (English)">Tiếng Anh (English)</option>
                            <option value="Tiếng Hàn (Korean)">Tiếng Hàn (Korean)</option>
                            <option value="Tiếng Trung (Chinese)">Tiếng Trung (Chinese)</option>
                            <option value="Tiếng Nhật (Japanese)">Tiếng Nhật (Japanese)</option>
                            <option value="Ngôn Ngữ Ký Hiệu (Khiếm Thính)">Ngôn Ngữ Ký Hiệu (Khiếm Thính)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên phiên dịch viên phụ trách <span class="text-danger">*</span></label>
                            <input type="text" name="interpreter_name" class="form-control" placeholder="VD: Lê Thu Thảo (CSKH VIP)" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại liên hệ</label>
                            <input type="text" name="interpreter_phone" class="form-control" placeholder="VD: 0988112233">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú phòng khám / Khoa yêu cầu</label>
                        <input type="text" name="notes" class="form-control" placeholder="VD: Phòng khám Nội 102...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">Điều Động Phiên Dịch</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
