<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Lai Thu Tiền Viện Phí - {{ $appointment->appointment_code }} - BV Tâm Anh</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
        }

        .receipt-card {
            max-width: 680px;
            margin: 2rem auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }

        .receipt-stamp {
            border: 2px dashed #0284c7;
            border-radius: 8px;
            padding: 8px 16px;
            display: inline-block;
            color: #0284c7;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .vietqr-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                max-width: 100% !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 15px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="no-print bg-white border-bottom py-2 px-3 sticky-top shadow-sm text-center">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2" style="max-width: 760px;">
            <a href="{{ route('reception.appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay lại quầy lễ tân
            </a>

            <!-- BHYT Route Toggle Options -->
            @if(!empty($appointment->patientProfile->insurance_code))
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('reception.appointments.receipt', [$appointment, 'route' => 'correct']) }}"
                       class="btn {{ ($routeParam ?? 'correct') === 'correct' ? 'btn-success fw-bold' : 'btn-outline-success' }}"
                       title="Áp dụng chi trả BHYT đúng tuyến / thông tuyến">
                        <i class="bi bi-check-circle me-1"></i> BHYT Đúng Tuyến
                    </a>
                    <a href="{{ route('reception.appointments.receipt', [$appointment, 'route' => 'incorrect']) }}"
                       class="btn {{ ($routeParam ?? '') === 'incorrect' ? 'btn-warning fw-bold' : 'btn-outline-warning' }}"
                       title="BHYT khám trái tuyến ngoại trú">
                        <i class="bi bi-exclamation-triangle me-1"></i> Trái Tuyến
                    </a>
                    <a href="{{ route('reception.appointments.receipt', [$appointment, 'route' => 'none']) }}"
                       class="btn {{ ($routeParam ?? '') === 'none' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}"
                       title="Không áp dụng BHYT cho ca khám này">
                        Tự Nguyện 100%
                    </a>
                </div>
            @endif

            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm fw-bold">
                    <i class="bi bi-printer-fill"></i> In Phiếu Thu (Ctrl + P)
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt Container -->
    <div class="receipt-card">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start pb-3 border-bottom mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-primary text-uppercase" style="letter-spacing: 0.5px;">BỆNH VIỆN ĐA KHOA TÂM ANH</h6>
                <div class="small text-muted">108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</div>
                <div class="small text-muted">Hotline CSKH: <strong>1800 6858</strong> &bull; Website: tamanhhospital.vn</div>
            </div>
            <div class="text-end">
                <div class="receipt-stamp">
                    <i class="bi bi-check2-circle me-1"></i> ĐÃ XÁC NHẬN THU PHÍ
                </div>
                <div class="small text-muted font-monospace mt-1">Mã BL: BL-{{ $appointment->appointment_code }}</div>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center my-3">
            <h4 class="fw-bold text-dark text-uppercase mb-1">BIÊN LAI THU TIỀN KHÁM BỆNH</h4>
            <div class="small text-muted">(Phiếu thu viện phí tạm ứng ngoại trú)</div>
        </div>

        <!-- Demographics -->
        <div class="row g-2 mb-3 small">
            <div class="col-8">
                Họ và tên bệnh nhân: <strong class="text-uppercase text-dark">{{ $appointment->patientProfile->full_name }}</strong>
            </div>
            <div class="col-4 text-end">
                Mã BN: <strong class="font-monospace text-primary">{{ $appointment->patientProfile->patient_code }}</strong>
            </div>
            <div class="col-6">
                Năm sinh / Giới tính: <strong>{{ $appointment->patientProfile->dob ? $appointment->patientProfile->dob->format('Y') : '--' }} ({{ $appointment->patientProfile->age_text }})</strong> &bull; <strong>{{ $appointment->patientProfile->gender_name }}</strong>
            </div>
            <div class="col-6 text-end">
                Số điện thoại: <strong class="font-monospace">{{ $appointment->patientProfile->phone }}</strong>
            </div>
            <div class="col-12">
                Địa chỉ: <span>{{ $appointment->patientProfile->address ?: 'Tại Hà Nội' }}</span>
            </div>
            <div class="col-6">
                Chuyên khoa khám: <strong>{{ $appointment->specialty ? $appointment->specialty->name : 'Khám chuyên khoa' }}</strong>
            </div>
            <div class="col-6 text-end">
                Bác sĩ phụ trách: <strong>{{ $appointment->doctor ? $appointment->doctor->full_name_with_title : '--' }}</strong> ({{ $appointment->doctor ? $appointment->doctor->room_number : '' }})
            </div>
            @if(isset($insuranceData) && $insuranceData['is_valid'])
                <div class="col-12 mt-2 pt-2 border-top">
                    <div class="p-2 bg-light rounded d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-1"><i class="bi bi-card-heading"></i> BHYT</span>
                            Mã thẻ: <strong class="font-monospace text-primary">{{ $insuranceData['code'] }}</strong>
                            &bull; <span class="text-muted">{{ $insuranceData['object_name'] }}</span>
                        </div>
                        <div>
                            <span class="badge {{ $insuranceData['is_correct_route'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $insuranceData['route_text'] }} (Mức hưởng: {{ $insuranceData['benefit_rate_pct'] }}%)
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Fee Items Table -->
        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th>Nội dung thanh toán / Dịch vụ khám</th>
                        <th class="text-center" style="width: 70px;">SL</th>
                        <th class="text-end" style="width: 140px;">Đơn giá</th>
                        <th class="text-end" style="width: 140px;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <div class="fw-semibold">Phí khám lâm sàng ngoại trú theo yêu cầu</div>
                            <small class="text-muted">Chuyên khoa: {{ $appointment->specialty ? $appointment->specialty->name : '--' }}</small>
                        </td>
                        <td class="text-center">01</td>
                        <td class="text-end font-monospace">{{ number_format($fee, 0, ',', '.') }} đ</td>
                        <td class="text-end fw-bold font-monospace text-primary">{{ number_format($fee, 0, ',', '.') }} đ</td>
                    </tr>
                </tbody>
                <tfoot>
                    @if(isset($insuranceData) && $insuranceData['is_valid'] && $insuranceData['benefit_rate_pct'] > 0)
                        <tr>
                            <td colspan="4" class="text-end text-muted">Tổng chi phí dịch vụ khám:</td>
                            <td class="text-end font-monospace text-muted">{{ number_format($fee, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-success fw-semibold">
                                <i class="bi bi-shield-check"></i> Quỹ BHYT chi trả ({{ $insuranceData['benefit_rate_pct'] }}%):
                            </td>
                            <td class="text-end font-monospace text-success fw-bold">-{{ $insuranceData['formatted_insurance_paid'] }}</td>
                        </tr>
                        <tr class="table-warning">
                            <td colspan="4" class="text-end fw-bold fs-6 text-danger">Người bệnh cùng chi trả thực tế:</td>
                            <td class="text-end fw-bold font-monospace fs-6 text-danger">{{ $insuranceData['formatted_patient_copay'] }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Tổng cộng tiền viện phí thu:</td>
                            <td class="text-end fw-bold font-monospace fs-6 text-danger">{{ number_format($payableAmount ?? $fee, 0, ',', '.') }} đ</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <div class="mb-4 small">
            <em>Số tiền bằng chữ: <strong>{{ $feeWords }}</strong></em>
        </div>

        <!-- Payment methods & VietQR -->
        <div class="row g-3 align-items-center mb-4 p-3 bg-light rounded border">
            <div class="col-md-7">
                <h6 class="fw-bold text-dark small text-uppercase mb-2">
                    <i class="bi bi-wallet2 text-primary me-1"></i> Phương Thức Thanh Toán:
                </h6>
                <div class="small d-flex flex-column gap-1 text-secondary">
                    <div>&bull; <strong>Tiền mặt tại quầy:</strong> Đã tiếp nhận tại quầy tiếp đón.</div>
                    <div>&bull; <strong>Chuyển khoản QR Napas 24/7:</strong> Miễn phí giao dịch qua mọi ứng dụng ngân hàng.</div>
                    <div>&bull; <strong>Nội dung CK:</strong> <code class="fw-bold">TAMANH {{ $appointment->appointment_code }}</code></div>
                </div>
            </div>
            <div class="col-md-5 text-center">
                <div class="vietqr-box bg-white shadow-sm d-inline-block">
                    <img src="{{ $vietQrUrl }}" alt="VietQR Napas 247" class="img-fluid rounded" style="max-height: 125px;">
                    <div class="small fw-bold text-muted mt-1" style="font-size: 0.7rem;">Quét thanh toán VietQR Napas 247</div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="row text-center mt-4 small">
            <div class="col-6">
                <div class="fw-bold text-muted">NGƯỜI NỘP TIỀN</div>
                <small class="text-muted">(Ký và ghi rõ họ tên)</small>
                <div style="height: 60px;"></div>
                <div class="fw-semibold text-dark">{{ $appointment->patientProfile->full_name }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</div>
                <div class="fw-bold text-primary">NGƯỜI THU TIỀN (LỄ TÂN)</div>
                <small class="text-muted">(Ký, đóng dấu thu tiền)</small>
                <div style="height: 60px;" class="d-flex align-items-center justify-content-center">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">
                        <i class="bi bi-shield-check"></i> ĐÃ THU ĐỦ
                    </span>
                </div>
                <div class="fw-semibold text-dark">{{ Auth::user()->name ?? 'Nhân viên Lễ tân' }}</div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center pt-3 border-top mt-4 text-muted" style="font-size: 0.72rem;">
            (Biên lai có giá trị làm chứng từ đối chiếu viện phí và khám chữa bệnh. Quý khách vui lòng giữ lại biên lai để làm thủ tục tiếp theo)
        </div>
    </div>

</body>
</html>
