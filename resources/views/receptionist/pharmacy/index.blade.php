@extends('layouts.admin')

@section('title', 'Quầy Dược & Cấp Phát Thuốc Ngoại Trú - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Quầy Dược & Cấp Phát Thuốc Ngoại Trú" subtitle="Quản lý hàng đợi phát thuốc, kiểm tra toa thuốc của bác sĩ và in nhãn hướng dẫn sử dụng">
    <x-slot:actions>
        <a href="{{ route('reception.pharmacy.inventory.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-box-seam"></i> Quản lý Kho Thuốc & Tồn Kho
        </a>
        <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Về Quầy tiếp nhận
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Statistical KPI Counters -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-4 border-warning p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Chờ Cấp Phát Thuốc</div>
                    <h3 class="fw-bold text-warning mb-0 mt-1 font-monospace">{{ $pendingCount }} <small class="fs-6 text-muted fw-normal">toa</small></h3>
                    <small class="text-muted">Bệnh nhân đang chờ nhận thuốc hôm nay</small>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-4 border-success p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Đã Cấp Phát Hôm Nay</div>
                    <h3 class="fw-bold text-success mb-0 mt-1 font-monospace">{{ $dispensedTodayCount }} <small class="fs-6 text-muted fw-normal">toa</small></h3>
                    <small class="text-muted">Đã giao thuốc & dặn dò an toàn</small>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success">
                    <i class="bi bi-check2-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-4 border-primary p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Tổng Toa Thuốc Hôm Nay</div>
                    <h3 class="fw-bold text-primary mb-0 mt-1 font-monospace">{{ $totalTodayCount }} <small class="fs-6 text-muted fw-normal">toa</small></h3>
                    <small class="text-muted">Toa ngoại trú phát sinh trong ngày</small>
                </div>
                <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                    <i class="bi bi-capsule-pill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search Toolbar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reception.pharmacy.index') }}" class="row g-2 align-items-center">
            <!-- Date Filter -->
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar3"></i></span>
                    <input type="date" name="date" class="form-control" value="{{ $dateFilter }}" onchange="this.form.submit()">
                </div>
            </div>

            <!-- Status Tabs / Buttons -->
            <div class="col-md-4">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <a href="{{ route('reception.pharmacy.index', ['date' => $dateFilter, 'status' => 'all', 'q' => $search]) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('reception.pharmacy.index', ['date' => $dateFilter, 'status' => 'pending', 'q' => $search]) }}" class="btn {{ $statusFilter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                        <i class="bi bi-clock me-1"></i> Chờ phát
                    </a>
                    <a href="{{ route('reception.pharmacy.index', ['date' => $dateFilter, 'status' => 'dispensed', 'q' => $search]) }}" class="btn {{ $statusFilter === 'dispensed' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">
                        <i class="bi bi-check-lg me-1"></i> Đã phát
                    </a>
                </div>
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            </div>

            <!-- Search Bar -->
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm theo tên BN, mã BN, mã đơn hoặc tên thuốc...">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                    @if(!empty($search) || $statusFilter !== 'all' || $dateFilter !== date('Y-m-d'))
                        <a href="{{ route('reception.pharmacy.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Prescriptions List Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-prescription2 text-primary fs-5"></i> Danh Sách Toa Thuốc Ngoại Trú
        </h6>
        <span class="text-muted small">Hiển thị {{ $records->total() }} toa thuốc</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Bệnh Nhân</th>
                        <th>Bác Sĩ Chỉ Định</th>
                        <th>Toa Thuốc ({{ $records->count() }})</th>
                        <th>Trạng Thái Phát Thuốc</th>
                        <th class="text-end pe-3" style="min-width: 220px;">Thao Tác Dược Sĩ</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($records as $idx => $record)
                        <tr class="{{ $record->isDispensed() ? 'bg-light bg-opacity-25' : '' }}">
                            <!-- Index & Record Code -->
                            <td class="ps-3">
                                <span class="text-muted fw-bold">{{ $records->firstItem() + $idx }}</span>
                                <div class="font-monospace text-primary fw-semibold" style="font-size: 0.72rem;">{{ $record->record_code }}</div>
                            </td>

                            <!-- Patient Details -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                        {{ substr($record->patientProfile->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block">{{ $record->patientProfile->full_name }}</strong>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Mã BN: <span class="font-monospace text-primary">{{ $record->patientProfile->patient_code }}</span> &bull;
                                            {{ $record->patientProfile->age_text }} &bull; {{ $record->patientProfile->gender_name }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            <i class="bi bi-telephone"></i> {{ $record->patientProfile->phone }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Prescribing Doctor & Specialty -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $record->doctor ? $record->doctor->full_name_with_title : '--' }}</div>
                                <div class="text-muted small">{{ $record->appointment->specialty?->name ?? 'Khoa Khám Bệnh' }}</div>
                                <div class="text-truncate text-secondary" style="font-size: 0.72rem; max-width: 180px;" title="{{ $record->diagnosis }}">
                                    CĐ: {{ $record->diagnosis }}
                                </div>
                            </td>

                            <!-- Prescriptions Chips -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-secondary font-monospace">{{ $record->prescriptions->count() }} loại thuốc</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="viewPrescriptionDetail({{ $record->id }})">
                                            <i class="bi bi-eye"></i> Xem toa
                                        </button>
                                    </div>
                                    <div class="text-truncate text-dark fw-medium" style="max-width: 260px; font-size: 0.76rem;">
                                        {{ $record->prescriptions->pluck('medication_name')->implode(', ') }}
                                    </div>
                                    @php
                                        $medNames = $record->prescriptions->pluck('medication_name')->toArray();
                                        $ddiAlerts = \App\Services\DrugInteractionService::analyze($medNames);
                                    @endphp
                                    @if(!empty($ddiAlerts))
                                        <div class="mt-1">
                                            <span class="badge bg-danger text-white border small" title="{{ $ddiAlerts[0]['title'] }}">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Cảnh báo DDI ({{ count($ddiAlerts) }})
                                            </span>
                                        </div>
                                    @endif

                                    @php
                                        $medList = $record->prescriptions->map(fn($p) => ['name' => $p->medication_name, 'active_ingredient' => $p->medication_name])->toArray();
                                        $allergyResult = \App\Services\AllergyCrossReactionService::checkAllergies($record->patientProfile->allergies, $medList);
                                    @endphp
                                    @if($allergyResult['has_critical_allergy'])
                                        <div class="mt-1">
                                            <span class="badge bg-danger text-white border border-danger small animate-pulse" title="{{ $allergyResult['alerts'][0]['explanation'] }}">
                                                <i class="bi bi-shield-slash-fill me-1"></i>NGUY CƠ DỊ ỨNG ({{ $allergyResult['alerts_count'] }})
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Dispense Status -->
                            <td>
                                @if($record->isDispensed())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check2-circle me-1"></i> ĐÃ PHÁT THUỐC
                                    </span>
                                    <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                        Lúc: <strong>{{ $record->dispensed_at->format('H:i d/m') }}</strong><br>
                                        DS: {{ $record->dispensedBy?->name ?? 'Dược sĩ' }}
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i> CHỜ PHÁT THUỐC
                                    </span>
                                    <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                        Đơn kê lúc: {{ $record->created_at->format('H:i') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if(!$record->isDispensed())
                                        <!-- Confirm Dispensed Button -->
                                        <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm" onclick="openDispenseModal({{ $record->id }}, '{{ addslashes($record->patientProfile->full_name) }}', '{{ $record->record_code }}')">
                                            <i class="bi bi-check2"></i> Phát thuốc
                                        </button>
                                    @else
                                        <!-- Undo Dispense Form -->
                                        <form action="{{ route('reception.pharmacy.undo', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có muốn hoàn tác trạng thái phát thuốc của đơn này về Chờ phát?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Hoàn tác">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Print Drug Bag Label -->
                                    <a href="{{ route('reception.pharmacy.label', $record) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm" title="In nhãn hướng dẫn túi thuốc dán bao bì">
                                        <i class="bi bi-bag"></i> Nhãn túi
                                    </a>

                                    <!-- Print Smart Bottle Stickers -->
                                    <a href="{{ route('reception.pharmacy.bottle-labels', $record) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm" title="In tem dán hộp/lọ thuốc thông minh (Decal 75x50mm)">
                                        <i class="bi bi-tag-fill"></i> Tem lọ thuốc
                                    </a>

                                    <!-- Visual Schedule & iCal -->
                                    <a href="{{ route('reception.pharmacy.schedule', $record) }}" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1 shadow-sm" title="Thời gian biểu dùng thuốc & Đồng bộ Lịch điện thoại (.ics)">
                                        <i class="bi bi-calendar2-check"></i> Lịch uống
                                    </a>

                                    <!-- Pharmacy Voice Audio Caller -->
                                    <button type="button" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1 shadow-sm" title="Phát loa gọi bệnh nhân đến Quầy Dược nhận thuốc"
                                        onclick="callPharmacyPatientVoice('{{ addslashes($record->patientProfile->full_name) }}', '{{ $record->record_code }}')">
                                        <i class="bi bi-megaphone-fill text-warning"></i> Gọi loa
                                    </button>

                                    <!-- Print MOH Prescription -->
                                    <a href="{{ route('doctor.records.print', $record) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="In toa thuốc chuẩn Bộ Y Tế">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Hidden Data container for JS modal -->
                        <div id="rx-data-{{ $record->id }}" class="d-none">
                            <div class="rx-patient">{{ $record->patientProfile->full_name }}</div>
                            <div class="rx-code">{{ $record->record_code }}</div>
                            <div class="rx-doctor">{{ $record->doctor ? $record->doctor->full_name_with_title : '--' }}</div>
                            <div class="rx-diagnosis">{{ $record->diagnosis }}</div>
                            <div class="rx-items">
                                @foreach($record->prescriptions as $p)
                                    <div class="rx-item" data-name="{{ $p->medication_name }}" data-dosage="{{ $p->dosage }}" data-freq="{{ $p->frequency }}" data-days="{{ $p->duration_days }}" data-inst="{{ $p->instructions }}"></div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-capsule fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                <h6>Không tìm thấy toa thuốc nào phù hợp với bộ lọc</h6>
                                <p class="small mb-0">Hãy thử thay đổi ngày hoặc chọn trạng thái "Tất cả".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: View Prescription Details -->
<div class="modal fade" id="prescriptionDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-file-medical text-primary"></i> Chi Tiết Toa Thuốc: <span id="modalRxCode" class="font-monospace text-primary"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 mb-3 pb-3 border-bottom small">
                    <div class="col-md-6">
                        <span class="text-muted">Bệnh nhân:</span> <strong id="modalRxPatient" class="text-dark"></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Bác sĩ chỉ định:</span> <strong id="modalRxDoctor" class="text-dark"></strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Chẩn đoán:</span> <span id="modalRxDiagnosis" class="fw-semibold text-primary"></span>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-muted mb-2">Danh mục thuốc & Hướng dẫn sử dụng:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 40px;">STT</th>
                                <th>Tên thuốc</th>
                                <th>Liều lượng</th>
                                <th>Tần suất</th>
                                <th>Số ngày</th>
                                <th>Hướng dẫn / Lời dặn</th>
                            </tr>
                        </thead>
                        <tbody id="modalRxTableBody" class="small"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Confirm Dispensing Modal -->
<div class="modal fade" id="dispenseConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="" id="dispenseForm" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Xác Nhận Cấp Phát Thuốc Ngoại Trú
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small mb-3">
                    Bệnh nhân: <strong id="dispensePatientName" class="text-dark"></strong><br>
                    Mã toa: <span id="dispenseRecordCode" class="font-monospace fw-bold text-primary"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Ghi chú giao nhận thuốc / Hướng dẫn bệnh nhân:</label>
                    <input type="text" name="dispense_notes" class="form-control form-control-sm" value="Đã kiểm tra đúng 5 đúng (Đúng người, đúng thuốc, đúng liều, đúng đường dùng, đúng thời gian) và hướng dẫn sử dụng." required>
                </div>

                <div class="form-check small text-muted">
                    <input class="form-check-input" type="checkbox" id="checkVerify" checked required>
                    <label class="form-check-label" for="checkVerify">
                        Dược sĩ / Người phát thuốc đã kiểm tra đối chiếu hạn sử dụng và cảm quan của toàn bộ các mặt hàng thuốc trong toa.
                    </label>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success btn-sm px-3 fw-semibold">
                    <i class="bi bi-check-lg"></i> Xác nhận đã phát thuốc
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function viewPrescriptionDetail(recordId) {
        const container = document.getElementById(`rx-data-${recordId}`);
        if (!container) return;

        document.getElementById('modalRxCode').textContent = container.querySelector('.rx-code').textContent;
        document.getElementById('modalRxPatient').textContent = container.querySelector('.rx-patient').textContent;
        document.getElementById('modalRxDoctor').textContent = container.querySelector('.rx-doctor').textContent;
        document.getElementById('modalRxDiagnosis').textContent = container.querySelector('.rx-diagnosis').textContent;

        const items = container.querySelectorAll('.rx-item');
        const tbody = document.getElementById('modalRxTableBody');
        tbody.innerHTML = '';

        items.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center fw-bold text-muted">${index + 1}</td>
                <td class="fw-semibold text-primary">${item.dataset.name}</td>
                <td><span class="badge bg-light text-dark border">${item.dataset.dosage}</span></td>
                <td>${item.dataset.freq}</td>
                <td>${item.dataset.days} ngày</td>
                <td class="text-secondary">${item.dataset.inst || 'Uống theo chỉ định'}</td>
            `;
            tbody.appendChild(tr);
        });

        const modal = new bootstrap.Modal(document.getElementById('prescriptionDetailModal'));
        modal.show();
    }

    function openDispenseModal(recordId, patientName, recordCode) {
        const form = document.getElementById('dispenseForm');
        form.action = `{{ url('/reception/pharmacy') }}/${recordId}/dispense`;
        document.getElementById('dispensePatientName').textContent = patientName;
        document.getElementById('dispenseRecordCode').textContent = recordCode;

        const modal = new bootstrap.Modal(document.getElementById('dispenseConfirmModal'));
        modal.show();
    }

    // Hospital Chime + Speech Synthesis for Pharmacy Voice Caller
    function playHospitalDingDong() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Tone 1: 523.25 Hz (C5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(523.25, ctx.currentTime);
            gain1.gain.setValueAtTime(0.3, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.45);

            // Tone 2: 659.25 Hz (E5)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(659.25, ctx.currentTime + 0.22);
            gain2.gain.setValueAtTime(0.35, ctx.currentTime + 0.22);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.85);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.22);
            osc2.stop(ctx.currentTime + 0.85);
        } catch (e) {
            console.warn('Audio chime unavailable', e);
        }
    }

    function callPharmacyPatientVoice(patientName, recordCode) {
        playHospitalDingDong();

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();

            setTimeout(() => {
                const speechText = `Kính mời bệnh nhân ${patientName}, mã đơn ${recordCode}, vui lòng đến Quầy Dược số 01 nhận thuốc và tư vấn sử dụng!`;
                const utterance = new SpeechSynthesisUtterance(speechText);
                utterance.lang = 'vi-VN';
                utterance.rate = 0.92;
                utterance.pitch = 1.05;

                const voices = window.speechSynthesis.getVoices();
                const viVoice = voices.find(v => v.lang.includes('vi') || v.lang.includes('VN'));
                if (viVoice) utterance.voice = viVoice;

                window.speechSynthesis.speak(utterance);
            }, 450);
        }

        // Show floating toast
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show align-items-center text-bg-warning border-0 shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body text-dark">
                        <i class="bi bi-megaphone-fill me-2 fs-5"></i>
                        <strong>Đang phát loa quầy Dược:</strong> Mời <b>${patientName}</b> (Toa ${recordCode}) &rarr; <b>Quầy Dược số 01</b>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
</script>
@endpush
@endsection
