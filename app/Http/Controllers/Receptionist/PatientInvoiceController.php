<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientInvoice;
use App\Services\HealthInsuranceService;
use App\Services\VietQrPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientInvoiceController extends Controller
{
    /**
     * Danh sách sổ hóa đơn viện phí ngoại trú.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');
        $date = $request->input('date', '');

        $query = PatientInvoice::with(['patientProfile', 'appointment.doctor', 'receivedBy']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($status)) {
            $query->where('payment_status', $status);
        }

        if (!empty($date)) {
            $query->whereDate('created_at', $date);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // Thống kê nhanh hôm nay
        $todayStats = [
            'total_revenue' => PatientInvoice::whereDate('paid_at', today())->where('payment_status', PatientInvoice::STATUS_PAID)->sum('patient_co_pay_amount'),
            'paid_count' => PatientInvoice::whereDate('paid_at', today())->where('payment_status', PatientInvoice::STATUS_PAID)->count(),
            'unpaid_count' => PatientInvoice::where('payment_status', PatientInvoice::STATUS_UNPAID)->count(),
        ];

        return view('receptionist.invoices.index', compact('invoices', 'search', 'status', 'date', 'todayStats'));
    }

    /**
     * Chi tiết hóa đơn viện phí kèm mã VietQR Động.
     */
    public function show(PatientInvoice $invoice): View
    {
        $invoice->load(['patientProfile', 'appointment.doctor.specialty', 'medicalRecord.prescriptions', 'receivedBy']);
        $paymentDetails = VietQrPaymentService::getPaymentDetails($invoice);

        return view('receptionist.invoices.show', compact('invoice', 'paymentDetails'));
    }

    /**
     * Tạo hóa đơn tự động từ Ca khám hoặc Đơn thuốc.
     */
    public function createFromAppointment(Appointment $appointment): RedirectResponse
    {
        $existingInvoice = PatientInvoice::where('appointment_id', $appointment->id)->first();
        if ($existingInvoice) {
            return redirect()->route('reception.invoices.show', $existingInvoice);
        }

        $patient = $appointment->patientProfile;
        $consultationFee = $appointment->doctor ? ($appointment->doctor->consultation_fee ?? 300000) : 300000;
        
        // Tính tiền thuốc nếu có bệnh án
        $medicationFee = 0;
        $medicalRecord = $appointment->medicalRecord;
        if ($medicalRecord) {
            // Giả định trung bình 35.000đ/viên hoặc gói thuốc trong đơn
            $medicationFee = $medicalRecord->prescriptions->sum(fn($p) => ($p->duration_days ?: 5) * 45000);
        }

        $totalAmount = $consultationFee + $medicationFee;

        // Tính BHYT
        $bhytInfo = HealthInsuranceService::calculate($patient->insurance_code ?? '', $totalAmount);

        $insuranceCovered = $bhytInfo['is_valid'] ? $bhytInfo['insurance_paid_amount'] : 0;
        $patientCoPay = $totalAmount - $insuranceCovered;

        $invoice = PatientInvoice::create([
            'invoice_code' => PatientInvoice::generateInvoiceCode(),
            'patient_profile_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'medical_record_id' => $medicalRecord?->id,
            'consultation_fee' => $consultationFee,
            'medication_fee' => $medicationFee,
            'procedure_fee' => 0,
            'total_amount' => $totalAmount,
            'insurance_covered_amount' => $insuranceCovered,
            'patient_co_pay_amount' => $patientCoPay,
            'payment_status' => PatientInvoice::STATUS_UNPAID,
            'payment_method' => PatientInvoice::METHOD_CASH,
            'notes' => 'Tự động tạo hóa đơn khám bệnh BHYT / Ngoại trú',
        ]);

        return redirect()->route('reception.invoices.show', $invoice)
            ->with('success', 'Đã khởi tạo bảng kê viện phí thành công.');
    }

    /**
     * Xác nhận đã thu tiền viện phí.
     */
    public function confirmPayment(PatientInvoice $invoice, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,vietqr,card,insurance_direct',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'payment_status' => PatientInvoice::STATUS_PAID,
            'payment_method' => $validated['payment_method'],
            'paid_at' => now(),
            'received_by_user_id' => Auth::id(),
            'notes' => $validated['notes'] ?? $invoice->notes,
        ]);

        return redirect()->route('reception.invoices.show', $invoice)
            ->with('success', 'Xác nhận thu viện phí thành công! Đã phát hành biên lai thu tiền điện tử.');
    }

    /**
     * In Phiếu Thu Viện Phí Điện Tử kiêm Bảng Kê chuẩn Bộ Y Tế.
     */
    public function print(PatientInvoice $invoice): View
    {
        $invoice->load(['patientProfile', 'appointment.doctor.specialty', 'medicalRecord.prescriptions', 'receivedBy']);
        $paymentDetails = VietQrPaymentService::getPaymentDetails($invoice);

        return view('receptionist.invoices.print', compact('invoice', 'paymentDetails'));
    }

    /**
     * Tra cứu viện phí công khai cho Bệnh nhân quét mã hoặc tra cứu bằng điện thoại.
     */
    public function publicLookup(Request $request): View
    {
        $code = trim($request->input('code', ''));
        $invoice = null;

        if (!empty($code)) {
            $invoice = PatientInvoice::with(['patientProfile', 'appointment.doctor', 'medicalRecord.prescriptions'])
                ->where('invoice_code', $code)
                ->orWhereHas('appointment', fn($aq) => $aq->where('appointment_code', $code))
                ->first();
        }

        $paymentDetails = $invoice ? VietQrPaymentService::getPaymentDetails($invoice) : null;

        return view('public.billing_lookup', compact('invoice', 'code', 'paymentDetails'));
    }
}
