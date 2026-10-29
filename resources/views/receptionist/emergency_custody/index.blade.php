@extends('layouts.admin')

@section('title', 'Quản Lý Niêm Phong Tài Sản Cấp Cứu & Đồ Thất Lạc')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-safe2-fill me-2"></i>Niêm Phong Tài Sản Cấp Cứu & Quản Lý Đồ Thất Lạc
            </h4>
            <p class="text-muted small mb-0">Bảo vệ tư trang người bệnh cấp cứu hôn mê & Tra cứu đồ nhặt được tại sảnh - Bệnh Viện Tâm Anh</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newCustodyModal">
                <i class="bi bi-lock-fill"></i> Niêm Phong Tài Sản Cấp Cứu
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newLostModal">
                <i class="bi bi-box-seam"></i> Ghi Nhận Đồ Thất Lạc
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tài Sản Đang Niêm Phong</div>
                            <div class="fs-4 fw-bold text-danger">{{ $stats['sealed_custody'] }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đã Bàn Giao Người Nhà</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['returned_custody'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đồ Thất Lạc Chưa Nhận</div>
                            <div class="fs-4 fw-bold text-warning">{{ $stats['unclaimed_items'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-search fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đồ Thất Lạc Đã Trả Khách</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['claimed_items'] }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-hand-thumbs-up-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs border-bottom mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'custody' ? 'active fw-bold text-primary' : 'text-secondary' }}" href="{{ route('reception.emergency-custody.index', ['tab' => 'custody']) }}">
                <i class="bi bi-lock-fill me-1"></i> Niêm Phong Tài Sản Cấp Cứu ({{ $custodies->total() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'lost' ? 'active fw-bold text-primary' : 'text-secondary' }}" href="{{ route('reception.emergency-custody.index', ['tab' => 'lost']) }}">
                <i class="bi bi-box-seam me-1"></i> Quản Lý Đồ Thất Lạc Sảnh ({{ $lostFounds->total() }})
            </a>
        </li>
    </ul>

    @if($tab === 'custody')
        <!-- Tab 1: Tài Sản Cấp Cứu -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Mã Niêm Phong</th>
                                <th>Bệnh Nhân Cấp Cứu</th>
                                <th>Số Túi Niêm Phong</th>
                                <th>Chi Tiết Tài Sản & Tiền Mặt</th>
                                <th>Ký Kép Bàn Giao</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($custodies as $c)
                                <tr>
                                    <td class="ps-3 fw-bold text-danger font-monospace">{{ $c->custody_code }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $c->patientProfile->full_name }}</div>
                                        <small class="text-muted">{{ $c->patientProfile->patient_code }} | {{ $c->patientProfile->phone }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark font-monospace"><i class="bi bi-tag-fill me-1"></i>{{ $c->bag_seal_number }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-truncate" style="max-width: 250px;" title="{{ $c->item_description }}">{{ $c->item_description }}</div>
                                        @if($c->cash_amount > 0)
                                            <span class="badge bg-success font-monospace">+{{ number_format($c->cash_amount, 0, ',', '.') }} đ</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        <div><i class="bi bi-person-heart text-danger me-1"></i>ĐD: {{ $c->nurse?->name }}</div>
                                        <div><i class="bi bi-shield-shaded text-primary me-1"></i>AN: {{ $c->security?->name }}</div>
                                    </td>
                                    <td>{!! $c->status_badge !!}</td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('reception.emergency-custody.print', $c) }}" target="_blank" class="btn btn-outline-secondary" title="In biên bản A5">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            @if($c->status === 'sealed')
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#returnModal{{ $c->id }}" title="Bàn giao trả">
                                                    <i class="bi bi-box-arrow-right"></i> Trả tài sản
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Return Modal -->
                                        @if($c->status === 'sealed')
                                            <div class="modal fade text-start" id="returnModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('reception.emergency-custody.return', $c) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i>Bàn Giao Trả Tài Sản Cấp Cứu</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="alert alert-info py-2 small mb-3">
                                                                    Túi niêm phong: <strong>{{ $c->bag_seal_number }}</strong> - Bệnh nhân: <strong>{{ $c->patientProfile->full_name }}</strong>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Họ tên người nhận lại <span class="text-danger">*</span></label>
                                                                    <input type="text" name="return_recipient_name" class="form-control" placeholder="VD: Trần Văn B (Người nhà)..." required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Số CCCD người nhận <span class="text-danger">*</span></label>
                                                                    <input type="text" name="return_recipient_id_card" class="form-control" placeholder="VD: 001092003344" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Mối quan hệ với bệnh nhân <span class="text-danger">*</span></label>
                                                                    <input type="text" name="return_relationship" class="form-control" placeholder="VD: Bệnh nhân đã tỉnh lại / Vợ / Con..." required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                                <button type="submit" class="btn btn-success fw-bold">Xác Nhận Bàn Giao</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Chưa có tài sản cấp cứu nào được niêm phong.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($custodies->hasPages())
                    <div class="p-3 border-top">{{ $custodies->links() }}</div>
                @endif
            </div>
        </div>
    @else
        <!-- Tab 2: Đồ Thất Lạc -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Mã Đồ Thất Lạc</th>
                                <th>Tên Tài Sản Nhặt Được</th>
                                <th>Vị Trí Nhặt Được</th>
                                <th>Thời Gian Nhặt</th>
                                <th>Mô Tả Đặc Điểm</th>
                                <th>Trạng Thái</th>
                                <th class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lostFounds as $item)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary font-monospace">{{ $item->item_code }}</td>
                                    <td class="fw-semibold text-dark">{{ $item->item_name }}</td>
                                    <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $item->found_location }}</td>
                                    <td class="small">{{ $item->found_at->format('H:i d/m/Y') }}</td>
                                    <td class="small text-muted">{{ $item->description ?: '---' }}</td>
                                    <td>{!! $item->status_badge !!}</td>
                                    <td class="text-end pe-3">
                                        @if($item->status === 'unclaimed')
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#claimModal{{ $item->id }}">
                                                <i class="bi bi-check2-circle me-1"></i> Trả Đồ Cho Khách
                                            </button>

                                            <!-- Claim Modal -->
                                            <div class="modal fade text-start" id="claimModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('reception.lost-found.claim', $item) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title"><i class="bi bi-check2-circle me-2"></i>Bàn Giao Đồ Thất Lạc Cho Khách</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                                                                    <input type="text" name="claimant_name" class="form-control" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                                                    <input type="text" name="claimant_phone" class="form-control" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Số CCCD / CMND <span class="text-danger">*</span></label>
                                                                    <input type="text" name="claimant_id_card" class="form-control" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                                <button type="submit" class="btn btn-success fw-bold">Xác Nhận Bàn Giao</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted">Đã nhận bởi: <strong>{{ $item->claimant_name }}</strong> lúc {{ $item->claimed_at?->format('H:i d/m/Y') }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Chưa có đồ thất lạc nào trong danh mục.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lostFounds->hasPages())
                    <div class="p-3 border-top">{{ $lostFounds->links() }}</div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Modal Niêm Phong Tài Sản Mới -->
