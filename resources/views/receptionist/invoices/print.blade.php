<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Lai Thu Viện Phí - {{ $invoice->invoice_code }} - {{ $invoice->patientProfile->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            color: #000;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .invoice-sheet {
            max-width: 750px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .invoice-sheet {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 10px !important;
            }
            .no-print {
                display: none !important;
            }
        }
        .table-items th, .table-items td {
            padding: 6px 8px;
            border: 1px solid #444;
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4 shadow-sm">
        <i class="bi bi-printer-fill me-1"></i> In Biên Lai Thu Viện Phí
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm ms-2">
        Đóng cửa sổ
    </button>
</div>

<div class="invoice-sheet">
    <!-- Header -->
    <div class="row align-items-center pb-3 border-bottom mb-3">
        <div class="col-8">
            <h6 class="fw-bold text-uppercase mb-0" style="letter-spacing: 0.5px;">BỆNH VIỆN ĐA KHOA TÂM ANH</h6>
            <div style="font-size: 11px;">108 Phố Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</div>
            <div style="font-size: 11px;">Mã số thuế: <strong>0102345678</strong> &bull; Hotline: 1800 6858</div>
        </div>
        <div class="col-4 text-end">
            <div class="fw-bold font-monospace fs-6 text-primary">{{ $invoice->invoice_code }}</div>
            <div style="font-size: 10px;">MẪU SỐ: 01/BV-BHXH</div>
            <div style="font-size: 10px;">Ngày tạo: {{ $invoice->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <!-- Title -->
    <div class="text-center my-3">
        <h4 class="fw-bold text-uppercase mb-1">BIÊN LAI THU VIỆN PHÍ NGOẠI TRÚ</h4>
        <div class="fst-italic" style="font-size: 11.5px;">(Kiêm Bảng Kê Chi Phí Khám Bệnh, Chữa Bệnh Ngoại Trú Chuẩn Bộ Y Tế)</div>
        <div class="mt-1">
            @if($invoice->isPaid())
                <span class="badge bg-success text-white px-3 py-1 font-monospace">ĐÃ THANH TOÁN (HỢP LỆ)</span>
            @else
                <span class="badge bg-warning text-dark px-3 py-1 font-monospace">PHIẾU TẠM TÍNH (CHỜ THANH TOÁN)</span>
            @endif
        </div>
    </div>

    <!-- Patient Info -->
    <div class="p-2 border rounded bg-light mb-3" style="font-size: 12.5px; line-height: 1.6;">
        <div class="row g-1">
            <div class="col-7">
                Họ và tên người bệnh: <strong class="text-uppercase">{{ $invoice->patientProfile->full_name }}</strong>
            </div>
            <div class="col-5 text-end">
                Mã người bệnh: <strong class="font-monospace text-primary">{{ $invoice->patientProfile->patient_code }}</strong>
            </div>
            <div class="col-4">
                Năm sinh / Tuổi: <strong>{{ $invoice->patientProfile->dob ? $invoice->patientProfile->dob->format('Y') . ' (' . $invoice->patientProfile->dob->age . ' tuổi)' : '--' }}</strong>
            </div>
            <div class="col-3">
                Giới tính: <strong>{{ $invoice->patientProfile->gender_name }}</strong>
            </div>
            <div class="col-5 text-end">
                Mã thẻ BHYT: <strong>{{ $invoice->patientProfile->insurance_code ?: 'Không có' }}</strong>
            </div>
            <div class="col-12">
                Địa chỉ: {{ $invoice->patientProfile->address ?: 'Chưa cập nhật' }}
            </div>
            <div class="col-7">
                Bác sĩ khám: <strong>{{ $invoice->appointment && $invoice->appointment->doctor ? $invoice->appointment->doctor->full_name_with_title : 'Khoa Khám Bệnh' }}</strong>
            </div>
            <div class="col-5 text-end">
                Khoa phòng: <strong>{{ $invoice->appointment && $invoice->appointment->specialty ? $invoice->appointment->specialty->name : 'Ngoại trú' }}</strong>
            </div>
            <div class="col-12">
                Chẩn đoán: <strong>{{ $invoice->medicalRecord ? $invoice->medicalRecord->diagnosis : ($invoice->appointment->symptom_description ?? 'Khám bệnh theo yêu cầu') }}</strong>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-3">
        <table class="table-items w-100 mb-0" style="font-size: 12px;">
            <thead>
                <tr class="bg-light text-center fw-bold">
                    <th style="width: 40px;">STT</th>
                    <th>Nội dung chi phí danh mục y tế</th>
                    <th style="width: 50px;">ĐVT</th>
                    <th style="width: 50px;">SL</th>
                    <th style="width: 120px;" class="text-end">Đơn giá (VNĐ)</th>
                    <th style="width: 130px;" class="text-end">Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>Công khám chuyên khoa ngoại trú</td>
                    <td class="text-center">Lượt</td>
                    <td class="text-center">1</td>
                    <td class="text-end font-monospace">{{ number_format($invoice->consultation_fee, 0, ',', '.') }}</td>
                    <td class="text-end font-monospace">{{ number_format($invoice->consultation_fee, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->medication_fee > 0)
                    <tr>
                        <td class="text-center">2</td>
                        <td>Chi phí thuốc, dược phẩm điều trị ngoại trú</td>
                        <td class="text-center">Toa</td>
                        <td class="text-center">1</td>
                        <td class="text-end font-monospace">{{ number_format($invoice->medication_fee, 0, ',', '.') }}</td>
                        <td class="text-end font-monospace">{{ number_format($invoice->medication_fee, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($invoice->procedure_fee > 0)
                    <tr>
                        <td class="text-center">3</td>
                        <td>Thủ thuật / Can thiệp điều trị tại phòng khám</td>
                        <td class="text-center">Lần</td>
                        <td class="text-center">1</td>
                        <td class="text-end font-monospace">{{ number_format($invoice->procedure_fee, 0, ',', '.') }}</td>
                        <td class="text-end font-monospace">{{ number_format($invoice->procedure_fee, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">1. Tổng chi phí khám chữa bệnh:</td>
                    <td class="text-end font-monospace fw-bold">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">2. Quỹ Bảo hiểm Y tế (BHYT) thanh toán:</td>
                    <td class="text-end font-monospace fw-bold">-{{ number_format($invoice->insurance_covered_amount, 0, ',', '.') }} đ</td>
                </tr>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="5" class="text-end text-uppercase">3. SỐ TIỀN BỆNH NHÂN PHẢI THANH TOÁN (1 - 2):</td>
                    <td class="text-end font-monospace fs-6">{{ number_format($invoice->patient_co_pay_amount, 0, ',', '.') }} đ</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Payment info & Signatures -->
    <div class="row align-items-center pt-2" style="font-size: 11.5px;">
        <div class="col-7">
            <div>Phương thức thanh toán: <strong>{{ $invoice->payment_method_text }}</strong></div>
            <div>Trạng thái: <strong>{{ $invoice->isPaid() ? 'Đã thu đủ viện phí' : 'Chờ bệnh nhân nộp viện phí' }}</strong></div>
            <div class="mt-2 text-muted fst-italic">
                * Quý khách vui lòng kiểm tra lại biên lai trước khi rời quầy thu ngân. Hóa đơn điện tử tra cứu tại: https://tamanhhospital.vn/billing/lookup
            </div>
        </div>
        <div class="col-5 text-center">
            <div>Hà Nội, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</div>
            <strong class="d-block mt-1">NGƯỜI THU TIỀN / THU NGÂN</strong>
            <div style="height: 55px;"></div>
            <div class="fw-bold">{{ $invoice->receivedBy?->name ?? (Auth::user()?->name ?? 'Thu ngân Bệnh viện') }}</div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.print();
        }, 500);
    });
</script>
</body>
</html>
