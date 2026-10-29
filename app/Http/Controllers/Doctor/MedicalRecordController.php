<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    /**
     * Show consultation / examination form for an appointment.
     */
    public function create(Appointment $appointment): View|RedirectResponse
    {
        $user = Auth::user();

        // Kiểm tra quyền: Chỉ bác sĩ phụ trách ca khám hoặc Admin mới được vào khám
        if ($user->isDoctor() && (!$user->doctor || $user->doctor->id !== $appointment->doctor_id)) {
            abort(403, 'Bạn không được phân công phụ trách ca khám này.');
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return redirect()->route('doctor.queue')
                ->with('error', 'Lịch hẹn này đã bị hủy, không thể tiến hành khám bệnh.');
        }

        // Nếu ca khám đã có bệnh án, điều hướng sang màn hình xem chi tiết
        if ($appointment->medicalRecord) {
            return redirect()->route('doctor.records.show', $appointment->medicalRecord->id)
                ->with('info', 'Ca khám này đã được lập bệnh án hoàn tất trước đó.');
        }

        $appointment->load(['patientProfile', 'doctor.user', 'specialty']);

        // Tiền sử bệnh án các lần khám trước của bệnh nhân này
        $pastRecords = MedicalRecord::with(['doctor.user', 'prescriptions', 'attachments', 'appointment.specialty'])
            ->where('patient_profile_id', $appointment->patient_profile_id)
            ->latest()
            ->take(5)
            ->get();

        return view('doctor.examine', compact('appointment', 'pastRecords'));
    }

    /**
     * Store a newly created medical record and prescriptions within DB::transaction.
     */
    public function store(StoreMedicalRecordRequest $request): RedirectResponse
    {
        $medicalRecord = DB::transaction(function () use ($request) {
            $appointment = Appointment::lockForUpdate()->findOrFail($request->appointment_id);

            $user = Auth::user();
            if ($user->isDoctor() && (!$user->doctor || $user->doctor->id !== $appointment->doctor_id)) {
                abort(403, 'Bạn không được phân công phụ trách ca khám này.');
            }

            if ($appointment->medicalRecord) {
                return $appointment->medicalRecord;
            }

            // 1. Tạo bản ghi hồ sơ bệnh án (tự động sinh mã MR-YYYYMMDD-XXXX)
            $record = MedicalRecord::create([
                'record_code' => MedicalRecord::generateRecordCode(),
                'appointment_id' => $appointment->id,
                'patient_profile_id' => $appointment->patient_profile_id,
                'doctor_id' => $appointment->doctor_id,
                'symptoms' => $request->symptoms,
                'diagnosis' => $request->diagnosis,
                'clinical_notes' => $request->clinical_notes,
                'treatment_plan' => $request->treatment_plan,
                'follow_up_date' => $request->follow_up_date,
            ]);

            // 2. Chèn danh sách đơn thuốc nếu có
            if ($request->has('prescriptions') && is_array($request->prescriptions)) {
                foreach ($request->prescriptions as $p) {
                    $medName = trim($p['medication_name'] ?? '');
                    if ($medName !== '') {
                        $record->prescriptions()->create([
                            'medication_name' => $medName,
                            'dosage' => trim($p['dosage'] ?? ''),
                            'frequency' => trim($p['frequency'] ?? ''),
                            'duration_days' => max(1, (int)($p['duration_days'] ?? 1)),
                            'instructions' => trim($p['instructions'] ?? '') ?: null,
                        ]);
                    }
                }
            }

            // 3. Cập nhật trạng thái lịch hẹn sang COMPLETED
            $appointment->update([
                'status' => Appointment::STATUS_COMPLETED,
            ]);

            return $record;
        });

        return redirect()->route('doctor.records.show', $medicalRecord->id)
            ->with('success', "Đã lập bệnh án [{$medicalRecord->record_code}] và hoàn tất ca khám thành công!");
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $record): View
    {
        $user = Auth::user();

        // Bệnh nhân chỉ được xem bệnh án của chính mình
        if ($user->isPatient()) {
            if (!$user->patientProfile || $user->patientProfile->id !== $record->patient_profile_id) {
                abort(403, 'Bạn không có quyền truy cập hồ sơ bệnh án này.');
            }
        }

        $record->load([
            'appointment.specialty',
            'patientProfile.user',
            'doctor.user',
            'prescriptions',
            'attachments.uploader',
        ]);

        return view('doctor.records.show', compact('record'));
    }
}

