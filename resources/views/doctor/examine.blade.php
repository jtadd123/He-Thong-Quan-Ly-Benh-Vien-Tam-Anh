@extends('layouts.admin')

@section('title', 'Bàn Khám Bác Sĩ - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Bàn Khám Bệnh Ngoại Trú" subtitle="Bàn Khám Bệnh & Lập Bệnh Án - Ghi nhận hồ sơ bệnh án, chẩn đoán lâm sàng và kê đơn thuốc điều trị">
    <x-slot:actions>
        <a href="{{ route('doctor.queue') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Quay lại hàng đợi
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Emergency Clinical Safety Alert Banner (CDSS) -->
@if($appointment->patientProfile->hasAllergies())
    <div class="alert alert-danger border-2 border-danger shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-shield-exclamation fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold text-danger mb-0 text-uppercase">
                    Cảnh Báo An Toàn Người Bệnh: Tiền Sử Dị Ứng Thuốc / Thức Ăn!
                </h6>
                <div class="fs-6 fw-bold text-dark mt-1">
                    {{ $appointment->patientProfile->allergies }}
                </div>
                <small class="text-danger">* Bác sĩ thận trọng khi kê đơn, tuyệt đối tránh các nhóm hoạt chất gây nguy cơ sốc phản vệ.</small>
            </div>
        </div>
        <span class="badge bg-danger text-white px-3 py-2 fs-6">
            <i class="bi bi-exclamation-octagon me-1"></i> BÁO ĐỘNG DỊ ỨNG
        </span>
    </div>
@endif

<div class="row g-4 mb-5">
    <!-- Left Column: Patient Profile & Initial Vitals -->
    <div class="col-lg-4">
        <!-- Patient Demographics Card -->
        <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 1rem;">
            <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-person-badge me-1"></i> Bệnh Nhân Đang Khám
                </h6>
                @if($appointment->is_priority)
                    <span class="badge bg-danger text-white border border-white small">
                        <i class="bi bi-star-fill text-warning me-1"></i> ƯU TIÊN
                    </span>
                @endif
            </div>
            <div class="card-body p-3">
                <div class="text-center pb-3 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fs-2 mb-2" style="width: 64px; height: 64px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark text-uppercase mb-0">{{ $appointment->patientProfile->full_name }}</h5>
                    <div class="text-muted small">
                        Mã BN: <strong class="font-monospace text-primary">{{ $appointment->patientProfile->patient_code }}</strong>
                    </div>
                </div>

                <ul class="list-unstyled mb-0 py-3 d-flex flex-column gap-2 small border-bottom">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Tuổi / Giới tính:</span>
                        <strong class="text-dark">{{ $appointment->patientProfile->age_text }} &bull; {{ $appointment->patientProfile->gender_name }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Số điện thoại:</span>
                        <span class="font-monospace fw-semibold">{{ $appointment->patientProfile->phone }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Mã lịch hẹn:</span>
                        <span class="font-monospace fw-bold text-primary">{{ $appointment->appointment_code }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Nhóm máu:</span>
                        <span class="badge bg-danger text-white font-monospace">{{ $appointment->patientProfile->blood_group ?: 'Chưa xác định' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Số thứ tự khám:</span>
                        <span class="badge bg-primary text-white font-monospace fs-6">#{{ str_pad($appointment->queue_number, 2, '0', STR_PAD_LEFT) }}</span>
                    </li>
                </ul>

                @if($appointment->patientProfile->hasMedicalHistory())
                    <div class="p-2 mt-2 bg-warning bg-opacity-10 rounded border border-warning small">
                        <strong class="text-warning-emphasis d-block"><i class="bi bi-activity me-1"></i> Bệnh nền / Tiền sử:</strong>
                        <span class="text-dark">{{ $appointment->patientProfile->medical_history }}</span>
                    </div>
                @endif

                <!-- Initial Vitals Intake from Reception Desk -->
                <div class="pt-3">
                    <h6 class="fw-bold small text-info-emphasis text-uppercase mb-2">
                        <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Sinh hiệu đo tại quầy đón tiếp:
                    </h6>
                    @if(str_contains($appointment->symptom_description ?? '', '[Sinh hiệu:'))
                        @php
                            preg_match('/\[Sinh hiệu: (.*?)\]/', $appointment->symptom_description, $vMatch);
                            $vitalsStr = $vMatch[1] ?? '';
                            $cleanDesc = trim(preg_replace('/\[Sinh hiệu: .*?\]/', '', preg_replace('/\[ƯU TIÊN\]/', '', $appointment->symptom_description)));
                        @endphp
                        <div class="p-2 bg-info bg-opacity-10 rounded border border-info small mb-2 text-dark">
                            <strong>{{ $vitalsStr }}</strong>
                        </div>
                        <div class="small text-muted">
                            Lý do bệnh nhân đến khám: <br>
                            <span class="text-dark fw-medium">{{ $cleanDesc ?: 'Khám chuyên khoa' }}</span>
                        </div>
                    @else
                        <div class="p-2 bg-light rounded border small text-muted">
                            {{ $appointment->symptom_description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Past Medical History Accordion -->
        @if(isset($pastRecords) && $pastRecords->isNotEmpty())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">
                        <i class="bi bi-clock-history me-1"></i> Tiền Sử Khám Cũ ({{ $pastRecords->count() }} lần)
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="accordion accordion-flush" id="pastRecordsAccordion">
                        @foreach($pastRecords as $idx => $past)
                            <div class="accordion-item border-bottom">
                                <h2 class="accordion-header" id="heading-{{ $past->id }}">
                                    <button class="accordion-button collapsed py-2 px-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $past->id }}">
                                        <div>
                                            <strong>{{ $past->created_at->format('d/m/Y') }}</strong>:
                                            <span class="text-primary">{{ $past->diagnosis }}</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $past->id }}" class="accordion-collapse collapse" data-bs-parent="#pastRecordsAccordion">
                                    <div class="accordion-body p-2 small bg-light">
                                        <div class="mb-1"><strong>Bác sĩ:</strong> {{ $past->doctor ? $past->doctor->full_name_with_title : '--' }}</div>
                                        <div class="mb-1"><strong>Triệu chứng:</strong> {{ $past->symptoms }}</div>
                                        @if($past->treatment_plan)
                                            <div class="mb-1"><strong>Điều trị:</strong> {{ $past->treatment_plan }}</div>
                                        @endif
                                        @if($past->prescriptions->count() > 0)
                                            <div class="mt-2 pt-1 border-top">
                                                <strong>Thuốc đã kê:</strong>
                                                <ul class="mb-0 ps-3">
                                                    @foreach($past->prescriptions as $p)
                                                        <li>{{ $p->medication_name }} ({{ $p->dosage }})</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Medical Record Examination Form & Prescriptions -->
    <div class="col-lg-8">
        <form method="POST" action="{{ route('doctor.examine.store', $appointment) }}" id="examinationForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

            <!-- 1. Clinical Diagnosis Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-clipboard2-pulse-fill me-1"></i> 1. Chẩn Đoán & Khám Lâm Sàng
                    </h6>
                    <span class="badge bg-light text-dark border small">Bác sĩ: {{ Auth::user()->name }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Symptoms -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Triệu chứng lâm sàng ghi nhận: <span class="text-danger">*</span></label>
                            @php
                                $defaultSymptoms = preg_replace('/\[Sinh hiệu: .*?\]/', '', preg_replace('/\[ƯU TIÊN\]/', '', $appointment->symptom_description));
                            @endphp
                            <textarea name="symptoms" class="form-control @error('symptoms') is-invalid @enderror" rows="2" placeholder="Ghi nhận diễn tiến bệnh, thời gian khởi phát..." required>{{ old('symptoms', trim($defaultSymptoms)) }}</textarea>
                            @error('symptoms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Diagnosis -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Chẩn đoán kết luận của bác sĩ: <span class="text-danger">*</span></label>
                            <input type="text" name="diagnosis" value="{{ old('diagnosis', $appointment->medicalRecord?->diagnosis) }}" class="form-control fw-bold text-primary @error('diagnosis') is-invalid @enderror" placeholder="VD: Viêm họng cấp / Tăng huyết áp độ 1 / Viêm dạ dày tá tràng..." required>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Clinical Notes & Treatment Plan -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Ghi chú lâm sàng / Cận lâm sàng:</label>
                            <textarea name="clinical_notes" class="form-control" rows="2" placeholder="Kết quả tim phổi, xét nghiệm máu, X-Quang, siêu âm...">{{ old('clinical_notes', $appointment->medicalRecord?->clinical_notes) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phác đồ / Hướng điều trị:</label>
                            <textarea name="treatment_plan" class="form-control" rows="2" placeholder="Nghỉ ngơi, chế độ ăn giảm muối, uống nhiều nước...">{{ old('treatment_plan', $appointment->medicalRecord?->treatment_plan) }}</textarea>
                        </div>

                        <!-- Follow up date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Hẹn ngày tái khám (nếu có):</label>
                            <input type="date" name="follow_up_date" min="{{ date('Y-m-d') }}" value="{{ old('follow_up_date', $appointment->medicalRecord?->follow_up_date?->format('Y-m-d')) }}" class="form-control form-control-sm">
                            <div class="form-text small">Hệ thống sẽ đồng bộ lịch tái khám vào cổng hồ sơ của bệnh nhân.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Dynamic Prescription Builder -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h6 class="fw-bold text-success mb-0">
                            <i class="bi bi-capsule-pill me-1"></i> 2. Kê Đơn Thuốc Ngoại Trú (Prescription)
                        </h6>
                        <small class="text-muted">Kê đơn thuốc điều trị ngoại trú theo danh mục thuốc chuẩn Bộ Y Tế</small>
                    </div>

                    <!-- Quick medication dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-success btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Chọn thuốc nhanh mẫu
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 320px; overflow-y: auto; min-width: 300px;">
                            @foreach($commonMeds as $m)
                                <li>
                                    <a class="dropdown-item small d-flex justify-content-between align-items-center py-2" href="javascript:void(0)" onclick="addQuickMedication('{{ addslashes($m['name']) }}', '{{ addslashes($m['dosage']) }}', '{{ addslashes($m['frequency']) }}', '{{ $m['duration'] }}', '{{ addslashes($m['instructions']) }}')">
                                        <div>
                                            <strong>{{ $m['name'] }}</strong>
                                            <div class="text-muted" style="font-size: 0.72rem;">{{ $m['dosage'] }} &bull; {{ $m['frequency'] }}</div>
                                        </div>
                                        @if(isset($m['stock_quantity']))
                                            @if(!empty($m['is_out_of_stock']))
                                                <span class="badge bg-danger ms-2">Hết hàng</span>
                                            @elseif($m['stock_quantity'] <= 50)
                                                <span class="badge bg-warning text-dark ms-2">Còn {{ $m['stock_quantity'] }}</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">Kho: {{ $m['stock_quantity'] }}</span>
                                            @endif
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div id="prescriptionItemsContainer">
                        <!-- Prescription Rows inserted here by JS -->
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" onclick="addPrescriptionRow()">
                            <i class="bi bi-plus-circle-fill"></i> Thêm thuốc khác
                        </button>
                        <span class="text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i>Đang bật kiểm tra Tương tác thuốc (DDI) & An toàn dị ứng thời gian thực
                        </span>
                    </div>

                    <!-- Clinical Drug-Drug Interaction (DDI) Alert Box -->
                    <div id="ddiAlertContainer" class="mt-3" style="display: none;"></div>
                </div>
            </div>

            <!-- 3. Medical Attachments Section (X-Quang, CT/MRI, Siêu Âm, Kết Quả Xét Nghiệm) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-info-emphasis mb-0">
                            <i class="bi bi-paperclip me-1"></i> 3. Cận Lâm Sàng & Tệp Đính Kèm (X-Quang, MRI, Siêu Âm, Xét Nghiệm)
                        </h6>
                        <small class="text-muted">Tải lên phim X-Quang, ảnh siêu âm, phiếu xét nghiệm (Định dạng JPG, PNG, WEBP, PDF &bull; Tối đa 10MB/tệp)</small>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle small">PACS / Cloud Storage</span>
                </div>

                <div class="card-body p-4">
                    @if(isset($appointment->medicalRecord) && $appointment->medicalRecord->attachments->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Tệp cận lâm sàng đã có trong bệnh án:</label>
                            <div class="row g-2">
                                @foreach($appointment->medicalRecord->attachments as $att)
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-2 border rounded bg-light d-flex align-items-center gap-2">
                                            @if($att->is_image)
                                                <img src="{{ $att->url }}" alt="{{ $att->original_name }}" class="rounded border object-fit-cover" style="width: 44px; height: 44px;">
                                            @else
                                                <div class="rounded bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                    <i class="bi bi-file-earmark-pdf fs-4"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1 text-truncate">
                                                <a href="{{ $att->url }}" target="_blank" class="small fw-semibold text-truncate d-block text-decoration-none">
                                                    {{ $att->original_name }}
                                                </a>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    {{ $att->formatted_size }} &bull; {{ $att->file_type }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="border border-dashed rounded p-3 bg-light bg-opacity-25">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Chọn tệp tải lên bổ sung:</label>
                                <input type="file" name="attachments[]" id="attachmentInput" class="form-control form-control-sm" multiple accept="image/jpeg,image/png,image/webp,application/pdf" onchange="previewSelectedAttachments(this)">
                                <div class="form-text small">Có thể giữ phím Ctrl để chọn cùng lúc nhiều tệp X-quang/kết quả xét nghiệm.</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Phân loại tệp:</label>
                                <select name="attachment_types[]" class="form-select form-select-sm">
                                    <option value="x_ray">Phim X-Quang / CT / MRI / Siêu âm</option>
                                    <option value="lab_result">Kết quả Xét nghiệm Máu / Sinh hóa</option>
                                    <option value="prescription_scan">Scan đơn thuốc cũ / Hồ sơ tuyến dưới</option>
                                </select>
                            </div>
                        </div>

                        <!-- Client-side Preview of selected files -->
                        <div id="attachmentsPreviewList" class="row g-2 mt-2" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Submit Action Card -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('doctor.queue') }}" class="btn btn-light px-4">Hủy / Quay lại</a>
                <button type="submit" class="btn btn-success px-4 py-2 d-inline-flex align-items-center gap-2 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle fs-5"></i> Lưu Bệnh Án & Hoàn Tất Ca Khám
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let prescriptionIndex = 0;

    const patientAllergies = "{{ addslashes(mb_strtolower($appointment->patientProfile->allergies ?? '')) }}";

    function checkAllergyConflict(inputEl, idx) {
        if (!patientAllergies || patientAllergies.includes('không') || patientAllergies.includes('none')) return;

        const val = inputEl.value.trim().toLowerCase();
        const alertContainer = document.getElementById(`allergy-alert-${idx}`);
        if (!alertContainer) return;

        // Common cross-reactivity mapping for clinical decision support
        const rules = [
            { trigger: ['penicillin', 'amox', 'augmentin', 'clavam'], match: ['penicillin', 'amoxicillin', 'ampicillin', 'augmentin'] },
            { trigger: ['aspirin', 'nsaid', 'ibuprofen', 'meloxicam', 'diclofenac'], match: ['aspirin', 'nsaid', 'ibuprofen'] },
            { trigger: ['paracetamol', 'panadol', 'efferalgan', 'acetaminophen'], match: ['paracetamol', 'panadol', 'acetaminophen'] },
            { trigger: ['cephalo', 'cefixim', 'ceftriaxon', 'cefaclor'], match: ['cephalo', 'cefixim', 'cefaclor'] },
        ];

        let hasConflict = false;
        if (val.length >= 3) {
            // Check direct match with patient's allergy string
            const allergyWords = patientAllergies.split(/[,;\/\s]+/).filter(w => w.length >= 3);
            for (const word of allergyWords) {
                if (val.includes(word)) {
                    hasConflict = true;
                    break;
                }
            }

            // Check clinical cross-reactivity rules
            if (!hasConflict) {
                for (const rule of rules) {
                    const matchAllergy = rule.match.some(m => patientAllergies.includes(m));
                    const matchDrug = rule.trigger.some(t => val.includes(t));
                    if (matchAllergy && matchDrug) {
                        hasConflict = true;
                        break;
                    }
                }
            }
        }

        if (hasConflict) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small text-danger fw-bold border border-danger d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                    <span>CẢNH BÁO DƯỢC LÂM SÀNG: Thuốc này có thể gây dị ứng/sốc phản vệ cho bệnh nhân (Tiền sử: "${patientAllergies}")!</span>
                </div>
            `;
            inputEl.classList.add('is-invalid');
        } else {
            alertContainer.innerHTML = '';
            inputEl.classList.remove('is-invalid');
        }
    }

    function addPrescriptionRow(medName = '', dosage = '', frequency = '', duration = 5, instructions = '') {
        const container = document.getElementById('prescriptionItemsContainer');
        const idx = prescriptionIndex++;

        const rowHtml = `
            <div class="card border rounded p-3 mb-2 bg-light bg-opacity-50 prescription-row" id="pres-row-${idx}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="badge bg-secondary font-monospace small">Thuốc #${idx + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removePrescriptionRow(${idx})" title="Xóa dòng thuốc">
                        <i class="bi bi-trash"></i> Xóa
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Tên thuốc & Hàm lượng: <span class="text-danger">*</span></label>
                        <input type="text" name="prescriptions[${idx}][medication_name]" value="${medName}" class="form-control form-control-sm fw-semibold" placeholder="VD: Augmentin 1g" required oninput="checkAllergyConflict(this, ${idx})">
                        <div id="allergy-alert-${idx}"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Liều lượng:</label>
                        <input type="text" name="prescriptions[${idx}][dosage]" value="${dosage}" class="form-control form-control-sm" placeholder="VD: 1 viên/lần" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">Tần suất uống:</label>
                        <input type="text" name="prescriptions[${idx}][frequency]" value="${frequency}" class="form-control form-control-sm" placeholder="VD: Ngày 2 lần (sáng, tối)" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Số ngày dùng:</label>
                        <input type="number" name="prescriptions[${idx}][duration_days]" value="${duration}" min="1" max="90" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold mb-1">Lời dặn / Hướng dẫn uống:</label>
                        <input type="text" name="prescriptions[${idx}][instructions]" value="${instructions}" class="form-control form-control-sm" placeholder="VD: Uống sau bữa ăn no 15 phút...">
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        const insertedInput = document.querySelector(`input[name="prescriptions[${idx}][medication_name]"]`);
        if (insertedInput) {
            insertedInput.addEventListener('input', function() {
                checkAllergyConflict(this, idx);
                runDdiInteractionCheck();
            });
            if (medName) {
                checkAllergyConflict(insertedInput, idx);
            }
        }
        runDdiInteractionCheck();
    }

    function removePrescriptionRow(idx) {
        const row = document.getElementById(`pres-row-${idx}`);
        if (row) row.remove();
        runDdiInteractionCheck();
    }

    // Drug-Drug Interaction (DDI) Client Engine
    const ddiRules = [
        {
            a: ['aspirin', 'aspilet'],
            b: ['ibuprofen', 'diclofenac', 'meloxicam', 'celecoxib'],
            severity: 'danger',
            title: 'Tương tác Chống chỉ định: Nguy cơ xuất huyết tiêu hóa & thủng dạ dày',
            desc: 'Dùng chung Aspirin với NSAID làm tăng gấp 5 lần nguy cơ loét và xuất huyết dạ dày tá tràng nặng.',
            advice: 'Đổi sang Paracetamol hoặc bổ sung thuốc ức chế bơm proton (Nexium 40mg).'
        },
        {
            a: ['paracetamol', 'panadol', 'efferalgan'],
            b: ['paracetamol', 'panadol', 'efferalgan', 'hapacol'],
            severity: 'danger',
            title: 'Trùng lặp hoạt chất Paracetamol: Nguy cơ hoại tử tế bào gan cấp',
            desc: 'Đơn thuốc chứa từ 2 chế phẩm có cùng Paracetamol, rất dễ vượt ngưỡng ngộ độc 4g/ngày.',
            advice: 'Chỉ chọn 1 biệt dược giảm đau hạ sốt duy nhất.'
        },
        {
            a: ['clarithromycin', 'erythromycin'],
            b: ['amlodipine', 'amlodipin', 'simvastatin', 'atorvastatin'],
            severity: 'danger',
            title: 'Tương tác ức chế men gan CYP3A4 nguy cơ cao',
            desc: 'Macrolid làm tăng vọt nồng độ Amlodipine/Statin gây tụt huyết áp nghiêm trọng hoặc tiêu cơ vân.',
            advice: 'Cân nhắc chuyển sang Augmentin hoặc tạm ngưng thuốc tim mạch trong đợt kháng sinh.'
        },
        {
            a: ['ciprofloxacin', 'levofloxacin'],
            b: ['canxi', 'calcium', 'sat', 'iron', 'antacid'],
            severity: 'warning',
            title: 'Tương tác tạo phức Chelate giảm hấp thu kháng sinh',
            desc: 'Các ion kim loại làm giảm hấp thu kháng sinh đường ruột tới 80%.',
            advice: 'Uống kháng sinh cách thuốc bổ sung canxi/sắt ít nhất 2 đến 4 giờ.'
        }
    ];

    function runDdiInteractionCheck() {
        const ddiContainer = document.getElementById('ddiAlertContainer');
        if (!ddiContainer) return;

        const inputs = Array.from(document.querySelectorAll('input[name^="prescriptions"][name$="[medication_name]"]'));
        const meds = inputs.map(i => (i.value || '').trim().toLowerCase()).filter(v => v.length >= 3);

        const foundAlerts = [];

        for (let i = 0; i < meds.length; i++) {
            for (let j = i + 1; j < meds.length; j++) {
                const medA = meds[i];
                const medB = meds[j];

                ddiRules.forEach(r => {
                    const matchAB = r.a.some(k => medA.includes(k)) && r.b.some(k => medB.includes(k));
                    const matchBA = r.a.some(k => medB.includes(k)) && r.b.some(k => medA.includes(k));

                    if (matchAB || matchBA) {
                        foundAlerts.push({
                            ...r,
                            pair: `${inputs[i].value} & ${inputs[j].value}`
                        });
                    }
                });
            }
        }

        if (foundAlerts.length === 0) {
            ddiContainer.style.display = 'none';
            ddiContainer.innerHTML = '';
            return;
        }

        ddiContainer.style.display = 'block';
        ddiContainer.innerHTML = foundAlerts.map(a => `
            <div class="alert alert-${a.severity} border-${a.severity} shadow-sm p-3 mb-2">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-${a.severity}"></i>
                    <strong class="text-uppercase small">${a.title}</strong>
                    <span class="badge bg-${a.severity} text-white ms-auto">${a.pair}</span>
                </div>
                <div class="small text-dark mb-1">${a.desc}</div>
                <div class="small fw-bold text-${a.severity}">
                    <i class="bi bi-lightbulb me-1"></i>Khuyến nghị xử trí: ${a.advice}
                </div>
            </div>
        `).join('');
    }

    function addQuickMedication(name, dosage, frequency, duration, instructions) {
        addPrescriptionRow(name, dosage, frequency, duration, instructions);
    }

    function previewSelectedAttachments(input) {
        const previewList = document.getElementById('attachmentsPreviewList');
        previewList.innerHTML = '';
        if (!input.files || input.files.length === 0) {
            previewList.style.display = 'none';
            return;
        }

        previewList.style.display = 'flex';
        Array.from(input.files).forEach((file, index) => {
            const isImage = file.type.startsWith('image/');
            const sizeKb = (file.size / 1024).toFixed(0);
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';

            const card = document.createElement('div');
            card.className = 'p-2 border rounded bg-white shadow-sm d-flex align-items-center gap-2';

            if (isImage) {
                const img = document.createElement('img');
                img.className = 'rounded border object-fit-cover';
                img.style.width = '48px';
                img.style.height = '48px';
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; };
                reader.readAsDataURL(file);
                card.appendChild(img);
            } else {
                const iconDiv = document.createElement('div');
                iconDiv.className = 'rounded bg-danger-subtle text-danger d-flex align-items-center justify-content-center';
                iconDiv.style.width = '48px';
                iconDiv.style.height = '48px';
                iconDiv.innerHTML = '<i class="bi bi-file-earmark-pdf fs-4"></i>';
                card.appendChild(iconDiv);
            }

            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-grow-1 text-truncate';
            infoDiv.innerHTML = `
                <div class="small fw-semibold text-truncate text-dark" title="${file.name}">${file.name}</div>
                <div class="text-muted" style="font-size: 0.72rem;">${sizeKb} KB &bull; Sẵn sàng tải lên</div>
            `;
            card.appendChild(infoDiv);
            col.appendChild(card);
            previewList.appendChild(col);
        });
    }

    // Populate initial existing prescriptions or 1 empty default row
    document.addEventListener('DOMContentLoaded', function () {
        @if(isset($appointment->medicalRecord) && $appointment->medicalRecord->prescriptions->count() > 0)
            @foreach($appointment->medicalRecord->prescriptions as $p)
                addPrescriptionRow(
                    "{{ addslashes($p->medication_name) }}",
                    "{{ addslashes($p->dosage) }}",
                    "{{ addslashes($p->frequency) }}",
                    {{ $p->duration_days }},
                    "{{ addslashes($p->instructions) }}"
                );
            @endforeach
        @else
            // Default 1 blank row for convenience
            addPrescriptionRow('Paracetamol 500mg', '1 viên/lần', 'Ngày 2 lần khi sốt > 38.5°C', 5, 'Uống sau ăn no');
        @endif
    });
</script>
@endpush
@endsection
