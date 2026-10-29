<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\PatientVital;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientVitalController extends Controller
{
    /**
     * Store a newly recorded vital signs measurement for the patient.
     */
    public function store(Request $request, PatientProfile $patient): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'recorded_at' => ['nullable', 'date'],
            'systolic_bp' => ['nullable', 'integer', 'min:40', 'max:300'],
            'diastolic_bp' => ['nullable', 'integer', 'min:30', 'max:200'],
            'heart_rate' => ['nullable', 'integer', 'min:30', 'max:250'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'spo2' => ['nullable', 'integer', 'min:50', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'systolic_bp.min' => 'Huyết áp tâm thu tối thiểu là 40 mmHg.',
            'systolic_bp.max' => 'Huyết áp tâm thu tối đa là 300 mmHg.',
            'diastolic_bp.min' => 'Huyết áp tâm trương tối thiểu là 30 mmHg.',
            'diastolic_bp.max' => 'Huyết áp tâm trương tối đa là 200 mmHg.',
            'heart_rate.min' => 'Mạch/nhịp tim tối thiểu là 30 lần/phút.',
            'heart_rate.max' => 'Mạch/nhịp tim tối đa là 250 lần/phút.',
            'temperature.min' => 'Thân nhiệt không hợp lệ (tối thiểu 30°C).',
            'temperature.max' => 'Thân nhiệt không hợp lệ (tối đa 45°C).',
            'spo2.min' => 'Chỉ số SpO2 tối thiểu là 50%.',
            'spo2.max' => 'Chỉ số SpO2 tối đa là 100%.',
        ]);

        if (empty($validated['systolic_bp']) && empty($validated['heart_rate']) && empty($validated['temperature']) && empty($validated['spo2'])) {
            return back()->with('error', 'Vui lòng nhập ít nhất một chỉ số sinh hiệu (Huyết áp, Mạch, Thân nhiệt hoặc SpO2).');
        }

        if (!empty($validated['systolic_bp']) && !empty($validated['diastolic_bp'])) {
            if ($validated['systolic_bp'] <= $validated['diastolic_bp']) {
                return back()->withInput()->with('error', 'Chỉ số huyết áp không hợp lệ theo chuẩn lâm sàng: Huyết áp tâm thu (' . $validated['systolic_bp'] . ' mmHg) phải lớn hơn huyết áp tâm trương (' . $validated['diastolic_bp'] . ' mmHg).');
            }
        }

        $vital = PatientVital::create([
            'patient_profile_id' => $patient->id,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'recorded_by_user_id' => Auth::id(),
            'recorded_at' => !empty($validated['recorded_at']) ? Carbon::parse($validated['recorded_at']) : Carbon::now(),
            'systolic_bp' => $validated['systolic_bp'] ?? null,
            'diastolic_bp' => $validated['diastolic_bp'] ?? null,
            'heart_rate' => $validated['heart_rate'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'spo2' => $validated['spo2'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'height' => $validated['height'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Audit Trail: Record vitals measurement log
        ClinicalAuditLog::record(
            $patient,
            'record_vitals',
            "Ghi nhận chỉ số sinh hiệu: HA " . ($vital->systolic_bp ? "{$vital->systolic_bp}/{$vital->diastolic_bp} mmHg" : "chưa đo") . ", Mạch " . ($vital->heart_rate ? "{$vital->heart_rate} l/p" : "chưa đo"),
            null,
            $validated
        );

        return back()->with('success', "Đã ghi nhận chỉ số sinh hiệu mới lúc {$vital->recorded_at->format('H:i d/m/Y')} cho bệnh nhân {$patient->full_name}.");
    }

    /**
     * Delete an incorrectly recorded vital entry.
     */
    public function destroy(PatientVital $vital): RedirectResponse
    {
        $vital->delete();

        return back()->with('success', 'Đã xóa bản ghi sinh hiệu thành công.');
    }
}
