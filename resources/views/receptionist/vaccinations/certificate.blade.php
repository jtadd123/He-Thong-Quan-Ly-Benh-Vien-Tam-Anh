<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giấy Xác Nhận Tiêm Chủng - {{ $record->patientProfile->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f1f5f9;
            padding: 20px;
            font-size: 13pt;
        }

        .cert-paper {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm 20mm 20mm 25mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
            border: 2px solid #0284c7;
        }

        .header-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
        }

        .header-en {
            font-size: 10pt;
            font-style: italic;
            color: #475569;
        }

        .main-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin: 20px 0 5px 0;
        }

        .main-title-en {
            text-align: center;
            font-size: 11pt;
            font-style: italic;
            color: #64748b;
            margin-bottom: 25px;
        }

        .info-grid {
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .vac-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-size: 10.5pt;
        }

        .vac-table td {
            font-size: 11pt;
            vertical-align: middle;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .cert-paper {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                max-width: 100% !important;
                padding: 15mm !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            🖨️ In Giấy Chứng Nhận Tiêm Chủng (A4)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">
            Đóng
        </button>
    </div>

    <div class="cert-paper">
        <!-- Header -->
        <table style="width: 100%; border-bottom: 1.5px solid #0284c7; padding-bottom: 10px; margin-bottom: 15px;">
            <tr>
                <td style="width: 20%; text-align: center;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #0284c7; color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 24pt; font-weight: bold;">
                        +
                    </div>
                </td>
                <td style="width: 80%;">
                    <div class="header-title">BỆNH VIỆN ĐA KHOA TÂM ANH - TRUNG TÂM TIÊM CHỦNG</div>
                    <div class="header-en">TAM ANH GENERAL HOSPITAL - VACCINATION & IMMUNIZATION CENTER</div>
                    <div style="font-size: 9.5pt; color: #334155;">108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội • Hotline: 1800 6858</div>
                </td>
            </tr>
        </table>

        <!-- Certificate Title -->
        <div class="main-title">GIẤY XÁC NHẬN TIÊM CHỦNG NGOẠI TRÚ</div>
        <div class="main-title-en">CERTIFICATE OF VACCINATION OR PROPHYLAXIS</div>

        <!-- Personal Information -->
        <div class="info-grid">
            <table style="width: 100%; line-height: 1.6;">
                <tr>
                    <td style="width: 65%;">
                        Họ và tên / <em>Full name</em>: <strong style="text-transform: uppercase; font-size: 13.5pt;">{{ $record->patientProfile->full_name }}</strong>
                    </td>
                    <td style="width: 35%;">
                        Giới tính / <em>Gender</em>: <strong>{{ $record->patientProfile->gender_name }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        Ngày sinh / <em>Date of birth</em>: <strong>{{ $record->patientProfile->formatted_dob }}</strong> ({{ $record->patientProfile->age_text }})
                    </td>
                    <td>
                        Mã người bệnh / <em>PID</em>: <strong>{{ $record->patientProfile->patient_code }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        Số CCCD / Hộ chiếu / <em>ID/Passport</em>: <strong>{{ $record->patientProfile->identity_card ?: 'Chưa cập nhật' }}</strong>
                    </td>
                    <td>
                        Số điện thoại / <em>Phone</em>: <strong>{{ $record->patientProfile->phone }}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Địa chỉ cư trú / <em>Address</em>: {{ $record->patientProfile->address ?: 'TP. Hà Nội, Việt Nam' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Vaccination History Table -->
        <div style="font-weight: bold; margin-bottom: 8px; text-transform: uppercase; font-size: 11pt;">
            LỊCH SỬ TIÊM CHỦNG ĐÃ THỰC HIỆN / <em>RECORD OF IMMUNIZATION:</em>
        </div>

        <table class="table table-bordered vac-table" style="border: 1px solid #000;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Tên Vắc-xin<br><em>Vaccine Name</em></th>
                    <th style="width: 25%;">Bệnh phòng ngừa<br><em>Target Disease</em></th>
                    <th style="width: 12%;">Số lô<br><em>Lot/Batch</em></th>
                    <th style="width: 15%;">Ngày tiêm<br><em>Date (dd/mm/yy)</em></th>
                    <th style="width: 18%;">Hẹn mũi sau<br><em>Next Dose</em></th>
                </tr>
            </thead>
            <tbody>
                @foreach($allRecords as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $item->vaccine->name }}</strong><br>
                            <small class="text-muted">{{ $item->vaccine->manufacturer }} ({{ $item->vaccine->origin_country }})</small>
                        </td>
                        <td>{{ $item->vaccine->disease_prevented }}</td>
                        <td class="text-center font-monospace">{{ $item->batch_number }}</td>
                        <td class="text-center"><strong>{{ $item->administered_at->format('d/m/Y') }}</strong></td>
                        <td class="text-center text-primary">
                            {{ $item->next_dose_due_date ? $item->next_dose_due_date->format('d/m/Y') : 'Hoàn thành' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="font-size: 10pt; font-style: italic; color: #475569; margin-top: 10px;">
            * Người bệnh đã được khám sàng lọc trước tiêm và hoàn thành đủ 30 phút theo dõi phản ứng sau tiêm tại cơ sở y tế theo đúng quy định Thông tư Bộ Y Tế.
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div style="width: 45%;">
                <div style="font-size: 9pt; color: #64748b; margin-bottom: 5px;">Mã xác thực điện tử:</div>
                <div style="display: inline-block; padding: 5px; border: 1px dashed #cbd5e1; border-radius: 4px;">
                    <div style="font-size: 8pt; font-family: monospace;">[QR VERIFIED]</div>
                    <div style="font-size: 7.5pt; color: #0284c7;">{{ $record->record_code }}</div>
                </div>
            </div>
            <div style="width: 55%;">
                <div style="font-style: italic; font-size: 10.5pt;">Hà Nội, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</div>
                <div style="font-weight: bold; text-transform: uppercase; margin-top: 3px;">TRƯỞNG TRUNG TÂM TIÊM CHỦNG</div>
                <div style="font-size: 9pt; font-style: italic;">(Ký, đóng dấu / Signature & Seal)</div>
                <div style="height: 70px;"></div>
                <strong style="text-transform: uppercase;">BS. CKII. NGUYỄN VĂN HƯNG</strong>
            </div>
        </div>
    </div>

</body>
</html>
