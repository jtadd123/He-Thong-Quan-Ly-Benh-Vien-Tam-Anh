<?php

namespace App\Services;

use App\Models\PatientInvoice;

class VietQrPaymentService
{
    // Thông tin tài khoản chuyên dụng thu viện phí Bệnh Viện Đa Khoa Tâm Anh
    public const DEFAULT_BANK_ID = 'ICB'; // VietinBank
    public const DEFAULT_ACCOUNT_NO = '110002868888';
    public const DEFAULT_ACCOUNT_NAME = 'BENH VIEN DA KHOA TAM ANH';

    /**
     * Tạo đường dẫn Dynamic VietQR Image URL theo chuẩn Napas 247.
     *
     * @param PatientInvoice $invoice
     * @param string $template Kiểu mẫu QR ('compact', 'compact2', 'qr_only', 'print')
     * @return string
     */
    public static function generateQrUrl(PatientInvoice $invoice, string $template = 'compact2'): string
    {
        $bankId = self::DEFAULT_BANK_ID;
        $accountNo = self::DEFAULT_ACCOUNT_NO;
        $accountName = rawurlencode(self::DEFAULT_ACCOUNT_NAME);
        
        // Số tiền thực thu từ bệnh nhân (sau khi đã trừ BHYT)
        $amount = (int) round($invoice->patient_co_pay_amount);
        
        // Nội dung chuyển khoản chuẩn hóa không dấu: "TT VP [MA_HD] [HO_TEN]"
        $cleanName = self::removeVietnameseAccents($invoice->patientProfile->full_name ?? 'BN');
        $cleanName = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cleanName), 0, 15);
        $addInfo = rawurlencode("TTVP {$invoice->invoice_code} {$cleanName}");

        return "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png?amount={$amount}&addInfo={$addInfo}&accountName={$accountName}";
    }

    /**
     * Trả về chi tiết cấu hình thanh toán.
     *
     * @param PatientInvoice $invoice
     * @return array
     */
    public static function getPaymentDetails(PatientInvoice $invoice): array
    {
        $amount = (int) round($invoice->patient_co_pay_amount);
        $cleanName = self::removeVietnameseAccents($invoice->patientProfile->full_name ?? 'BN');
        $cleanName = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cleanName), 0, 15);
        $transferContent = "TTVP {$invoice->invoice_code} {$cleanName}";

        return [
            'bank_name' => 'Ngân hàng TMCP Công Thương Việt Nam (VietinBank)',
            'bank_code' => self::DEFAULT_BANK_ID,
            'account_number' => self::DEFAULT_ACCOUNT_NO,
            'account_name' => self::DEFAULT_ACCOUNT_NAME,
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0, ',', '.') . ' VNĐ',
            'transfer_content' => $transferContent,
            'qr_image_url' => self::generateQrUrl($invoice, 'compact2'),
            'qr_print_url' => self::generateQrUrl($invoice, 'qr_only'),
        ];
    }

    /**
     * Loại bỏ dấu tiếng Việt để tạo nội dung chuyển khoản an toàn cho Napas.
     */
    protected static function removeVietnameseAccents(string $str): string
    {
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/u', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/u', 'y', $str);
        $str = preg_replace('/(đ)/u', 'd', $str);
        $str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u', 'A', $str);
        $str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u', 'E', $str);
        $str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/u', 'I', $str);
        $str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u', 'O', $str);
        $str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u', 'U', $str);
        $str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u', 'Y', $str);
        $str = preg_replace('/(Đ)/u', 'D', $str);
        return $str;
    }
}
