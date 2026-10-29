@extends('layouts.admin')

@section('title', 'Phòng Mổ Thông Minh & Hồi Tỉnh PACU')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-hospital text-primary me-2"></i>Phòng Mổ Thông Minh & Hồi Tỉnh PACU (Smart OR)
            </h4>
            <p class="text-muted small mb-0">
                Quản lý kíp mổ 7 bước, kiểm đếm gạc chống dị vật JCI, thang điểm hồi tỉnh Aldrete & Cổng theo dõi thân nhân
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('public.or-waiting-board') }}" target="_blank" class="btn btn-outline-info rounded-pill px-3 shadow-sm btn-sm">
                <i class="bi bi-tv me-1"></i> Mở Bảng Sảnh Chờ Thân Nhân
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#newSurgeryModal">
                <i class="bi bi-plus-circle me-1"></i> Đặt Lịch Ca Mổ Mới
            </button>
        </div>
    </div>

    <!-- Operating Rooms Status Cards -->
    <div class="row g-3 mb-4">
        @foreach($rooms as $r)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 {{ $r->status === 'in_use' ? 'border-start border-4 border-danger' : ($r->status === 'cleaning' ? 'border-start border-4 border-warning' : 'border-start border-4 border-success') }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $r->status === 'in_use' ? 'bg-danger' : ($r->status === 'cleaning' ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                            {{ $r->status === 'in_use' ? 'ĐANG PHẪU THUẬT' : ($r->status === 'cleaning' ? 'ĐANG KHỬ KHUẨN' : 'SẴN SÀNG') }}
                        </span>
                        <small class="text-muted fw-bold">{{ $r->room_number }}</small>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $r->room_name }}</h6>
                    <div class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $r->zone }}</div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                        <span class="text-secondary"><i class="bi bi-shield-check text-info me-1"></i>Chuẩn {{ $r->cleanliness_class }}</span>
                        <span class="fw-semibold text-dark">{{ $r->surgical_cases_count ?? 0 }} ca hôm nay</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Table of Surgical Cases -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-task text-primary me-2"></i>Danh Sách Ca Phẫu Thuật Trong Ngày</h6>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <select name="phase" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">Tất cả giai đoạn</option>
                        <option value="scheduled" {{ request('phase') == 'scheduled' ? 'selected' : '' }}>Chờ mổ (Scheduled)</option>
                        <option value="surgery" {{ request('phase') == 'surgery' ? 'selected' : '' }}>Đang phẫu thuật</option>
                        <option value="pacu" {{ request('phase') == 'pacu' ? 'selected' : '' }}>Hồi tỉnh PACU</option>
                        <option value="discharged" {{ request('phase') == 'discharged' ? 'selected' : '' }}>Đã xuất viện / Về trại</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th>Mã Ca / Mức Độ</th>
                        <th>Bệnh Nhân</th>
                        <th>Phòng / Kíp Mổ</th>
                        <th>Phẫu Thuật Chỉ Định</th>
                        <th>Tiến Trình (Phase)</th>
                        <th>Kiểm Đếm Gạc</th>
                        <th>Điểm PACU</th>
                        <th class="text-end pe-3">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($cases as $c)
                    <tr>
                        <td>
                            <strong class="text-dark d-block">{{ $c->case_code }}</strong>
                            <span class="badge {{ $c->priority === 'emergency' ? 'bg-danger' : ($c->priority === 'urgent' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill" style="font-size: 0.65rem;">
                                {{ strtoupper($c->priority) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $c->patient->full_name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $c->patient->patient_code ?? '' }} • {{ $c->patient->age ?? '' }}t</small>
                        </td>
                        <td>
                            <div class="text-dark fw-medium">{{ $c->room->room_number ?? 'Chưa gán' }}</div>
                            <small class="text-muted">PTV: {{ $c->leadSurgeon->user->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="text-dark fw-medium">{{ $c->surgical_procedure }}</span>
                            @if($c->icd10_code)
                                <small class="text-primary d-block font-monospace">ICD: {{ $c->icd10_code }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($c->phase) {
                                    'scheduled' => 'bg-secondary',
                                    'pre_op' => 'bg-info text-dark',
                                    'anesthesia' => 'bg-warning text-dark',
                                    'surgery' => 'bg-danger',
                                    'hemostasis' => 'bg-primary',
                                    'pacu' => 'bg-warning text-dark',
                                    'discharged' => 'bg-success',
                                    default => 'bg-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-2 py-1">
                                {{ strtoupper($c->phase) }}
                            </span>
                        </td>
                        <td>
                            @if($c->spongeCounts->count() > 0)
                                @php $lastCount = $c->spongeCounts->last(); @endphp
                                @if($lastCount->is_matched)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>Khớp 100%
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                        <i class="bi bi-exclamation-triangle me-1"></i>LỆCH GẠC!
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">Chưa kiểm</span>
                            @endif
                        </td>
                        <td>
                            @if($c->aldreteScores->count() > 0)
                                @php $lastAldrete = $c->aldreteScores->last(); @endphp
                                <span class="badge {{ $lastAldrete->is_eligible_for_discharge ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                    {{ $lastAldrete->total_score }}/10 đ
                                </span>
                            @else
                                <span class="text-muted">Chưa chấm</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Xử trí
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><h6 class="dropdown-header small">Chuyển bước ca mổ</h6></li>
                                    <li>
                                        <form method="POST" action="{{ route('reception.operating-room.update-phase', $c->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="phase" value="surgery">
                                            <button type="submit" class="dropdown-item small text-danger"><i class="bi bi-play-circle me-2"></i>Bắt đầu phẫu thuật</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('reception.operating-room.update-phase', $c->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="phase" value="pacu">
                                            <button type="submit" class="dropdown-item small text-warning"><i class="bi bi-heart-pulse me-2"></i>Chuyển Hồi tỉnh PACU</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('reception.operating-room.update-phase', $c->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="phase" value="discharged">
                                            <button type="submit" class="dropdown-item small text-success"><i class="bi bi-check-all me-2"></i>Xuất phòng PACU về khoa</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item small" data-bs-toggle="modal" data-bs-target="#spongeModal{{ $c->id }}">
                                            <i class="bi bi-clipboard-check me-2"></i>Kiểm đếm gạc JCI
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item small" data-bs-toggle="modal" data-bs-target="#aldreteModal{{ $c->id }}">
                                            <i class="bi bi-award me-2"></i>Chấm điểm Aldrete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Đếm Gạc Cho Từng Ca -->
                    <div class="modal fade" id="spongeModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-3">
                                <form method="POST" action="{{ route('reception.operating-room.sponge-count', $c->id) }}">
                                    @csrf
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i>Kiểm Đếm Gạc & Dụng Cụ Mổ - {{ $c->case_code }}</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-3">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Vật tư / Dụng cụ kiểm đếm</label>
                                            <input type="text" name="item_name" class="form-control form-control-sm" value="Gạc phẫu thuật cản quang 10x10" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small">Số lượng mở bụng (Initial)</label>
                                                <input type="number" name="initial_count" class="form-control form-control-sm" value="10" required min="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Số lượng thêm lúc mổ (Added)</label>
                                                <input type="number" name="added_count" class="form-control form-control-sm" value="0" required min="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Đếm trước đóng bụng (Closure)</label>
                                                <input type="number" name="before_closure_count" class="form-control form-control-sm" value="10" required min="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Đếm kết thúc ca (Final)</label>
                                                <input type="number" name="final_count" class="form-control form-control-sm" value="10" required min="0">
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small">ĐD vòng trong (Scrub)</label>
                                                <input type="text" name="scrub_nurse_name" class="form-control form-control-sm" value="ĐD. Trần Thị Mai" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">ĐD vòng ngoài (Circulating)</label>
                                                <input type="text" name="circulating_nurse_name" class="form-control form-control-sm" value="ĐD. Lê Văn Phúc" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light p-2">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Lưu Biên Bản Đếm</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Chấm Điểm Aldrete -->
                    <div class="modal fade" id="aldreteModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-3">
                                <form method="POST" action="{{ route('reception.operating-room.aldrete-score', $c->id) }}">
                                    @csrf
                                    <div class="modal-header bg-warning text-dark">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-award me-2"></i>Thang Điểm Hồi Tỉnh Aldrete (PACU) - {{ $c->case_code }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-3 small">
                                        <div class="row g-2">
                                            <div class="col-12 mb-2">
                                                <label class="form-label fw-semibold">1. Hô hấp (Respiration)</label>
                                                <select name="respiration_score" class="form-select form-select-sm" required>
                                                    <option value="2">2 đ - Thở sâu, ho tốt, nhịp thở đều</option>
                                                    <option value="1">1 đ - Thở nông, khó thở nhẹ</option>
                                                    <option value="0">0 đ - Ngừng thở hoặc cần đặt ống thở máy</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label fw-semibold">2. Tuần hoàn (Circulation - Huyết áp)</label>
                                                <select name="circulation_score" class="form-select form-select-sm" required>
                                                    <option value="2">2 đ - Huyết áp chênh lệch < 20% so với trước mổ</option>
                                                    <option value="1">1 đ - Huyết áp chênh lệch 20% - 50%</option>
                                                    <option value="0">0 đ - Huyết áp chênh lệch > 50%</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label fw-semibold">3. Tri giác (Consciousness)</label>
                                                <select name="consciousness_score" class="form-select form-select-sm" required>
                                                    <option value="2">2 đ - Tỉnh hoàn toàn, trả lời đúng</option>
                                                    <option value="1">1 đ - Gọi mở mắt, đáp ứng chậm</option>
                                                    <option value="0">0 đ - Không đáp ứng kích thích</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label fw-semibold">4. Vận động (Activity)</label>
                                                <select name="activity_score" class="form-select form-select-sm" required>
                                                    <option value="2">2 đ - Cử động tự chủ cả 4 chi</option>
                                                    <option value="1">1 đ - Cử động được 2 chi</option>
                                                    <option value="0">0 đ - Không cử động được chi nào</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label fw-semibold">5. Màu da / SpO2</label>
                                                <select name="color_score" class="form-select form-select-sm" required>
                                                    <option value="2">2 đ - Hồng hào, SpO2 > 92% khí trời</option>
                                                    <option value="1">1 đ - Nhợt, cần thở oxy duy trì SpO2 > 90%</option>
                                                    <option value="0">0 đ - Tím tái, SpO2 < 90% dù có thở oxy</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Bác sĩ / Điều dưỡng đánh giá</label>
                                                <input type="text" name="evaluated_by" class="form-control form-control-sm" value="BS. Gây Mê PACU" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light p-2">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Đóng</button>
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3">Xác Nhận Chấm Điểm</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Chưa có ca phẫu thuật nào trong ngày hôm nay.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-2">
            {{ $cases->links() }}
        </div>
    </div>
</div>

<!-- Modal Đặt Lịch Ca Mổ Mới -->
<div class="modal fade" id="newSurgeryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <form method="POST" action="{{ route('reception.operating-room.store-case') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>Lên Lịch Ca Phẫu Thuật Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 small">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select form-select-sm" required>
                                <option value="">-- Chọn người bệnh --</option>
                                @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phòng Mổ <span class="text-danger">*</span></label>
                            <select name="room_id" class="form-select form-select-sm" required>
                                <option value="">-- Chọn phòng mổ --</option>
                                @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->room_number }} - {{ $r->room_name }} ({{ $r->cleanliness_class }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phẫu Thuật Viên Chính <span class="text-danger">*</span></label>
                            <select name="lead_surgeon_id" class="form-select form-select-sm" required>
                                <option value="">-- Chọn bác sĩ mổ chính --</option>
                                @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->user->name ?? 'Bác sĩ' }} ({{ $d->specialty->name ?? 'Khoa Ngoại' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bác Sĩ Gây Mê Hồi Sức</label>
                            <select name="anesthesiologist_id" class="form-select form-select-sm">
                                <option value="">-- Chọn BS gây mê --</option>
                                @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->user->name ?? 'Bác sĩ' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Tên Phẫu Thuật / Thủ Thuật <span class="text-danger">*</span></label>
                            <input type="text" name="surgical_procedure" class="form-control form-control-sm" placeholder="VD: Phẫu thuật nội soi cắt ruột thừa viêm cấp" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã ICD-10</label>
                            <input type="text" name="icd10_code" class="form-control form-control-sm" placeholder="VD: K35.8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mức Độ Ưu Tiên <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select form-select-sm" required>
                                <option value="elective">Mổ chương trình (Elective)</option>
                                <option value="urgent">Mổ bán khẩn (Urgent)</option>
                                <option value="emergency">Mổ cấp cứu khẩn cấp (Emergency)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thời Gian Dự Kiến Mổ <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_start" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi Chú Phẫu Thuật</label>
                            <textarea name="surgical_notes" class="form-control form-control-sm" rows="2" placeholder="Chuẩn bị máu, dị ứng thuốc mê, tư thế bệnh nhân..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Hủy Bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Lưu Lịch Mổ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
