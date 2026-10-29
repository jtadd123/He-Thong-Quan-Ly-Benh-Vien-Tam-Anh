<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalAuditLog;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Màn hình chính Kiosk tự phục vụ cảm ứng tại cửa sảnh.
     */
    public function index(): View
    {
        $specialties = Specialty::where('is_active', true)
            ->orderBy('name')
            ->get();

        $today = Carbon::today()->toDateString();
        $totalServedToday = Appointment::whereDate('appointment_date', $today)->count();

        return view('kiosk.index', compact('specialties', 'totalServedToday'));
    }

    /**
     * Luồng 1: Tra cứu và Check-in cuộc hẹn đã đặt trước.
     */
    public function checkinAppointment(Request $request): JsonResponse
    {
        $codeOrPhone = trim($request->input('query', ''));

        if (empty($codeOrPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập Mã lịch hẹn hoặc Số điện thoại đặt khám.',
            ], 422);
        }

        $today = Carbon::today()->toDateString();

        // Tra cứu theo mã hẹn hoặc số điện thoại của bệnh nhân
        $appointment = Appointment::with(['patientProfile', 'specialty', 'doctor.user'])
            ->whereDate('appointment_date', $today)
            ->where(function ($query) use ($codeOrPhone) {
                $query->where('appointment_code', $codeOrPhone)
                    ->orWhereHas('patientProfile', function ($q) use ($codeOrPhone) {
                        $q->where('phone', $codeOrPhone)
                          ->orWhere('patient_code', $codeOrPhone)
                          ->orWhere('identity_card', $codeOrPhone);
                    });
            })
            ->latest('id')
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch hẹn ngày hôm nay với thông tin đã nhập. Quý khách vui lòng chọn mục "Bệnh Nhân Tái Khám" hoặc liên hệ Quầy tiếp đón số 01.',
            ], 404);
        }

        // Nếu trạng thái chưa được tiếp nhận (pending), cập nhật sang confirmed (đã check-in tại Kiosk)
        if ($appointment->status === Appointment::STATUS_PENDING) {
            $appointment->status = Appointment::STATUS_CONFIRMED;
            $appointment->updated_at = Carbon::now();
            $appointment->save();

            ClinicalAuditLog::record(
                $appointment->patientProfile,
                'checkin',
                "Bệnh nhân tự xác nhận check-in tại Kiosk số sảnh tiếp đón - Mã hẹn {$appointment->appointment_code}",
                null,
                ['source' => 'Kiosk', 'checkin_at' => Carbon::now()->toIso8601String()]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận tiếp nhận thành công!',
            'appointment_id' => $appointment->id,
            'ticket_url' => route('kiosk.ticket', $appointment),
            'appointment' => [
                'code' => $appointment->appointment_code,
                'patient_name' => $appointment->patientProfile->full_name,
                'specialty' => $appointment->specialty ? $appointment->specialty->name : 'Khám chuyên khoa',
                'doctor' => $appointment->doctor ? $appointment->doctor->full_name_with_title : 'Bác sĩ phụ trách',
                'room' => $appointment->doctor ? $appointment->doctor->room_number : 'Quầy 01',
                'queue_number' => $appointment->queue_number,
                'status' => $appointment->status_name,
            ],
        ]);
    }

    /**
     * Luồng 2: Tra cứu bệnh nhân cũ theo SĐT, Mã BN hoặc CCCD để đăng ký khám nhanh.
     */
    public function lookupPatient(Request $request): JsonResponse
    {
        $identifier = trim($request->input('identifier', ''));

        if (empty($identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập Số điện thoại, Mã bệnh nhân hoặc Số CCCD.',
            ], 422);
        }

        $patient = PatientProfile::where('phone', $identifier)
            ->orWhere('patient_code', $identifier)
            ->orWhere('identity_card', $identifier)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có hồ sơ bệnh nhân trong hệ thống. Quý khách vui lòng chọn mục "Bệnh Nhân Mới Lần Đầu".',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'patient_code' => $patient->patient_code,
                'full_name' => $patient->full_name,
                'phone' => $patient->phone,
                'dob' => $patient->dob ? $patient->dob->format('d/m/Y') : '',
                'gender_name' => $patient->gender_name,
                'insurance_code' => $patient->insurance_code,
            ],
        ]);
    }

    /**
     * Luồng 2: Xác nhận đăng ký chuyên khoa cho bệnh nhân cũ.
     */
    public function registerReturningPatient(Request $request): JsonResponse
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id' => 'required|exists:specialties,id',
        ]);

        $patient = PatientProfile::findOrFail($request->input('patient_profile_id'));
        $specialty = Specialty::findOrFail($request->input('specialty_id'));

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $shift = $now->hour < 12 ? 'morning' : 'afternoon';

        // Phân bổ bác sĩ đang trực ca của chuyên khoa nếu có
        $doctor = Doctor::where('specialty_id', $specialty->id)->where('is_available', true)->first()
            ?? Doctor::where('specialty_id', $specialty->id)->first()
            ?? Doctor::first();

        $appointment = DB::transaction(function () use ($patient, $specialty, $doctor, $today, $shift) {
            $code = Appointment::generateAppointmentCode();

            $app = Appointment::create([
                'appointment_code' => $code,
                'patient_profile_id' => $patient->id,
                'doctor_id' => $doctor?->id,
                'specialty_id' => $specialty->id,
                'appointment_date' => $today,
                'shift' => $shift,
                'appointment_time' => Carbon::now()->format('H:i:s'),
                'symptom_description' => '[KIOSK-TÁI KHÁM] Bệnh nhân tự đăng ký lấy số tại cây Kiosk tiếp đón',
                'status' => Appointment::STATUS_CONFIRMED,
                'created_by_user_id' => null,
            ]);

            ClinicalAuditLog::record(
                $patient,
                'create',
                "Kiosk tự động cấp số thứ tự tái khám chuyên khoa {$specialty->name} - Mã {$code}",
                null,
                [
                    'source' => 'Cây Kiosk Tự Phục Vụ Cửa Sảnh',
                    'type' => 'Bệnh nhân cũ tái khám',
                    'specialty' => $specialty->name,
                ]
            );

            return $app;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy số thứ tự khám thành công!',
            'appointment_id' => $appointment->id,
            'ticket_url' => route('kiosk.ticket', $appointment),
        ]);
    }

    /**
     * Luồng 3: Đăng ký nhanh bệnh nhân mới lần đầu tại Kiosk.
     */
    public function registerNewPatient(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^[0-9]{10,11}$/',
            'birth_year' => 'required|integer|min:1920|max:' . Carbon::now()->year,
            'gender' => 'required|in:male,female,other',
            'specialty_id' => 'required|exists:specialties,id',
            'identity_card' => 'nullable|string|max:20',
            'insurance_code' => 'nullable|string|max:20',
        ]);

        $specialty = Specialty::findOrFail($request->input('specialty_id'));
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $shift = $now->hour < 12 ? 'morning' : 'afternoon';

        $doctor = Doctor::where('specialty_id', $specialty->id)->where('is_available', true)->first()
            ?? Doctor::where('specialty_id', $specialty->id)->first()
            ?? Doctor::first();

        $appointment = DB::transaction(function () use ($request, $specialty, $doctor, $today, $shift) {
            $dob = Carbon::createFromDate((int) $request->input('birth_year'), 1, 1);
            $patientCode = PatientProfile::generatePatientCode();

            $patient = PatientProfile::create([
                'patient_code' => $patientCode,
                'user_id' => null,
                'full_name' => trim(mb_strtoupper($request->input('full_name'))),
                'phone' => trim($request->input('phone')),
                'dob' => $dob,
                'gender' => $request->input('gender'),
                'identity_card' => trim($request->input('identity_card', '')),
                'insurance_code' => trim($request->input('insurance_code', '')),
                'address' => 'Đăng ký tại Kiosk sảnh Tâm Anh',
            ]);

            $code = Appointment::generateAppointmentCode();

            $app = Appointment::create([
                'appointment_code' => $code,
                'patient_profile_id' => $patient->id,
                'doctor_id' => $doctor?->id,
                'specialty_id' => $specialty->id,
                'appointment_date' => $today,
                'shift' => $shift,
                'appointment_time' => Carbon::now()->format('H:i:s'),
                'symptom_description' => '[KIOSK-BỆNH NHÂN MỚI] Đăng ký lần đầu tại Kiosk sảnh tiếp đón',
                'status' => Appointment::STATUS_CONFIRMED,
                'created_by_user_id' => null,
            ]);

            ClinicalAuditLog::record(
                $patient,
                'CREATE_PATIENT_KIOSK',
                "Bệnh nhân mới tự đăng ký tại Kiosk số sảnh tiếp đón - Cấp mã BN {$patientCode}",
                null,
                [
                    'source' => 'Cây Kiosk Tự Phục Vụ Cửa Sảnh',
                    'patient_code' => $patientCode,
                    'full_name' => $patient->full_name,
                ]
            );

            return $app;
        });

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký hồ sơ và lấy số thứ tự thành công!',
            'appointment_id' => $appointment->id,
            'ticket_url' => route('kiosk.ticket', $appointment),
        ]);
    }

    /**
     * In phiếu số thứ tự tiếp nhận mini khổ nhiệt 80mm.
     */
    public function printTicket(Appointment $appointment): View
    {
        $appointment->load(['patientProfile', 'specialty', 'doctor.user']);

        // Mã QR theo dõi thứ tự khám trên điện thoại bệnh nhân
        $trackingUrl = route('reception.appointments.queue-display') . '?search=' . urlencode($appointment->appointment_code);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($trackingUrl);

        return view('kiosk.ticket', compact('appointment', 'qrCodeUrl'));
    }
}
