<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Tem Nhãn Hộp Thuốc Thông Minh - {{ $record->record_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #334155;
            margin: 0;
            padding: 20px;
        }

        /* Khổ nhãn dán tiêu chuẩn 75mm x 50mm */
        .sticker-label {
            width: 75mm;
            min-height: 50mm;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 2.5mm 3mm;
            box-sizing: border-box;
            position: relative;
            margin: 0 auto 15px auto;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            page-break-after: always;
            color: #0f172a;
        }

        .sticker-header {
            border-bottom: 1.5px solid #0284c7;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
        }

        .hospital-title {
            font-size: 7.5pt;
            font-weight: 800;
            color: #0284c7;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .dept-subtitle {
            font-size: 5.5pt;
            color: #64748b;
            font-weight: 600;
        }

        .patient-row {
            font-size: 7pt;
            line-height: 1.2;
            margin-bottom: 1.5mm;
        }

        .drug-name-row {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 1.5mm 2mm;
            margin-bottom: 2mm;
        }

        .drug-title {
            font-size: 8.5pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        /* Bảng liều dùng 4 khung Sáng - Trưa - Chiều - Tối */
        .dosing-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5mm;
            margin-bottom: 2mm;
        }

        .dose-slot {
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            text-align: center;
            padding: 1mm 0.5mm;
            background: #ffffff;
        }

        .dose-slot.active {
            border-color: #0284c7;
            background: #f0f9ff;
        }

        .dose-icon {
            font-size: 8pt;
            line-height: 1;
            display: block;
        }

        .dose-label {
            font-size: 5.5pt;
            color: #64748b;
            font-weight: 600;
            display: block;
            margin-top: 0.5mm;
        }

        .dose-qty {
            font-size: 8pt;
            font-weight: 800;
            color: #0284c7;
            line-height: 1;
        }

        /* Tem cảnh báo phụ lâm sàng (Auxiliary Caution Badges) */
        .caution-banner {
            border-radius: 3px;
            padding: 1mm 1.5mm;
            font-size: 6pt;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1.5mm;
            margin-bottom: 1.5mm;
            color: white;
            line-height: 1.1;
        }

        .sticker-footer {
            font-size: 5pt;
            color: #64748b;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 1mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* In ấn máy in nhiệt decal */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .sticker-label {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                width: 75mm !important;
                height: 50mm !important;
                page-break-after: always !important;
            }
            @page {
                size: 75mm 50mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Top Bar -->
    <div class="no-print position-fixed top-0 start-0 end-0 bg-dark text-white p-2 d-flex justify-content-between align-items-center shadow-lg" style="z-index: 1050;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-6"><i class="bi bi-tag-fill me-1"></i>Tem Nhãn Dán Hộp Thuốc (75mm x 50mm)</span>
            <small class="text-white-50">Tổng số tem: <strong>{{ count($labels) }} tem</strong> • Đơn thuốc: <strong>{{ $record->record_code }}</strong></small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-success px-3 fw-bold">
                <i class="bi bi-printer-fill me-1"></i> In Ngay (Tất Cả Tem)
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                <i class="bi bi-x-lg"></i> Đóng
            </button>
        </div>
    </div>

    <div style="margin-top: 50px;">
        @foreach($labels as $label)
            <div class="sticker-label">
                <!-- Header -->
                <div class="sticker-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="hospital-title">{{ $label['hospital_name'] }}</div>
                        <div class="dept-subtitle">{{ $label['department_name'] }}</div>
                    </div>
                    <div class="text-end">
                        <span style="font-size: 5.5pt; font-family: monospace; font-weight: 700; color: #0284c7;">{{ $label['record_code'] }}</span>
                    </div>
                </div>

                <!-- Patient Row -->
                <div class="patient-row d-flex justify-content-between">
                    <div>
                        BN: <strong style="font-size: 7.5pt; text-transform: uppercase;">{{ $label['patient_name'] }}</strong>
                    </div>
                    <div>
                        Mã: <strong style="font-family: monospace;">{{ $label['patient_code'] }}</strong> ({{ $label['patient_gender'] }})
                    </div>
                </div>

                <!-- Drug Name & Spec -->
                <div class="drug-name-row">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="drug-title">{{ $label['medication_name'] }}</div>
                        <span class="badge bg-dark" style="font-size: 6pt;">{{ $label['dosage'] }}</span>
                    </div>
                    @if($label['instructions'])
                        <div style="font-size: 6pt; color: #475569; margin-top: 0.5mm;">
                            <em>HD: {{ $label['instructions'] }} (Dùng {{ $label['duration_days'] }} ngày)</em>
                        </div>
                    @endif
                </div>

                <!-- 4-Slot Dosing Matrix (Sáng - Trưa - Chiều - Tối) -->
                <div class="dosing-grid">
                    <div class="dose-slot {{ $label['dosing']['morning'] > 0 ? 'active' : '' }}">
                        <span class="dose-icon">🌅</span>
                        <span class="dose-label">SÁNG</span>
                        <span class="dose-qty">{{ $label['dosing']['morning'] > 0 ? $label['dosing']['morning'] . 'v' : '-' }}</span>
                    </div>
                    <div class="dose-slot {{ $label['dosing']['noon'] > 0 ? 'active' : '' }}">
                        <span class="dose-icon">☀️</span>
                        <span class="dose-label">TRƯA</span>
                        <span class="dose-qty">{{ $label['dosing']['noon'] > 0 ? $label['dosing']['noon'] . 'v' : '-' }}</span>
                    </div>
                    <div class="dose-slot {{ $label['dosing']['evening'] > 0 ? 'active' : '' }}">
                        <span class="dose-icon">🌇</span>
                        <span class="dose-label">CHIỀU</span>
                        <span class="dose-qty">{{ $label['dosing']['evening'] > 0 ? $label['dosing']['evening'] . 'v' : '-' }}</span>
                    </div>
                    <div class="dose-slot {{ $label['dosing']['night'] > 0 ? 'active' : '' }}">
                        <span class="dose-icon">🌙</span>
                        <span class="dose-label">TỐI</span>
                        <span class="dose-qty">{{ $label['dosing']['night'] > 0 ? $label['dosing']['night'] . 'v' : '-' }}</span>
                    </div>
                </div>

                <!-- Auxiliary Caution Badges -->
                @foreach($label['cautions'] as $caution)
                    <div class="caution-banner" style="background-color: {{ $caution['color'] }};">
                        <i class="bi {{ $caution['icon'] }}"></i>
                        <span>{{ $caution['badge_text'] }}</span>
                    </div>
                @endforeach

                <!-- Footer -->
                <div class="sticker-footer">
                    <div>
                        BS: {{ $label['doctor_name'] }} • Ngày cấp: {{ $label['dispense_date'] }}
                    </div>
                    <div>
                        <i class="bi bi-qr-code text-primary"></i> Quét tra cứu
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
