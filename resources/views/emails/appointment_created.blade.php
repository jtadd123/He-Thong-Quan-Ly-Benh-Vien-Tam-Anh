<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận lịch hẹn khám bệnh</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #0284c7;
            color: #ffffff;
            padding: 25px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .code-box {
            background-color: #f0f9ff;
            border: 2px dashed #0284c7;
            border-radius: 8px;
            text-align: center;
            padding: 15px;
            margin: 20px 0;
        }
        .code-box .label {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
        }
        .code-box .code {
            font-size: 24px;
            font-weight: bold;
            color: #0284c7;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .info-table td.label {
            color: #64748b;
            width: 35%;
            font-weight: 600;
        }
        .info-table td.value {
            color: #0f172a;
            font-weight: 500;
        }
        .note-box {
            background-color: #fefce8;
            border-left: 4px solid #eab308;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: #854d0e;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BỆNH VIỆN ĐA KHOA TÂM ANH</h1>
            <p>Hệ thống Đặt lịch khám bệnh Ngoại trú Trực tuyến</p>
        </div>

        <div class="content">
            <p>Kính gửi Quý khách <strong>{{ $appointment->patientProfile->full_name }}</strong>,</p>
            <p>Hệ thống Bệnh viện Đa khoa Tâm Anh xin trân trọng thông báo lịch khám bệnh của Quý khách đã được tiếp nhận thành công vào hệ thống điều phối.</p>

            <div class="code-box">
                <div class="label">Mã tra cứu lịch hẹn</div>
                <div class="code">{{ $appointment->appointment_code }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Họ tên bệnh nhân:</td>
                    <td class="value">{{ $appointment->patientProfile->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Số điện thoại:</td>
                    <td class="value">{{ $appointment->patientProfile->phone }}</td>
                </tr>
                <tr>
                    <td class="label">Chuyên khoa khám:</td>
                    <td class="value">{{ $appointment->specialty->name }}</td>
                </tr>
                <tr>
                    <td class="label">Bác sĩ phụ trách:</td>
                    <td class="value">{{ $appointment->doctor->full_name_with_title }}</td>
                </tr>
                <tr>
                    <td class="label">Phòng khám:</td>
                    <td class="value">{{ $appointment->doctor->room_number }}</td>
                </tr>
                <tr>
                    <td class="label">Ngày khám dự kiến:</td>
                    <td class="value"><strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Ca khám:</td>
                    <td class="value">{{ $appointment->shift_name }} {{ $appointment->appointment_time ? '(' . substr($appointment->appointment_time, 0, 5) . ')' : '' }}</td>
                </tr>
                <tr>
                    <td class="label">Lý do / Triệu chứng:</td>
                    <td class="value">{{ $appointment->symptom_description }}</td>
                </tr>
                <tr>
                    <td class="label">Trạng thái:</td>
                    <td class="value">
                        @if($appointment->status === 'waitlist')
                            <span style="color: #b45309; font-weight: bold;">Danh sách chờ ưu tiên (Vị trí #{{ $appointment->waitlist_position ?? 1 }})</span>
                        @else
                            <span style="color: #d97706; font-weight: bold;">Chờ tiếp nhận tại quầy</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="note-box">
                <strong>Lưu ý quan trọng khi đi khám:</strong><br>
                - Quý khách vui lòng có mặt tại <strong>Quầy Tiếp nhận Bệnh viện Tâm Anh</strong> trước giờ khám ít nhất <strong>15 phút</strong>.<br>
                - Xuất trình <strong>Mã tra cứu lịch hẹn</strong> hoặc <strong>Số điện thoại</strong> để nhân viên lễ tân in phiếu số thứ tự vào phòng khám.<br>
                - Mang theo CCCD/Thẻ BHYT và các kết quả chụp chiếu, xét nghiệm gần nhất (nếu có).
            </div>

            <p style="margin-top: 25px; font-size: 14px;">Mọi thắc mắc hoặc cần hỗ trợ thay đổi lịch, Quý khách vui lòng liên hệ Tổng đài CSKH: <strong>1800 6858</strong> (Miễn phí).</p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 5px 0;"><strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong></p>
            <p style="margin: 0;">Địa chỉ: 108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</p>
            <p style="margin: 5px 0 0 0;">Email này được gửi tự động từ hệ thống. Quý khách vui lòng không phản hồi trực tiếp vào địa chỉ này.</p>
        </div>
    </div>
</body>
</html>
