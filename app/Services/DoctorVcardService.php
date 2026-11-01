<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Support\Str;

class DoctorVcardService
{
    /**
     * Generate standard RFC 6350 vCard content for a doctor.
     */
    public static function generateVcard(Doctor $doctor): string
    {
        $name = $doctor->user->name ?? 'Bác sĩ';
        $title = $doctor->full_name_with_title;
        $specialtyName = $doctor->specialty->name ?? 'Đa khoa';
        $room = $doctor->room_number ?? 'Phòng khám Đa khoa';
        $url = route('public.doctors.show', $doctor->user->username ?? $doctor->id);
        $hospitalName = 'Bệnh Viện Đa Khoa Tâm Anh';
        $hotline = '1800 6858';
        $email = $doctor->user->email ?? 'cskh@tamanhhospital.vn';

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN;CHARSET=UTF-8:' . $title,
            'N;CHARSET=UTF-8:' . $name . ';;;' . ($doctor->qualification ?? '') . ';',
            'ORG;CHARSET=UTF-8:' . $hospitalName . ';' . $specialtyName,
            'TITLE;CHARSET=UTF-8:Bác sĩ Chuyên khoa ' . $specialtyName,
            'TEL;TYPE=WORK,VOICE:' . $hotline,
            'EMAIL;TYPE=PREF,INTERNET:' . $email,
            'URL;CHARSET=UTF-8:' . $url,
            'ADR;TYPE=WORK;CHARSET=UTF-8:;;108 Hoàng Như Tiếp, P. Bồ Đề;Q. Long Biên;Hà Nội;;Việt Nam',
            'NOTE;CHARSET=UTF-8:' . addcslashes("Vị trí làm việc: {$room} - {$hospitalName}. Đặt lịch khám trực tuyến tại: {$url}", "\n\r;,"),
            'REV:' . now()->toIso8601String(),
            'END:VCARD',
        ];

        return implode("\r\n", $lines);
    }

    /**
     * Generate dynamic QR Code image URL pointing to doctor public profile or booking.
     */
    public static function getQrCodeUrl(Doctor $doctor, int $size = 220): string
    {
        $targetUrl = route('public.doctors.show', $doctor->user->username ?? $doctor->id);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=8&data=" . urlencode($targetUrl);
    }
}
