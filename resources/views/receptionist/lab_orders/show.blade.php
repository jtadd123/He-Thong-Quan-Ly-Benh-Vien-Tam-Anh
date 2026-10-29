@extends('layouts.admin')

@section('title', 'Chi Tiết Phiếu Chỉ Định Cận Lâm Sàng - ' . $labOrder->order_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-slate-800">Phiếu Chỉ Định Cận Lâm Sàng</h4>
                <span class="badge bg-primary fs-6 font-monospace">{{ $labOrder->order_code }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Thời gian tạo: {{ $labOrder->ordered_at->format('H:i d/m/Y') }} • 
                Trạng thái: 
                @if($labOrder->status === 'completed')
                    <span class="text-success fw-bold">Đã có kết quả</span>
                @else
                    <span class="text-warning fw-bold">Đang xử lý</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.lab-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Danh sách
            </a>
            @if($labOrder->status === 'completed')
                <a href="{{ route('reception.lab-orders.report', $labOrder) }}" target="_blank" class="btn btn-primary fw-semibold shadow-sm">
                    <i class="bi bi-printer me-1"></i> In Kết Quả Điện Tử (A4)
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert" style="animation: pulse 1.5s infinite;">
            <i class="bi bi-exclamation-octagon-fill fs-2 text-danger"></i>
            <div>
                <strong class="d-block fs-6">{{ session('warning') }}</strong>
                <small>Hệ thống tự động lưu nhật ký và gửi cảnh báo đỏ khẩn cấp đến phòng khám chuyên khoa!</small>
            </div>
            <button onclick="playCriticalAlertSound()" class="btn btn-sm btn-danger ms-auto fw-bold text-nowrap">
                <i class="bi bi-volume-up-fill me-1"></i> Nghe Chuông Cảnh Báo
            </button>
        </div>
    @endif

    <!-- Patient Info Bar -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-3 border-end">
                <span class="text-muted small d-block">Họ và tên bệnh nhân</span>
                <strong class="fs-6 text-dark">{{ $labOrder->patientProfile->full_name }}</strong>
                <small class="text-muted d-block">{{ $labOrder->patientProfile->patient_code }}</small>
            </div>
            <div class="col-md-3 border-end">
                <span class="text-muted small d-block">Ngày sinh & Giới tính</span>
                <strong class="text-dark">{{ $labOrder->patientProfile->dob ? \Carbon\Carbon::parse($labOrder->patientProfile->dob)->format('d/m/Y') : 'Chưa rõ' }}</strong>
                <small class="text-muted d-block">{{ $labOrder->patientProfile->gender === 'male' ? 'Nam' : 'Nữ' }}</small>
            </div>
            <div class="col-md-4 border-end">
                <span class="text-muted small d-block">Chẩn đoán lâm sàng</span>
                <strong class="text-danger">{{ $labOrder->clinical_diagnosis }}</strong>
            </div>
            <div class="col-md-2 text-end">
                <span class="text-muted small d-block">Tổng chi phí</span>
                <strong class="fs-5 text-success">{{ number_format($labOrder->total_amount) }} đ</strong>
            </div>
        </div>
    </div>

    <!-- Test Items & Result Entry -->
    <div class="card border-0 shadow-sm p-4">
        <h6 class="fw-bold text-slate-800 border-bottom pb-3 mb-3">
            <i class="bi bi-card-checklist text-primary me-2"></i>Chi Tiết Kết Quả Xét Nghiệm & Chẩn Đoán Hình Ảnh
        </h6>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Tên Xét Nghiệm / CĐHA</th>
                        <th style="width: 140px;">Khoảng Tham Chiếu</th>
                        <th style="width: 90px;" class="text-center">Đơn Vị</th>
                        <th style="width: 180px;">Kết Quả Thực Đo</th>
                        <th style="width: 130px;" class="text-center">Đánh Giá</th>
                        <th>Ghi Chú Kỹ Thuật Viên</th>
                        <th style="width: 110px;" class="text-end">Cập Nhật</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labOrder->items as $index => $item)
                        <tr class="{{ $item->is_panic_alert ? 'table-danger' : '' }}">
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $item->labService->name }}</strong>
                                <small class="text-muted font-monospace">{{ $item->labService->code }} • {{ $item->labService->execution_room }}</small>
                                @if($item->labService->panic_low !== null || $item->labService->panic_high !== null)
                                    <span class="badge bg-light text-danger border border-danger-subtle ms-1" style="font-size: 0.7rem;">
                                        Panic: &lt;{{ $item->labService->panic_low }} hoặc &gt;{{ $item->labService->panic_high }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-secondary">{{ $item->labService->normal_range ?: 'Bình thường' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $item->labService->unit ?: '--' }}</span>
                            </td>
                            <td>
                                @if($item->result_value)
                                    <strong class="fs-6 {{ $item->is_panic_alert ? 'text-danger' : 'text-primary' }}">
                                        {{ $item->result_value }}
                                    </strong>
                                    @if($item->dicom_image_url)
                                        <a href="{{ $item->dicom_image_url }}" target="_blank" class="d-block small text-info text-decoration-none mt-1">
                                            <i class="bi bi-image me-1"></i>Xem ảnh DICOM/PACS
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic small">Chưa có kết quả</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->is_panic_alert)
                                    <span class="badge bg-danger p-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>NGUY HIỂM (PANIC)</span>
                                @elseif($item->completed_at)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Bình thường</span>
                                @else
                                    <span class="badge bg-secondary">Chờ đo</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->technician_notes ?: 'Không có' }}</small>
                                @if($item->technician)
                                    <small class="text-secondary d-block mt-1">KTV: {{ $item->technician->name }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalItem{{ $item->id }}">
                                    <i class="bi bi-pencil"></i> Nhập
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Nhập Kết Quả cho từng item -->
                        <div class="modal fade" id="modalItem{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('reception.lab-orders.items.update', $item) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Nhập Kết Quả: {{ $item->labService->name }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kết quả đo được <span class="text-danger">*</span></label>
                                                <input type="text" name="result_value" class="form-control" value="{{ $item->result_value }}" required placeholder="Ví dụ: 5.4 hoặc Không thấy thâm nhiễm...">
                                                <small class="text-muted">Đơn vị: <strong>{{ $item->labService->unit ?: 'Không' }}</strong> | Khoảng chuẩn: <strong>{{ $item->labService->normal_range }}</strong></small>
                                            </div>

                                            @if($item->labService->category === 'imaging')
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Đường dẫn ảnh DICOM / PACS (Nếu có)</label>
                                                    <input type="url" name="dicom_image_url" class="form-control" value="{{ $item->dicom_image_url }}" placeholder="https://pacs.tamanh.vn/dicom/study/...">
                                                </div>
                                            @endif

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Ghi chú của Kỹ thuật viên / Bác sĩ</label>
                                                <textarea name="technician_notes" class="form-control" rows="2" placeholder="Nhận xét hình thái hoặc lưu ý đặc biệt...">{{ $item->technician_notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Lưu Kết Quả</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Chuông cảnh báo Critical Panic Value (Web Audio API)
    function playCriticalAlertSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) {
            console.log('AudioContext not allowed without gesture');
        }
    }

    @if($labOrder->items->where('is_panic_alert', true)->count() > 0)
        window.addEventListener('DOMContentLoaded', () => {
            playCriticalAlertSound();
        });
    @endif
</script>
@endsection
