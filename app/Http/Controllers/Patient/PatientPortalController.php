<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientPortalController extends Controller
{
    /**
     * Show patient appointment history and medical results.
     */
    public function history(Request $request): View
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        $appointments = collect();

        if ($patientProfile) {
            $query = Appointment::with([
                'specialty',
                'doctor.user',
                'medicalRecord.prescriptions',
            ])
                ->where('patient_profile_id', $patientProfile->id)
                ->latest('appointment_date');

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            $appointments = $query->paginate(10)->withQueryString();
        }

        return view('patient.history', compact('patientProfile', 'appointments'));
    }

    /**
     * View detailed appointment and prescription.
     */
    public function showAppointment(Appointment $appointment): View
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        // Patients can only view their own appointments; admins may review any appointment.
        if (!$user->isAdmin() && (!$patientProfile || $appointment->patient_profile_id !== $patientProfile->id)) {
            abort(403, 'Bạn không có quyền xem thông tin lịch khám này.');
        }

        $appointment->load([
            'specialty',
            'doctor.user',
            'medicalRecord.prescriptions',
            'medicalRecord.attachments',
            'medicalRecord.sickLeaveCertificate',
        ]);

        return view('patient.appointment-detail', compact('appointment', 'patientProfile'));
    }

    /**
     * Patient prints their official prescription.
     */
    public function printPrescription(Appointment $appointment): View
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        if (!$patientProfile || $appointment->patient_profile_id !== $patientProfile->id) {
            abort(403, 'Bạn không có quyền in đơn thuốc này.');
        }

        if (!$appointment->medicalRecord) {
            abort(404, 'Lịch khám này chưa có đơn thuốc.');
        }

        $record = $appointment->medicalRecord->load(['appointment.specialty', 'patientProfile', 'doctor.user', 'prescriptions']);

        return view('doctor.records.print', compact('record'));
    }

    /**
     * Patient can cancel their pending appointment.
     */
    public function cancelAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        if (!$patientProfile || $appointment->patient_profile_id !== $patientProfile->id) {
            abort(403, 'Bạn không có quyền thực hiện thao tác trên lịch hẹn này.');
        }

        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, 'waitlist'])) {
            return back()->with('error', 'Chỉ có thể hủy lịch hẹn đang ở trạng thái Chờ tiếp nhận, Đã xác nhận hoặc Đang trong hàng đợi.');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ], [
            'cancellation_reason.required' => 'Vui lòng cung cấp lý do hủy hẹn.',
        ]);

        $wasOccupyingSlot = in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]);

        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => 'Bệnh nhân tự hủy: ' . $request->input('cancellation_reason'),
        ]);

        // Auto-promote next candidate from smart waitlist if an active slot opened up
        if ($wasOccupyingSlot) {
            try {
                $dateStr = $appointment->appointment_date instanceof \Carbon\Carbon
                    ? $appointment->appointment_date->toDateString()
                    : (string) $appointment->appointment_date;

                app(\App\Services\WaitlistEngineService::class)->promoteNextCandidate(
                    $appointment->doctor_id,
                    $dateStr,
                    $appointment->shift
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Waitlist auto-promotion failed on patient cancel: " . $e->getMessage());
            }
        }

        return back()->with('success', "Đã hủy lịch hẹn {$appointment->appointment_code} thành công.");
    }

    /**
     * Show reschedule appointment page for patient.
     */
    public function showReschedule(Appointment $appointment): View
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        if (!$patientProfile || $appointment->patient_profile_id !== $patientProfile->id) {
            abort(403, 'Bạn không có quyền đổi lịch hẹn này.');
        }

        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])) {
            return redirect()->route('patient.history')->with('error', 'Chỉ có thể đổi lịch hẹn đang chờ tiếp nhận hoặc đã xác nhận.');
        }

        $appointment->load(['doctor.user', 'specialty']);

        return view('patient.reschedule', compact('appointment', 'patientProfile'));
    }

    /**
     * Process reschedule appointment request.
     */
    public function processReschedule(\App\Http\Requests\RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        $patientProfile = PatientProfile::where('user_id', $user->id)->first();

        if (!$patientProfile || $appointment->patient_profile_id !== $patientProfile->id) {
            abort(403, 'Bạn không có quyền thực hiện đổi lịch hẹn này.');
        }

        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])) {
            return back()->with('error', 'Chỉ có thể đổi lịch hẹn đang chờ tiếp nhận hoặc đã xác nhận.');
        }

        $validated = $request->validated();
        $newDate = \Carbon\Carbon::parse($validated['appointment_date']);
        $dayOfWeek = strtolower($newDate->englishDayOfWeek);

        $oldDate = $appointment->appointment_date instanceof \Carbon\Carbon
            ? $appointment->appointment_date->toDateString()
            : (string) $appointment->appointment_date;
        $oldShift = $appointment->shift;

        if ($oldDate === $newDate->toDateString() && $oldShift === $validated['shift']) {
            return back()->withInput()->with('error', 'Ngày khám và ca khám mới trùng với lịch hẹn hiện tại. Vui lòng chọn ngày hoặc ca khám khác.');
        }

        // Check if doctor has schedule on new date
        $schedule = \App\Models\DoctorSchedule::where('doctor_id', $appointment->doctor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('shift', $validated['shift'])
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return back()->withInput()->with('error', 'Bác sĩ không có lịch khám trong ca ' . ($validated['shift'] === 'morning' ? 'Sáng' : 'Chiều') . ' ngày ' . $newDate->format('d/m/Y') . '.');
        }

        // Check capacity on new date
        $existingCount = Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', $newDate->toDateString())
            ->where('shift', $validated['shift'])
            ->where('id', '!=', $appointment->id)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->where('status', '!=', 'waitlist')
            ->count();

        if ($existingCount >= $schedule->max_patients) {
            return back()->withInput()->with('error', "Ca khám mục tiêu ngày {$newDate->format('d/m/Y')} đã nhận đủ số lượng ({$schedule->max_patients}/{$schedule->max_patients} bệnh nhân). Vui lòng chọn ngày hoặc ca khác.");
        }

        // Perform reschedule update
        $appointment->update([
            'appointment_date' => $newDate->toDateString(),
            'shift' => $validated['shift'],
            'rescheduled_at' => now(),
            'rescheduled_from_code' => $appointment->appointment_code,
            'symptom_description' => $validated['reschedule_reason']
                ? $appointment->symptom_description . " [Đổi lịch: {$validated['reschedule_reason']}]"
                : $appointment->symptom_description,
        ]);

        // Promote waitlist on the old slot since a spot just opened up!
        try {
            app(\App\Services\WaitlistEngineService::class)->promoteNextCandidate(
                $appointment->doctor_id,
                $oldDate,
                $oldShift
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Waitlist auto-promotion failed on reschedule: " . $e->getMessage());
        }

        // Send notification email
        if ($appointment->patientProfile && $appointment->patientProfile->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->patientProfile->email)
                    ->send(new \App\Mail\AppointmentRescheduledMail($appointment, $oldDate, $oldShift));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Reschedule mail failed: " . $e->getMessage());
            }
        }

        return redirect()->route('patient.history')->with('success', "Đổi lịch khám cho phiếu {$appointment->appointment_code} thành công sang ngày {$newDate->format('d/m/Y')}!");
    }
}
