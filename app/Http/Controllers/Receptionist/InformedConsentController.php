<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalAuditLog;
use App\Models\Doctor;
use App\Models\InformedConsent;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InformedConsentController extends Controller
{
    /**
     * Danh sách các Bản Cam Đoan Phẫu Thuật / Thủ Thuật Y Khoa.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('q', ''));
        $date = $request->input('date', '');

        $query = InformedConsent::with(['patientProfile', 'doctor.user', 'createdBy']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('consent_code', 'like', "%{$search}%")
                    ->orWhere('procedure_name', 'like', "%{$search}%")
                    ->orWhere('signer_name', 'like', "%{$search}%")
                    ->orWhereHas('patientProfile', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%")
                           ->orWhere('patient_code', 'like', "%{$search}%")
                           ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($date)) {
            $query->whereDate('signed_at', $date);
        }

        $consents = $query->latest('signed_at')->paginate(15)->withQueryString();

        return view('receptionist.consents.index', compact('consents', 'search', 'date'));
    }

    /**
     * Màn hình tạo Bản Cam Đoan Thủ Thuật có Khung Ký Điện Tử Canvas.
     */
    public function create(PatientProfile $patient): View
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        $recentAppointment = $patient->appointments()->latest('appointment_date')->first();

        return view('receptionist.consents.create', compact('patient', 'doctors', 'recentAppointment'));
    }

    /**
     * Lưu bản cam đoan điện tử kèm chữ ký số.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'procedure_name' => 'required|string|max:200',
            'department_name' => 'required|string|max:150',
            'signer_name' => 'required|string|max:100',
            'signer_relationship' => 'required|in:patient,family,guardian',
            'signer_phone' => 'nullable|string|max:20',
            'signer_id_card' => 'nullable|string|max:30',
            'diagnosis_description' => 'nullable|string|max:500',
            'risks_explained' => 'nullable|string|max:1000',
            'patient_undertaking' => 'nullable|string|max:1000',
            'signature_data' => 'required|string', // Base64 Canvas Image
        ], [
            'procedure_name.required' => 'Vui lòng nhập tên thủ thuật / phẫu thuật y tế.',
            'signer_name.required' => 'Vui lòng nhập họ tên người ký cam đoan.',
            'signature_data.required' => 'Người bệnh hoặc người đại diện bắt buộc phải ký chữ ký điện tử trên khung ký.',
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $code = InformedConsent::generateConsentCode();

        $consent = InformedConsent::create([
            'patient_profile_id' => $patient->id,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'doctor_id' => $validated['doctor_id'] ?? null,
            'created_by_user_id' => Auth::id(),
            'consent_code' => $code,
            'procedure_name' => $validated['procedure_name'],
            'department_name' => $validated['department_name'],
            'signer_name' => $validated['signer_name'],
            'signer_relationship' => $validated['signer_relationship'],
            'signer_phone' => $validated['signer_phone'] ?? $patient->phone,
            'signer_id_card' => $validated['signer_id_card'] ?? $patient->identity_card,
            'diagnosis_description' => $validated['diagnosis_description'] ?? 'Chỉ định thủ thuật chuyên môn theo yêu cầu điều trị',
            'risks_explained' => $validated['risks_explained'] ?? 'Đã được bác sĩ giải thích rõ về mục đích, phương pháp, các tai biến nguy cơ tiềm ẩn (chảy máu, dị ứng thuốc gây mê/tê, nhiễm trùng) và các biện pháp dự phòng.',
            'patient_undertaking' => $validated['patient_undertaking'] ?? 'Tôi hoàn toàn tự nguyện đồng ý thực hiện thủ thuật và cam kết tuân thủ nghiêm ngặt mọi y lệnh của bác sĩ.',
            'signature_data' => $validated['signature_data'],
            'signed_at' => Carbon::now(),
            'ip_address' => $request->ip(),
            'status' => 'active',
        ]);

        // Audit Trail số hóa
        ClinicalAuditLog::record(
            $patient,
            'issue_certificate',
            "Ký số bản cam đoan phẫu thuật/thủ thuật: {$consent->procedure_name} (Mã: {$code})",
            null,
            [
                'consent_code' => $code,
                'procedure' => $consent->procedure_name,
                'signer' => $consent->signer_name,
                'signed_at' => $consent->signed_at->toIso8601String(),
            ]
        );

        return redirect()->route('reception.consents.print', $consent)
            ->with('success', "Đã ký số thành công bản cam đoan thủ thuật {$consent->procedure_name} (Mã: {$code})!");
    }

    /**
     * In bản cam đoan phẫu thuật / thủ thuật chuẩn Bộ Y Tế.
     */
    public function print(InformedConsent $consent): View
    {
        $consent->load(['patientProfile', 'doctor.user', 'createdBy', 'appointment.specialty']);
        return view('receptionist.consents.print', compact('consent'));
    }

    /**
     * Hủy bản cam đoan nếu có sai sót.
     */
    public function destroy(InformedConsent $consent): RedirectResponse
    {
        $patientId = $consent->patient_profile_id;
        $code = $consent->consent_code;
        $consent->delete();

        ClinicalAuditLog::record(
            $patientId,
            'undo_dispense',
            "Xóa bản cam đoan thủ thuật y tế mã {$code}",
            null,
            ['deleted_code' => $code]
        );

        return back()->with('success', "Đã xóa bản cam đoan {$code} khỏi hệ thống.");
    }
}
