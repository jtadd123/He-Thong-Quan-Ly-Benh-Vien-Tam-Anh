<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật lịch khám - Bệnh Viện Đa Khoa Tâm Anh</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; font-weight: bold;">BỆNH VIỆN ĐA KHOA TÂM ANH</h1>
            <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Thông Báo Cập Nhật Thay Đổi Lịch Khám Bệnh</p>
        </div>

        <div style="padding: 24px;">
            <p>Kính gửi Quý khách <strong>{{ $appointment->patientProfile->full_name ?? 'Quý khách' }}</strong>,</p>
            <p>Hệ thống ghi nhận lịch khám bệnh của Quý khách đã được điều chỉnh thành công với thông tin chi tiết dưới đây:</p>

            <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 14px 18px; margin: 18px 0; border-radius: 4px;">
                <p style="margin: 4px 0; font-size: 14px;"><strong>Mã lịch hẹn:</strong> <span style="font-size: 16px; color: #0369a1; font-weight: bold;">{{ $appointment->appointment_code }}</span></p>
                <p style="margin: 4px 0; font-size: 14px; color: #dc2626;"><strong>Lịch cũ:</strong> {{ \Carbon\Carbon::parse($oldDate)->format('d/m/Y') }} (Ca: {{ $oldShift == 'morning' ? 'Sáng (07:30 - 11:30)' : 'Chiều (13:30 - 17:00)' }})</p>
                <p style="margin: 4px 0; font-size: 14px; color: #16a34a; font-weight: bold;"><strong>LỊCH MỚI:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }} (Ca: {{ $appointment->shift == 'morning' ? 'Sáng (07:30 - 11:30)' : 'Chiều (13:30 - 17:00)' }})</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Bác sĩ phụ trách:</strong> {{ $appointment->doctor->full_name_with_title ?? ($appointment->doctor->user->name ?? 'Theo phân công khoa') }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Chuyên khoa:</strong> {{ $appointment->specialty->name ?? 'Khám đa khoa' }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Phòng khám:</strong> <strong style="color: #0284c7;">{{ $appointment->doctor->room_number ?? 'Theo hướng dẫn tại quầy' }}</strong></p>
            </div>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                Quý khách vui lòng đến trước giờ khám 15 phút tại Quầy tiếp đón để hoàn tất thủ tục khám chữa bệnh. 
                Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ Tổng đài CSKH: <strong>1800 6858</strong> (miễn cước).
            </p>
        </div>

        <div style="background: #f8fafc; padding: 14px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
            © {{ date('Y') }} Hệ Thống Bệnh Viện Đa Khoa Tâm Anh. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>
