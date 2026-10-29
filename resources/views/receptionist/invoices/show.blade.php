@extends('layouts.admin')

@section('title', 'Chi Tiết Hóa Đơn Viện Phí #' . $invoice->invoice_code)

@section('content')
<x-page-header title="Hóa Đơn Viện Phí: {{ $invoice->invoice_code }}" subtitle="Bảng kê chi phí khám chữa bệnh ngoại trú & Cổng thanh toán VietQR Napas 247.">
    <x-slot:actions>
        <a href="{{ route('reception.invoices.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Sổ hóa đơn
        </a>
        <a href="{{ route('reception.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark d-inline-flex align-items-center gap-1">
            <i class="bi bi-printer"></i> In phiếu thu điện tử
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <!-- Left Column: Invoice Details -->
    <div class="col-lg-8">
        <!-- Status Banner -->
        @php $statusBadge = $invoice->payment_status_badge; @endphp
        <div class="alert {{ $invoice->isPaid() ? 'alert-success border-success' : 'alert-warning border-warning' }} d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <i class="bi {{ $statusBadge['icon'] }} fs-2 text-{{ $statusBadge['color'] }}"></i>
                <div>
                    <h5 class="fw-bold mb-0 text-{{ $statusBadge['color'] }}">
                        {{ $statusBadge['label'] }}
                    </h5>
                    <small class="text-muted">
                        @if($invoice->isPaid())
                            Đã thanh toán lúc {{ $invoice->paid_at->format('H:i d/m/Y') }} &bull; Thu ngân: {{ $invoice->receivedBy?->name ?? 'Thu ngân trực' }} &bull; Hình thức: {{ $invoice->payment_method_text }}
                        @else
                            Bệnh nhân cần thanh toán viện phí trước khi nhận thuốc hoặc xuất viện.
                        @endif
                    </small>
                </div>
            </div>
            <span class="fs-4 fw-bold font-monospace text-{{ $statusBadge['color'] }}">
                {{ number_format($invoice->patient_co_pay_amount, 0, ',', '.') }} VNĐ
            </span>
        </div>

        <!-- Patient Demographics & Insurance Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-vcard text-primary me-2"></i> Thông Tin Bệnh Nhân & Chế Độ Bảo Hiểm Y Tế
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Họ và tên:</span>
                        <strong class="fs-5 text-dark">{{ $invoice->patientProfile->full_name }}</strong>
                        <div class="text-muted small">Mã BN: <span class="font-monospace text-primary fw-bold">{{ $invoice->patientProfile->patient_code }}</span> | Giới tính: {{ $invoice->patientProfile->gender_name }} | {{ $invoice->patientProfile->dob ? $invoice->patientProfile->dob->age . ' tuổi' : '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Mã Thẻ BHYT:</span>
                        @if($invoice->patientProfile->insurance_code)
                            <span class="badge bg-success-subtle text-success border border-success font-monospace fs-6 px-2 py-1">
                                {{ $invoice->patientProfile->insurance_code }}
                            </span>
                            <div class="text-muted small mt-1">Đã áp dụng giám định đồng chi trả theo quy định BYT</div>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">Không đăng ký BHYT (Khám dịch vụ tự túc 100%)</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Bác sĩ khám:</span>
                        <strong>{{ $invoice->appointment && $invoice->appointment->doctor ? $invoice->appointment->doctor->full_name_with_title : 'Khoa Khám Bệnh' }}</strong>
                        <div class="text-muted small">{{ $invoice->appointment && $invoice->appointment->specialty ? $invoice->appointment->specialty->name : 'Ngoại trú' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Chẩn đoán y khoa:</span>
                        <span class="fw-semibold text-danger">{{ $invoice->medicalRecord ? $invoice->medicalRecord->diagnosis : ($invoice->appointment->symptom_description ?? 'Khám ngoại trú') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-check text-primary me-2"></i> Bảng Kê Chi Tiết Viện Phí Ngoại Trú
                </h6>
                <span class="badge bg-light text-muted border">Mẫu số 01/BV - BYT</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">STT</th>
                            <th>Nội Dung Chi Phí Khám Chữa Bệnh</th>
                            <th class="text-center" style="width: 80px;">SL</th>
                            <th class="text-end" style="width: 140px;">Đơn Giá (VNĐ)</th>
                            <th class="text-end" style="width: 140px;">Thành Tiền (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-bold text-muted">1</td>
                            <td>
                                <strong>Công Khám Chuyên Khoa</strong>
                                <div class="text-muted">Bác sĩ chuyên khoa khám lâm sàng & tư vấn điều trị</div>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-end font-monospace">{{ number_format($invoice->consultation_fee, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace fw-semibold">{{ number_format($invoice->consultation_fee, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->medication_fee > 0)
                            <tr>
                                <td class="text-center fw-bold text-muted">2</td>
                                <td>
                                    <strong>Tiền Thuốc & Dược Phẩm Ngoại Trú</strong>
                                    <div class="text-muted">Cấp phát theo Đơn thuốc Bác sĩ chỉ định</div>
                                </td>
                                <td class="text-center">1 gói</td>
                                <td class="text-end font-monospace">{{ number_format($invoice->medication_fee, 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-semibold">{{ number_format($invoice->medication_fee, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if($invoice->procedure_fee > 0)
                            <tr>
                                <td class="text-center fw-bold text-muted">3</td>
                                <td>
                                    <strong>Thủ Thuật / Can Thiệp Y Tế</strong>
                                </td>
                                <td class="text-center">1</td>
                                <td class="text-end font-monospace">{{ number_format($invoice->procedure_fee, 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-semibold">{{ number_format($invoice->procedure_fee, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Tổng chi phí phát sinh:</td>
                            <td class="text-end font-monospace fw-bold">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-success fw-bold">
                                Quỹ Bảo hiểm Y tế thanh toán (BHYT):
                            </td>
                            <td class="text-end font-monospace text-success fw-bold">-{{ number_format($invoice->insurance_covered_amount, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr class="table-warning border-top border-2 border-warning">
                            <td colspan="4" class="text-end text-danger fw-bold fs-6">
                                BỆNH NHÂN CÙNG CHI TRẢ THỰC TẾ:
                            </td>
                            <td class="text-end font-monospace text-danger fw-bold fs-5">
                                {{ number_format($invoice->patient_co_pay_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: VietQR & Payment Processing -->
    <div class="col-lg-4">
        <!-- VietQR Box -->
        <div class="card border-0 shadow-sm mb-4 border-top border-4 border-primary">
            <div class="card-header bg-white py-3 border-bottom text-center">
                <span class="badge bg-primary px-3 py-1 mb-1 font-monospace">NAPAS 247 &bull; VIETQR</span>
                <h6 class="fw-bold mb-0 text-dark">Thanh Toán Quét Mã QR Ngân Hàng</h6>
            </div>
            <div class="card-body p-4 text-center">
                <p class="small text-muted mb-3">
                    Mở ứng dụng Ngân hàng (VCB, MB, Techcombank, Momo...) quét mã để thanh toán đúng số tiền <strong>{{ $paymentDetails['formatted_amount'] }}</strong>.
                </p>

                <!-- Dynamic VietQR Image -->
                <div class="p-2 bg-white border rounded shadow-sm d-inline-block mb-3">
                    <img src="{{ $paymentDetails['qr_image_url'] }}" alt="VietQR Payment" class="img-fluid" style="max-width: 240px;">
                </div>

                <div class="p-2 bg-light rounded text-start small font-monospace mb-3">
                    <div class="text-muted">Ngân hàng: <strong>VietinBank (ICB)</strong></div>
                    <div class="text-muted">Số TK: <strong>{{ $paymentDetails['account_number'] }}</strong></div>
                    <div class="text-muted">Chủ TK: <strong>{{ $paymentDetails['account_name'] }}</strong></div>
                    <div class="text-danger mt-1">Nội dung: <strong>{{ $paymentDetails['transfer_content'] }}</strong></div>
                </div>

                @if(!$invoice->isPaid())
                    <!-- Confirm Payment Modal Trigger -->
                    <button type="button" class="btn btn-success btn-sm w-100 fw-bold py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmPaymentModal">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Đã Thu Tiền Viện Phí
                    </button>
                @else
                    <div class="badge bg-success-subtle text-success border border-success p-2 w-100 fs-6">
                        <i class="bi bi-check-all me-1"></i> ĐÃ HOÀN TẤT THANH TOÁN
                    </div>
                @endif
            </div>
        </div>

        <!-- Reception Staff Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <h6 class="fw-bold small text-uppercase text-muted mb-2">Thao Tác Nhanh</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('reception.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-printer me-1"></i> In Phiếu Thu Viện Phí Chuẩn
                    </a>
                    @if($invoice->appointment)
                        <a href="{{ route('reception.appointments.print', $invoice->appointment) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-ticket-perforated me-1"></i> Xem Phiếu Khám
                        </a>
                    @endif
                    <a href="{{ route('reception.patients.show', $invoice->patientProfile) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-person me-1"></i> Mở Hồ Sơ Bệnh Nhân
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirm Payment -->
@if(!$invoice->isPaid())
<div class="modal fade" id="confirmPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('reception.invoices.confirm-payment', $invoice) }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-cash-coin me-1"></i> Xác Nhận Thu Viện Phí Ngoại Trú
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border p-3 mb-3">
                    <div class="small text-muted">Bệnh nhân:</div>
                    <strong class="text-dark fs-6">{{ $invoice->patientProfile->full_name }}</strong>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Số tiền cần thu:</span>
                        <span class="fs-5 fw-bold font-monospace text-danger">{{ number_format($invoice->patient_co_pay_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Hình Thức Thu Tiền: <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select form-select-sm" required>
                        <option value="vietqr">Chuyển khoản VietQR (Napas 247)</option>
                        <option value="cash" selected>Tiền mặt tại quầy tiếp đón</option>
                        <option value="card">Thẻ ngân hàng / Quẹt POS</option>
                        <option value="insurance_direct">BHYT bảo lãnh trực tiếp</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Ghi chú thu phí:</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="VD: Bệnh nhân đã thanh toán đầy đủ tiền mặt..." value="{{ $invoice->notes }}">
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold">
                    <i class="bi bi-check-lg me-1"></i> Hoàn Tất Thu Phí
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
