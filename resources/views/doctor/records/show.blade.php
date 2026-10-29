@extends('layouts.admin')

@section('title', 'Chi Tiết Bệnh Án - ' . $record->record_code . ' - BV Tâm Anh')

@section('content')
<x-page-header title="Hồ Sơ Bệnh Án Ngoại Trú" subtitle="Mã bệnh án: {{ $record->record_code }} &bull; Đã hoàn tất khám và lưu trữ an toàn">
    <x-slot:actions>
        <a href="{{ route('doctor.records.print', $record) }}" target="_blank" class="btn btn-outline-dark d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-printer-fill"></i> In đơn thuốc (A4/A5)
        </a>
        <a href="{{ route('reception.certificates.create', $record) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-file-earmark-medical"></i> Cấp giấy nghỉ BHXH (C65-HD)
        </a>
        <a href="{{ route('doctor.examine', $record->appointment) }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-1">
            <i class="bi bi-pencil-square"></i> Chỉnh sửa bệnh án
        </a>
        <a href="{{ route('doctor.queue') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Về danh sách chờ
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4 mb-5">
    <!-- Left Column: Patient Summary -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person-bounding-box me-1"></i> Thông Tin Bệnh Nhân
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fs-2 mb-2" style="width: 58px; height: 58px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark text-uppercase mb-0">{{ $record->patientProfile->full_name }}</h5>
                    <div class="text-muted small">
                        Mã BN: <strong class="font-monospace text-primary">{{ $record->patientProfile->patient_code }}</strong>
                    </div>
                </div>

                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Tuổi / Giới tính:</span>
                        <strong class="text-dark">{{ $record->patientProfile->age_text }} &bull; {{ $record->patientProfile->gender_name }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Số điện thoại:</span>
                        <span class="font-monospace">{{ $record->patientProfile->phone }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Mã lịch hẹn:</span>
                        <span class="font-monospace fw-bold text-primary">{{ $record->appointment->appointment_code }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Bác sĩ khám:</span>
                        <strong class="text-dark">{{ $record->doctor ? $record->doctor->full_name_with_title : '--' }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-1">
                        <span class="text-muted">Thời gian khám:</span>
                        <span>{{ $record->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        @if($record->follow_up_date)
            <div class="alert alert-warning border shadow-sm p-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-calendar-event fs-5 text-warning-emphasis"></i>
                    <strong class="text-warning-emphasis">Hẹn ngày tái khám:</strong>
                </div>
                <div class="fs-5 fw-bold text-dark">{{ $record->follow_up_date->format('d/m/Y') }}</div>
                <div class="small text-muted">Bệnh nhân đã được nhắc lịch trên cổng tra cứu y tế.</div>
            </div>
        @endif
    </div>

    <!-- Right Column: Medical Record Details & Prescriptions -->
    <div class="col-lg-8">
        <!-- Diagnosis Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Kết Luận Lâm Sàng & Phác Đồ Điều Trị
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                    {{ $record->record_code }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="small text-muted fw-bold text-uppercase mb-1">Chẩn đoán kết luận của Bác sĩ:</div>
                    <div class="fs-5 fw-bold text-primary">{{ $record->diagnosis }}</div>
                </div>

                <div class="row g-3 mb-3 small">
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">Triệu chứng lâm sàng ghi nhận:</strong>
                        <p class="text-dark bg-white p-2 rounded border">{{ $record->symptoms }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">Ghi chú lâm sàng / Cận lâm sàng:</strong>
                        <p class="text-dark bg-white p-2 rounded border">{{ $record->clinical_notes ?: 'Không có ghi chú thêm.' }}</p>
                    </div>
                    @if($record->treatment_plan)
                        <div class="col-12">
                            <strong class="text-muted d-block mb-1">Kế hoạch điều trị & Dặn dò y khoa:</strong>
                            <p class="text-dark bg-white p-2 rounded border">{{ $record->treatment_plan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Prescriptions Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-success mb-0">
                    <i class="bi bi-capsule text-success me-1"></i> Đơn Thuốc Điều Trị Ngoại Trú ({{ $record->prescriptions->count() }} loại thuốc)
                </h6>
                <a href="{{ route('doctor.records.print', $record) }}" target="_blank" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1">
                    <i class="bi bi-printer"></i> In đơn thuốc
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3" style="width: 45px;">#</th>
                                <th>Tên thuốc</th>
                                <th>Liều lượng</th>
                                <th>Tần suất</th>
                                <th>Số ngày</th>
                                <th>Hướng dẫn / Lời dặn</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($record->prescriptions as $idx => $p)
                                <tr>
                                    <td class="ps-3 text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $p->medication_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $p->dosage }}</span></td>
                                    <td>{{ $p->frequency }}</td>
                                    <td>{{ $p->duration_days }} ngày</td>
                                    <td class="text-secondary">{{ $p->instructions ?: 'Uống theo chỉ dẫn' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Không kê đơn thuốc ngoại trú cho ca khám này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Medical Attachments Card (X-Quang, MRI, Siêu Âm, Kết Quả Xét Nghiệm) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-info-emphasis mb-0">
                        <i class="bi bi-images me-1"></i> Hồ Sơ Cận Lâm Sàng & Tệp Đính Kèm ({{ $record->attachments->count() }} tệp)
                    </h6>
                    <small class="text-muted">Phim X-Quang, CT, MRI, Siêu âm và Phiếu xét nghiệm y khoa</small>
                </div>
                <button class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#uploadMoreCollapse">
                    <i class="bi bi-cloud-arrow-up"></i> Tải thêm tệp cận lâm sàng
                </button>
            </div>

            <!-- Upload More Collapse Form -->
            <div class="collapse border-bottom bg-light p-3" id="uploadMoreCollapse">
                <form action="{{ route('doctor.records.attachments.upload', $record) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Chọn tệp bổ sung (JPG, PNG, WEBP, PDF tối đa 10MB):</label>
                        <input type="file" name="attachment" class="form-control form-control-sm" required accept="image/jpeg,image/png,image/webp,application/pdf">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Phân loại tài liệu:</label>
                        <select name="file_type" class="form-select form-select-sm">
                            <option value="x_ray">Phim X-Quang / CT / MRI / Siêu âm</option>
                            <option value="lab_result">Kết quả Xét nghiệm Máu / Sinh hóa</option>
                            <option value="prescription_scan">Scan đơn thuốc / Tài liệu cũ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-upload"></i> Lưu tệp mới
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-body p-4">
                @if($record->attachments->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder2-open fs-2 d-block text-secondary opacity-50 mb-1"></i>
                        <span>Chưa có tệp phim X-quang hoặc kết quả xét nghiệm nào được đính kèm.</span>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($record->attachments as $att)
                            <div class="col-sm-6 col-md-4">
                                <div class="card h-100 border shadow-none bg-white">
                                    <div class="position-relative text-center bg-light rounded-top p-2" style="height: 160px; overflow: hidden;">
                                        @if($att->is_image)
                                            <img src="{{ $att->url }}" alt="{{ $att->original_name }}" class="w-100 h-100 object-fit-cover rounded cursor-pointer" onclick="openLightbox('{{ $att->url }}', '{{ addslashes($att->original_name) }}')" style="cursor: zoom-in;" title="Bấm để xem ảnh lớn">
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75">
                                                <i class="bi bi-zoom-in"></i> Phóng to
                                            </span>
                                        @else
                                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-danger">
                                                <i class="bi bi-file-earmark-pdf-fill fs-1"></i>
                                                <span class="small fw-semibold mt-1">Tài liệu PDF</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                @if($att->file_type === 'x_ray')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">X-Quang / CĐHA</span>
                                                @elseif($att->file_type === 'lab_result')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">Xét nghiệm</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border small">Scan / Tài liệu</span>
                                                @endif
                                                <small class="text-muted font-monospace">{{ $att->formatted_size }}</small>
                                            </div>
                                            <div class="small fw-bold text-dark text-truncate" title="{{ $att->original_name }}">
                                                {{ $att->original_name }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                Tải lên: {{ $att->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>

                                        <div class="pt-2 mt-2 border-top d-flex justify-content-between align-items-center">
                                            <a href="{{ route('attachments.download', $att) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="Tải xuống tệp an toàn">
                                                <i class="bi bi-download"></i> Tải về
                                            </a>
                                            <form action="{{ route('doctor.records.attachments.destroy', [$record, $att]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tệp cận lâm sàng này khỏi bệnh án?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Xóa tệp">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Lightbox Preview -->
<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-bottom border-secondary py-2">
                <h6 class="modal-title text-white small text-truncate" id="lightboxTitle">Xem Hình Ảnh Cận Lâm Sàng</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="lightboxDownloadBtn" target="_blank" download class="btn btn-sm btn-outline-light py-1 px-2">
                        <i class="bi bi-download"></i> Tải về
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-2 text-center bg-black">
                <img src="" id="lightboxImage" class="img-fluid rounded" style="max-height: 80vh; object-fit: contain;" alt="Xem hình ảnh">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openLightbox(url, title) {
        const modalEl = document.getElementById('imageLightboxModal');
        const imgEl = document.getElementById('lightboxImage');
        const titleEl = document.getElementById('lightboxTitle');
        const downloadBtn = document.getElementById('lightboxDownloadBtn');

        imgEl.src = url;
        titleEl.textContent = title;
        downloadBtn.href = url;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endpush
@endsection
