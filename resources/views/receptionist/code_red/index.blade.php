@extends('layouts.admin')

@section('title', 'Trung Tâm Cấp Cứu & Báo Động Đỏ Toàn Viện')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-hospital-fill text-danger me-2"></i>Trung Tâm Cấp Cứu Trước Viện & Báo Động Đỏ Toàn Viện (Hospital Code Red)
            </h4>
            <p class="text-muted small mb-0">Tiếp nhận thông tin từ xe cứu thương 115 từ xa, đo lường Thời Gian Vàng (Door-to-Balloon / Door-to-Needle) theo chuẩn JCI.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger shadow-sm fw-bold" onclick="playCodeRedSiren()">
                <i class="bi bi-volume-up-fill me-1"></i> Kiểm Tra Còi Hú Cấp Cứu
            </button>
            <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-2 shadow-sm fw-bold animate-pulse" data-bs-toggle="modal" data-bs-target="#activateCodeRedModal">
                <i class="bi bi-exclamation-octagon-fill"></i> Kích Hoạt Báo Động Đỏ
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Active Code Red Emergencies Banner -->
    @if($activeAlerts->isNotEmpty())
        <div class="card border-0 shadow-lg mb-4 bg-danger text-white border-start border-white border-4 animate-pulse">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill fs-2 text-warning animate-bounce"></i>
                        <h4 class="fw-black mb-0 text-white tracking-wider">CẢNH BÁO: ĐANG CÓ {{ $activeAlerts->count() }} CA BÁO ĐỘNG ĐỎ KHẨN CẤP!</h4>
                    </div>
                    <span class="badge bg-white text-danger fs-6 px-3 py-2 fw-bold">TOÀN VIỆN ĐẶT TRONG TÌNH TRẠNG SẴN SÀNG</span>
                </div>

                <div class="row g-3">
                    @foreach($activeAlerts as $alert)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 bg-white text-dark shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-danger fs-6 mb-1">{{ $alert->emergency_type_name }}</span>
                                            <h5 class="fw-bold mb-0 text-slate-900">{{ $alert->patient_name }}</h5>
                                            <small class="text-muted">{{ $alert->gender === 'male' ? 'Nam' : ($alert->gender === 'female' ? 'Nữ' : 'Chưa rõ') }} • {{ $alert->estimated_age }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="font-monospace fw-bold text-danger">{{ $alert->code_red_number }}</span>
                                        </div>
                                    </div>

                                    <div class="p-2 bg-light rounded-3 mb-2 small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="bi bi-truck me-1"></i>Xe vận chuyển:</span>
                                            <strong>{{ $alert->ambulance_unit }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted"><i class="bi bi-telephone me-1"></i>Kíp cấp cứu:</span>
                                            <strong>{{ $alert->paramedic_in_charge }} ({{ $alert->paramedic_phone }})</strong>
                                        </div>
                                        @if($alert->in_transit_vitals)
                                            <div class="d-flex justify-content-between text-danger fw-semibold">
                                                <span>Sinh hiệu: HA {{ $alert->in_transit_vitals['blood_pressure'] ?? '--' }}</span>
                                                <span>Mạch {{ $alert->in_transit_vitals['pulse'] ?? '--' }} | SpO2 {{ $alert->in_transit_vitals['spo2'] ?? '--' }}%</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="alert alert-warning p-2 small mb-3 border-0">
                                        <strong>Tình trạng:</strong> {{ $alert->clinical_condition }}
                                    </div>

                                    <!-- Golden Hour Timers -->
                                    <div class="mb-3 p-2 bg-light rounded text-center">
                                        @if($alert->status === 'en_route')
                                            <span class="text-danger fw-bold d-block fs-5">
                                                <i class="bi bi-clock-history me-1"></i>ETA: Khoảng {{ $alert->eta_minutes }} phút tới cửa viện
                                            </span>
                                            <small class="text-muted">Phát lệnh lúc: {{ $alert->activated_at->format('H:i') }}</small>
                                        @elseif($alert->status === 'arrived')
                                            <span class="text-primary fw-bold d-block fs-6">
                                                <i class="bi bi-door-open-fill me-1"></i>Đã tới cửa lúc: {{ $alert->arrived_at?->format('H:i') }}
                                            </span>
                                            <small class="text-muted">Đang chuyển phòng can thiệp</small>
                                        @elseif($alert->status === 'in_intervention')
                                            <span class="text-success fw-bold d-block fs-6">
                                                <i class="bi bi-heart-pulse-fill me-1"></i>Bắt đầu can thiệp: {{ $alert->intervention_started_at?->format('H:i') }}
                                            </span>
                                            @if($alert->door_to_intervention_minutes !== null)
                                                <span class="badge bg-success-subtle text-success border border-success">
                                                    Door-to-Intervention: {{ $alert->door_to_intervention_minutes }} phút (Đạt chuẩn < 60p)
                                                </span>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Quick Action Buttons -->
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($alert->status === 'en_route')
                                            <form action="{{ route('reception.code-red.mark-arrived', $alert) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning w-100 fw-bold">
                                                    <i class="bi bi-box-arrow-in-down me-1"></i>Xe Đã Cập Cổng
                                                </button>
                                            </form>
                                        @elseif($alert->status === 'arrived')
                                            <form action="{{ route('reception.code-red.mark-intervention', $alert) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                                    <i class="bi bi-heart-pulse me-1"></i>Bắt Đầu Can Thiệp
                                                </button>
                                            </form>
                                        @elseif($alert->status === 'in_intervention')
                                            <button type="button" class="btn btn-sm btn-success w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#resolveModal-{{ $alert->id }}">
                                                <i class="bi bi-check2-circle me-1"></i>Hoàn Tất Xử Trí
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Resolve Modal -->
                                    <div class="modal fade" id="resolveModal-{{ $alert->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('reception.code-red.resolve', $alert) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title fw-bold">Hoàn Tất Xử Trí Ca Báo Động Đỏ</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Kết quả can thiệp / Tình trạng bệnh nhân:</label>
                                                            <textarea name="outcome_notes" class="form-control form-control-sm" rows="3" required>Đã can thiệp tái thông mạch thành công, sinh hiệu ổn định, chuyển về ICU theo dõi tích cực.</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-success btn-sm">Xác Nhận Giải Tỏa Báo Động</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Mobilized Units Checklist Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="bi bi-diagram-3 text-primary me-2"></i>Quy Trình 5 Kíp Phản Ứng Nhanh Toàn Viện Khi Có Báo Động Đỏ
            </h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 text-center">
                <div class="col-sm-6 col-md">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-hospital fs-3 text-danger d-block mb-1"></i>
                        <strong class="small d-block text-dark">Kíp Cấp Cứu (ER)</strong>
                        <span class="badge bg-success-subtle text-success">Trực sảnh 24/7</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-activity fs-3 text-primary d-block mb-1"></i>
                        <strong class="small d-block text-dark">Cathlab / Can Thiệp Mạch</strong>
                        <span class="badge bg-success-subtle text-success">Sẵn sàng trong 10p</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-shield-plus fs-3 text-warning d-block mb-1"></i>
                        <strong class="small d-block text-dark">Phòng Mổ Cấp Cứu Số 1</strong>
                        <span class="badge bg-success-subtle text-success">Phòng mổ vô trùng</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-droplet-half fs-3 text-danger d-block mb-1"></i>
                        <strong class="small d-block text-dark">Ngân Hàng Máu</strong>
                        <span class="badge bg-success-subtle text-success">Dự trữ O âm khẩn</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md">
                    <div class="p-3 bg-light rounded-3 border">
                        <i class="bi bi-camera-fill fs-3 text-info d-block mb-1"></i>
                        <strong class="small d-block text-dark">CT Scanner 768 Lát</strong>
                        <span class="badge bg-success-subtle text-success">Dành riêng ưu tiên</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-journal-medical me-2"></i>Nhật Ký Tiếp Nhận Cấp Cứu Trước Viện</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Mã Ca / Loại Cấp Cứu</th>
                        <th>Bệnh Nhân / Tuổi</th>
                        <th>Đơn Vị Vận Chuyển</th>
                        <th>Chẩn Đoán / Diễn Biến</th>
                        <th>Thời Gian Vàng</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codeReds as $item)
                        <tr>
                            <td>
                                <strong class="text-danger font-monospace d-block">{{ $item->code_red_number }}</strong>
                                <span class="badge bg-light text-danger border">{{ $item->emergency_type_name }}</span>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $item->patient_name }}</strong>
                                <small class="text-muted">{{ $item->estimated_age }}</small>
                            </td>
                            <td>
                                <strong class="text-dark small d-block">{{ $item->ambulance_unit }}</strong>
                                <small class="text-muted">{{ $item->paramedic_in_charge }}</small>
                            </td>
                            <td style="max-width: 250px;">
                                <p class="small text-dark mb-0 text-truncate" title="{{ $item->clinical_condition }}">{{ $item->clinical_condition }}</p>
                            </td>
                            <td>
                                @if($item->door_to_intervention_minutes !== null)
                                    <strong class="text-success">{{ $item->door_to_intervention_minutes }} phút</strong>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>
                            <td>
                                {!! $item->status_badge !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-hospital fs-1 d-block mb-2 text-secondary"></i>
                                Chưa có ca báo động đỏ nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($codeReds->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $codeReds->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Kích Hoạt Báo Động Đỏ -->
<div class="modal fade" id="activateCodeRedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('reception.code-red.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>Kích Hoạt Báo Động Đỏ Cấp Cứu Toàn Viện
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Họ tên bệnh nhân <span class="text-danger">*</span></label>
                            <input type="text" name="patient_name" class="form-control" placeholder="Họ tên hoặc 'Bệnh nhân chưa rõ tên'" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="unknown">Chưa xác định</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Ước tính tuổi</label>
                            <input type="text" name="estimated_age" class="form-control" placeholder="VD: Khoảng 55 tuổi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Loại cấp cứu tối khẩn <span class="text-danger">*</span></label>
                            <select name="emergency_type" class="form-select" required>
                                <option value="stroke">Code Stroke (Đột quỵ não cấp - Giờ vàng < 4.5h)</option>
                                <option value="stemi">Code STEMI (Nhồi máu cơ tim cấp - Cần can thiệp mạch vành)</option>
                                <option value="trauma">Code Red Trauma (Đa chấn thương sốc mất máu)</option>
                                <option value="cardiac_arrest">Code Blue (Ngừng tuần hoàn ngoại viện)</option>
                                <option value="anaphylaxis">Sốc phản vệ nguy kịch độ IV</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Đơn vị xe cấp cứu <span class="text-danger">*</span></label>
                            <input type="text" name="ambulance_unit" class="form-control" value="Xe Cấp Cứu 115 Tâm Anh Số 01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Thời gian xe tới (ETA)</label>
                            <div class="input-group">
                                <input type="number" name="eta_minutes" class="form-control" value="10" min="1" max="120" required>
                                <span class="input-group-text">phút</span>
                            </div>
                        </div>

                        <!-- Sinh hiệu trên xe -->
                        <div class="col-md-3">
                            <label class="form-label small">Huyết áp (mmHg)</label>
                            <input type="text" name="blood_pressure" class="form-control form-control-sm" placeholder="VD: 190/110">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Mạch (lần/phút)</label>
                            <input type="text" name="pulse" class="form-control form-control-sm" placeholder="VD: 120">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">SpO2 (%)</label>
                            <input type="text" name="spo2" class="form-control form-control-sm" placeholder="VD: 88">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Glasgow</label>
                            <input type="text" name="glasgow" class="form-control form-control-sm" placeholder="VD: 12/15">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Mô tả tình trạng lâm sàng & Diễn biến <span class="text-danger">*</span></label>
                            <textarea name="clinical_condition" class="form-control" rows="3" placeholder="Liệt nửa người phải khởi phát đột ngột cách 40 phút, méo miệng, huyết áp tăng cao..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-bell-fill me-1"></i>PHÁT LỆNH BÁO ĐỘNG ĐỎ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Web Audio API Siren Alarm Simulator
    function playCodeRedSiren() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const ctx = new AudioContext();
            
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            osc.type = 'sawtooth';
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            const now = ctx.currentTime;
            gain.gain.setValueAtTime(0.15, now);
            
            // Còi hú 2 âm tần số cao cấp cứu
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.setValueAtTime(440, now + 0.3);
            osc.frequency.setValueAtTime(880, now + 0.6);
            osc.frequency.setValueAtTime(440, now + 0.9);
            osc.frequency.setValueAtTime(880, now + 1.2);
            
            gain.gain.exponentialRampToValueAtTime(0.001, now + 1.8);
            
            osc.start(now);
            osc.stop(now + 1.8);
        } catch(e) {
            console.error("Audio error: ", e);
        }
    }
</script>
@endsection