<div class="modal fade" id="newCustodyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.emergency-custody.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-lock-fill me-2"></i>Niêm Phong Tài Sản Cấp Cứu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bệnh nhân cấp cứu <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn bệnh nhân cấp cứu --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }} (Mã: {{ $p->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã niêm phong túi chuyên dụng <span class="text-danger">*</span></label>
                        <input type="text" name="bag_seal_number" class="form-control font-monospace" placeholder="VD: SEAL-2026-9988" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả chi tiết tài sản (Tiền, điện thoại, nhẫn, laptop...) <span class="text-danger">*</span></label>
                        <textarea name="item_description" class="form-control" rows="3" placeholder="Ghi rõ chi tiết từng món đồ bàn giao..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiền mặt kiểm đếm (nếu có)</label>
                        <input type="number" name="cash_amount" value="0" min="0" step="10000" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nhân viên an ninh cùng ký xác nhận <span class="text-danger">*</span></label>
                        <select name="security_user_id" class="form-select" required>
                            <option value="">-- Chọn nhân viên an ninh trực --</option>
                            @foreach($staffUsers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->role_name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger fw-bold">Ký Niêm Phong Túi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ghi Nhận Đồ Thất Lạc Mới -->
<div class="modal fade" id="newLostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.lost-found.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Ghi Nhận Đồ Thất Lạc Tại Sảnh</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên tài sản nhặt được <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" placeholder="VD: Ví da màu nâu, Chìa khóa xe Honda, Áo khoác..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Vị trí nhặt được <span class="text-danger">*</span></label>
                        <input type="text" name="found_location" class="form-control" placeholder="VD: Ghế chờ sảnh A, Quầy Kiosk số 2, Căng tin..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thời gian nhặt được <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="found_at" value="{{ date('Y-m-d\TH:i') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả đặc điểm nhận dạng</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Chi tiết màu sắc, nhãn hiệu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold">Lưu Đồ Thất Lạc</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
