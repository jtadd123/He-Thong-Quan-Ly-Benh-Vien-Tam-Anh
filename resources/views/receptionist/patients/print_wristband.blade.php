<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vòng Tay Nhận Diện Người Bệnh - {{ $patient->full_name }} ({{ $patient->patient_code }})</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .no-print-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* 
           Hospital Medical Wristband Standard Dimensions:
           Length: 250mm, Height: 25mm (Zebra Z-Band Direct / Brother Healthcare)
        */
        .wristband-canvas {
            width: 250mm;
            height: 25mm;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            display: flex;
            align-items: stretch;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* 1. Left Attachment Tab */
        .tab-securing {
            width: 35mm;
            background: #f8fafc;
            border-right: 1px dashed #cbd5e1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* 2. Main Patient Identification Zone */
        .patient-info-zone {
            flex-grow: 1;
            padding: 2mm 4mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4mm;
        }

        .hospital-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid #e2e8f0;
            padding-right: 3mm;
            min-width: 32mm;
        }

        .hospital-name {
            font-size: 9px;
            font-weight: 900;
            color: #0284c7;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .hospital-sub {
            font-size: 7px;
            color: #64748b;
            font-weight: 600;
        }

        .patient-demographics {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-grow: 1;
        }

        .patient-name {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.1;
        }

        .patient-details-row {
            display: flex;
            align-items: center;
            gap: 3mm;
            font-size: 8.5px;
            color: #334155;
            margin-top: 1mm;
        }

        .code-badge {
            font-family: monospace;
            font-weight: 800;
            color: #0284c7;
            font-size: 9.5px;
        }

        .blood-badge {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 800;
            font-size: 8.5px;
        }

        /* 3. Barcode Zone */
        .barcode-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-left: 2mm;
        }

        .barcode-text {
            font-family: 'Libre Barcode 128', cursive;
            font-size: 32px;
            line-height: 0.8;
            color: #000000;
            white-space: nowrap;
        }

        .barcode-digits {
            font-family: monospace;
            font-size: 8px;
            color: #475569;
            font-weight: 700;
        }

        /* 4. Safety Allergy Alert Zone (Red Warning Strip) */
        @if($patient->hasAllergies())
        .allergy-alert-strip {
            width: 42mm;
            background: #dc2626;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1mm 2mm;
            text-align: center;
            line-height: 1.1;
        }

        .allergy-title {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .allergy-sub {
            font-size: 7.5px;
            font-weight: 700;
            margin-top: 1px;
            max-width: 38mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @endif

        /* 5. Right Clip Tab */
        .tab-tail {
            width: 25mm;
            background: #f8fafc;
            border-left: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #94a3b8;
            font-weight: 600;
        }

        @media print {
            @page {
                size: 250mm 25mm landscape;
                margin: 0;
            }

            body {
                background: transparent;
                padding: 0;
                margin: 0;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .wristband-canvas {
                border: none !important;
                box-shadow: none !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar (Hidden during print) -->
    <div class="no-print-toolbar">
        <div>
            <div style="font-weight: 700; color: #0f172a; font-size: 15px;">
                <i class="bi bi-tag-fill text-danger me-1"></i> Vòng Tay Nhận Diện Người Bệnh Chuẩn JCI
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Bệnh nhân: <strong>{{ $patient->full_name }}</strong> | Mã: <code>{{ $patient->patient_code }}</code>
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer-fill"></i> In Vòng Tay
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Đóng
        </button>
    </div>

    <!-- 250mm x 25mm Hospital Patient Wristband -->
    <div class="wristband-canvas">
        <!-- Securing Tab -->
        <div class="tab-securing">
            <span>Dán cố định</span>
        </div>

        <!-- Patient Demographics & Barcode -->
        <div class="patient-info-zone">
            <!-- Brand -->
            <div class="hospital-brand">
                <span class="hospital-name">BV ĐK TÂM ANH</span>
                <span class="hospital-sub">KHOA KHÁM BỆNH</span>
            </div>

            <!-- Patient Information -->
            <div class="patient-demographics">
                <div class="patient-name">{{ $patient->full_name }}</div>
                <div class="patient-details-row">
                    <span>Mã BN: <strong class="code-badge">{{ $patient->patient_code }}</strong></span>
                    <span>NS: <strong>{{ $patient->dob ? $patient->dob->format('d/m/Y') : '--' }}</strong> ({{ $patient->dob ? $patient->dob->age . 'T' : '' }})</span>
                    <span>Phái: <strong>{{ $patient->gender_name }}</strong></span>
                    @if($patient->blood_group)
                        <span class="blood-badge">Máu: {{ $patient->blood_group }}</span>
                    @endif
                </div>
            </div>

            <!-- Code 128 Barcode -->
            <div class="barcode-zone">
                <div class="barcode-text">*{{ $patient->patient_code }}*</div>
                <div class="barcode-digits">{{ $patient->patient_code }}</div>
            </div>
        </div>

        <!-- Allergy Alert Zone (If Patient has recorded allergies) -->
        @if($patient->hasAllergies())
            <div class="allergy-alert-strip">
                <div class="allergy-title">
                    <i class="bi bi-exclamation-triangle-fill"></i> DỊ ỨNG
                </div>
                <div class="allergy-sub" title="{{ $patient->allergies }}">
                    {{ $patient->allergies }}
                </div>
            </div>
        @endif

        <!-- Tail Tab -->
        <div class="tab-tail">
            <span>BV TÂM ANH</span>
        </div>
    </div>

</body>
</html>
