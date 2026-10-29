<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Vận Đơn Giao Thuốc - {{ $delivery->waybill_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            background: #cbd5e1;
            margin: 0;
            padding: 20px;
        }

        .waybill-card {
            max-width: 148mm; /* A5 */
            min-height: 210mm;
            background: #fff;
            margin: 0 auto;
            border: 2px dashed #0284c7;
            padding: 10mm;
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .barcode-box {
            text-align: center;
            border: 1px solid #000;
            padding: 4px;
            font-family: monospace;
            font-size: 16pt;
            letter-spacing: 4px;
            font-weight: bold;
            background: #f8fafc;
        }

        .cold-badge {
            background: #0284c7;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            font-size: 10.5pt;
            margin: 8px 0;
            border-radius: 4px;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .waybill-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 8mm !important;
            }
            @page {
                size: A5 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            🖨️ In Phiếu Vận Đơn (A5/A6)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">
            Đóng
        </button>
    </div>

    <div class="waybill-card">
        <!-- Header -->
        <table style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 8px;">
            <tr>
                <td style="width: 60%;">
                    <strong style="font-size: 12pt; color: #0284c7;">BV ĐA KHOA TÂM ANH</strong><br>
                    <small>KHOA DƯỢC - NHÀ THUỐC NGOẠI TRÚ</small><br>
                    <small style="font-size: 8pt;">108 Hoàng Như Tiếp, Long Biên, Hà Nội</small>
                </td>
                <td style="width: 40%; text-align: right;">
                    <span style="font-size: 9pt; font-weight: bold; text-transform: uppercase;">PHIẾU GỬI HÀNG DƯỢC</span><br>
                    <small style="color: #64748b;">Đơn vị: {{ $delivery->carrier_name_formatted }}</small>
                </td>
            </tr>
        </table>

        <!-- Barcode Box -->
        <div class="barcode-box mb-2">
            *{{ $delivery->waybill_number }}*
        </div>
        <div style="text-align: center; font-size: 8.5pt; color: #64748b; margin-bottom: 8px;">
            Mã Tracking tra cứu: <strong>{{ $delivery->tracking_code }}</strong>
        </div>

        @if($delivery->requires_cold_chain)
            <div class="cold-badge">
                ❄️ HÀNG BẢO QUẢN LẠNH (2°C - 8°C) - VẬN CHUYỂN BẰNG THÙNG XỐP ĐÁ GEL
            </div>
        @endif

        <!-- Sender & Recipient -->
        <table style="width: 100%; border: 1px solid #000; margin-bottom: 8px; font-size: 10pt;">
            <tr>
                <td style="width: 50%; padding: 6px; border-right: 1px solid #000; vertical-align: top;">
                    <strong>NGƯỜI GỬI (FROM):</strong><br>
                    Nhà Thuốc BV Đa Khoa Tâm Anh<br>
                    SĐT: 1800 6858 / 024 3872 3872<br>
                    Địa chỉ: 108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội
                </td>
                <td style="width: 50%; padding: 6px; vertical-align: top; background: #f8fafc;">
                    <strong>NGƯỜI NHẬN (TO):</strong><br>
                    <strong style="font-size: 11pt; text-transform: uppercase;">{{ $delivery->recipient_name }}</strong><br>
                    SĐT: <strong style="font-size: 11pt;">{{ $delivery->recipient_phone }}</strong><br>
                    Địa chỉ: <strong>{{ $delivery->delivery_address }}</strong>
                </td>
            </tr>
        </table>

        <!-- Medication List -->
        <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 4px;">CHI TIẾT THUỐC ĐÓNG GÓI BÊN TRONG:</div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 9pt; margin-bottom: 8px;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="border: 1px solid #000; padding: 4px; text-align: center; width: 8%;">#</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: left;">Tên Thuốc / Hàm Lượng</th>
                    <th style="border: 1px solid #000; padding: 4px; text-align: center; width: 18%;">Thời Gian Dùng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($delivery->medicalRecord->prescriptions as $p)
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $loop->iteration }}</td>
                        <td style="border: 1px solid #000; padding: 4px;">
                            <strong>{{ $p->medication_name }}</strong> ({{ $p->dosage }})
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $p->duration_days }} ngày</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Delivery instructions & Signatures -->
        <table style="width: 100%; font-size: 9pt; margin-top: 15px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <strong>DƯỢC SĨ KIỂM SOÁT ĐÓNG GÓI</strong><br>
                    <small><em>(Ký & ghi rõ họ tên)</em></small>
                    <div style="height: 45px;"></div>
                    <span>Dược Sĩ Quầy Ngoại Trú</span>
                </td>
                <td style="width: 50%; text-align: center;">
                    <strong>NGƯỜI NHẬN THUỐC</strong><br>
                    <small><em>(Kiểm tra nguyên vẹn tem & ký nhận)</em></small>
                    <div style="height: 45px;"></div>
                    <span>{{ $delivery->recipient_name }}</span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
