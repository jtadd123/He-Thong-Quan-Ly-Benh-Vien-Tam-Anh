<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalAuditLog;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Services\HealthInsuranceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceptionAppointmentController extends Controller
{
    /**
     * Display appointment check-in queue for reception desk.
     */
    public function index(Request $request): View
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $status = $request->query('status');
        $shift = $request->query('shift');
        $specialtyId = $request->query('specialty_id');
        $search = $request->query('search');

        $query = Appointment::with(['patientProfile.latestVital', 'doctor.user', 'specialty'])
            ->whereDate('appointment_date', $date)
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($shift) {
            $query->where('shift', $shift);
        }

        if ($specialtyId) {
            $query->where('specialty_id', $specialtyId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%")
                         ->orWhere('patient_code', 'LIKE', "%{$search}%")
                         ->orWhere('identity_card', 'LIKE', "%{$search}%");
                  });
            });
        }

        $appointments = $query->paginate(15)->withQueryString();

        // Statistics for quick status tabs
        $stats = [
            'total' => Appointment::whereDate('appointment_date', $date)->count(),
            'pending' => Appointment::whereDate('appointment_date', $date)->where('status', Appointment::STATUS_PENDING)->count(),
            'confirmed' => Appointment::whereDate('appointment_date', $date)->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'completed' => Appointment::whereDate('appointment_date', $date)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => Appointment::whereDate('appointment_date', $date)->where('status', Appointment::STATUS_CANCELLED)->count(),
        ];

        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with(['user', 'specialty'])->where('is_available', true)->get();

        // Chart Analytics for reception dashboard
        $shiftChartData = [
            'morning' => Appointment::whereDate('appointment_date', $date)->where('shift', 'morning')->count(),
            'afternoon' => Appointment::whereDate('appointment_date', $date)->where('shift', 'afternoon')->count(),
        ];

        $specialtyChartData = Specialty::withCount(['appointments' => function ($q) use ($date) {
            $q->whereDate('appointment_date', $date);
        }])->get()->filter(function ($sp) {
            return $sp->appointments_count > 0;
        })->values();

        return view('receptionist.appointments.index', compact(
            'appointments',
            'date',
            'status',
            'shift',
            'specialtyId',
            'search',
            'stats',
            'specialties',
            'doctors',
            'shiftChartData',
            'specialtyChartData'
        ));
    }

    /**
     * Show form to receive a walk-in patient at the desk.
     */
    public function createWalkin(Request $request): View
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with(['user', 'specialty'])->where('is_available', true)->get();

        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = PatientProfile::find($request->query('patient_id'));
        }

        $isFollowUp = $request->boolean('follow_up');

        return view('receptionist.appointments.walkin', compact('specialties', 'doctors', 'selectedPatient', 'isFollowUp'));
    }

    /**
     * Store walk-in appointment and automatically confirm it.
     */
    public function storeWalkin(Request $request): RedirectResponse
    {
        $isExisting = $request->boolean('is_existing_patient');

        $rules = [
            'specialty_id' => ['required', 'exists:specialties,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'shift' => ['required', 'in:morning,afternoon'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'symptom_description' => ['required', 'string', 'max:1000'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string', 'max:1000'],
        ];

        if ($isExisting) {
            $rules['patient_profile_id'] = ['required', 'exists:patient_profiles,id'];
        } else {
            $rules['full_name'] = ['required', 'string', 'max:100'];
            $rules['dob'] = ['required', 'date', 'before_or_equal:today'];
            $rules['gender'] = ['required', 'in:male,female,other'];
            $rules['phone'] = ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/', 'max:20'];
            $rules['email'] = ['nullable', 'email', 'max:150'];
            $rules['address'] = ['nullable', 'string', 'max:255'];
            $rules['identity_card'] = ['nullable', 'string', 'max:20'];
            $rules['insurance_code'] = ['nullable', 'string', 'max:30'];
        }

        $validated = $request->validate($rules, [
            'specialty_id.required' => 'Vui lòng chọn chuyên khoa khám.',
            'doctor_id.required' => 'Vui lòng chọn bác sĩ phụ trách.',
            'appointment_date.required' => 'Vui lòng chọn ngày khám.',
            'shift.required' => 'Vui lòng chọn ca khám.',
            'symptom_description.required' => 'Vui lòng nhập mô tả triệu chứng hoặc lý do khám.',
            'patient_profile_id.required' => 'Vui lòng chọn hồ sơ bệnh nhân.',
            'full_name.required' => 'Họ và tên bệnh nhân không được để trống.',
            'dob.required' => 'Ngày sinh không được để trống.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
        ]);

        // Overbooking slot capacity check
        $doctor = Doctor::where('id', $validated['doctor_id'])
            ->where('specialty_id', $validated['specialty_id'])
            ->where('is_available', true)
            ->first();

        if (!$doctor) {
            return back()->withInput()->with('error', 'Bác sĩ được chọn không trực thuộc chuyên khoa này hoặc hiện đang tạm ngưng nhận khám.');
        }

        $dayOfWeek = strtolower(Carbon::parse($validated['appointment_date'])->englishDayOfWeek);
        $schedule = DoctorSchedule::where('doctor_id', $validated['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('shift', $validated['shift'])
            ->where('is_active', true)
            ->first();

        $maxPatients = $schedule ? $schedule->max_patients : 20;
        $currentBookings = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('shift', $validated['shift'])
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->count();

        if ($currentBookings >= $maxPatients) {
            return back()->withInput()->with('error', "Bác sĩ {$doctor->full_name_with_title} đã kín lịch trong ca này (Đã tiếp nhận {$currentBookings}/{$maxPatients} ca). Vui lòng chọn ca hoặc bác sĩ khác.");
        }

        $appointment = DB::transaction(function () use ($isExisting, $validated, $request) {
            if ($isExisting) {
                $patientProfile = PatientProfile::findOrFail($validated['patient_profile_id']);
            } else {
                $patientProfile = PatientProfile::where('phone', $validated['phone'])->first();

                if (!$patientProfile) {
                    $patientProfile = PatientProfile::create([
                        'patient_code' => PatientProfile::generatePatientCode(),
                        'full_name' => $validated['full_name'],
                        'dob' => $validated['dob'],
                        'gender' => $validated['gender'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'identity_card' => $validated['identity_card'] ?? null,
                        'insurance_code' => $validated['insurance_code'] ?? null,
                        'blood_group' => $validated['blood_group'] ?? null,
                        'allergies' => $validated['allergies'] ?? null,
                        'medical_history' => $validated['medical_history'] ?? null,
                    ]);
                }
            }

            // Update clinical safety fields if provided
            $dirtyProfile = false;
            if (!empty($validated['blood_group']) && $patientProfile->blood_group !== $validated['blood_group']) {
                $patientProfile->blood_group = $validated['blood_group'];
                $dirtyProfile = true;
            }
            if (isset($validated['allergies']) && $patientProfile->allergies !== $validated['allergies']) {
                $patientProfile->allergies = $validated['allergies'];
                $dirtyProfile = true;
            }
            if (isset($validated['medical_history']) && $patientProfile->medical_history !== $validated['medical_history']) {
                $patientProfile->medical_history = $validated['medical_history'];
                $dirtyProfile = true;
            }
            if ($dirtyProfile) {
                $patientProfile->save();
            }

            $symptom = $validated['symptom_description'];
            if ($request->boolean('is_priority') && !str_contains($symptom, '[ƯU TIÊN]')) {
                $symptom = '[ƯU TIÊN] ' . $symptom;
            }

            // Tự động phân loại cấp cứu chuẩn ESI
            $latestVital = $patientProfile->latestVital;
            $esiResult = \App\Services\EmergencyTriageService::classify($patientProfile, $symptom, $latestVital);

            $newAppointment = Appointment::create([
                'appointment_code' => Appointment::generateAppointmentCode(),
                'patient_profile_id' => $patientProfile->id,
                'doctor_id' => $validated['doctor_id'],
                'specialty_id' => $validated['specialty_id'],
                'appointment_date' => $validated['appointment_date'],
                'shift' => $validated['shift'],
                'appointment_time' => $validated['appointment_time'] ?? Carbon::now()->format('H:i'),
                'symptom_description' => $symptom,
                'status' => Appointment::STATUS_CONFIRMED, // Walk-in is auto check-in
                'created_by_user_id' => Auth::id(),
                'esi_level' => $esiResult['level'],
                'esi_criteria' => $esiResult['criteria'],
                'triage_notes' => $esiResult['action_protocol'],
                'triaged_by_user_id' => Auth::id(),
                'triaged_at' => now(),
            ]);

            ClinicalAuditLog::record(
                $patientProfile,
                'checkin',
                "Tiếp nhận vãng lai tại quầy (Mã hẹn: {$newAppointment->appointment_code}) vào BS {$newAppointment->doctor->full_name_with_title}"
            );

            return $newAppointment;
        });

        return redirect()
            ->route('reception.appointments.index', ['date' => $appointment->appointment_date->toDateString()])
            ->with('success', "Tiếp nhận trực tiếp thành công cho bệnh nhân {$appointment->patientProfile->full_name} (Mã hẹn: {$appointment->appointment_code}). Đã tự động Check-in vào hàng đợi bác sĩ.");
    }

    /**
     * Receptionist checks in a patient when they arrive at hospital.
     */
    public function checkin(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return back()->with('error', 'Không thể check-in lịch hẹn đã bị hủy.');
        }

        $appointment->update([
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        ClinicalAuditLog::record(
            $appointment->patient_profile_id,
            'checkin',
            "Check-in tiếp nhận lịch hẹn {$appointment->appointment_code} vào hàng đợi Bác sĩ {$appointment->doctor->full_name_with_title}"
        );

        return back()->with('success', "Đã Check-in thành công cho bệnh nhân {$appointment->patientProfile->full_name} (Mã hẹn: {$appointment->appointment_code}). Bệnh nhân đã được chuyển vào danh sách chờ khám của bác sĩ.");
    }

    /**
     * Receptionist cancels an appointment with reason.
     */
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ], [
            'cancellation_reason.required' => 'Vui lòng cung cấp lý do hủy ca khám.',
        ]);

        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => 'Lễ tân hủy: ' . $request->input('cancellation_reason'),
        ]);

        return back()->with('success', "Đã hủy lịch hẹn {$appointment->appointment_code}.");
    }

    /**
     * Reassign an appointment to a new doctor, shift, or date.
     */
    public function reassign(Request $request, Appointment $appointment): RedirectResponse
    {
        if (in_array($appointment->status, [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED])) {
            return back()->with('error', 'Không thể điều phối ca khám đã hoàn tất hoặc đã bị hủy.');
        }

        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date'],
            'shift' => ['required', 'in:morning,afternoon'],
            'reassign_reason' => ['nullable', 'string', 'max:255'],
        ], [
            'doctor_id.required' => 'Vui lòng chọn bác sĩ điều phối mới.',
            'appointment_date.required' => 'Vui lòng chọn ngày khám.',
            'shift.required' => 'Vui lòng chọn ca khám.',
        ]);

        $newDoctor = Doctor::findOrFail($validated['doctor_id']);

        if (!$newDoctor->is_available) {
            return back()->with('error', "Bác sĩ {$newDoctor->full_name_with_title} hiện đang tạm ngưng nhận lịch khám.");
        }

        $reason = $validated['reassign_reason'] ? " (Lý do: {$validated['reassign_reason']})" : '';

        $appointment->update([
            'doctor_id' => $newDoctor->id,
            'specialty_id' => $newDoctor->specialty_id,
            'appointment_date' => $validated['appointment_date'],
            'shift' => $validated['shift'],
            'cancellation_reason' => $appointment->cancellation_reason
                ? $appointment->cancellation_reason . " | Điều phối lại sang BS {$newDoctor->full_name_with_title}{$reason}"
                : "Điều phối lại sang BS {$newDoctor->full_name_with_title}{$reason}",
        ]);

        return back()->with('success', "Đã điều phối lịch hẹn {$appointment->appointment_code} sang Bác sĩ {$newDoctor->full_name_with_title} thành công.");
    }

    /**
     * Print OPD consultation ticket for patient at reception.
     */
    public function printTicket(Appointment $appointment): View
    {
        $appointment->load(['patientProfile', 'doctor.user', 'specialty']);

        return view('receptionist.appointments.print', compact('appointment'));
    }

    /**
     * Export daily appointments to CSV/Excel format.
     */
    public function export(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $status = $request->query('status');

        $query = Appointment::with(['patientProfile', 'doctor.user', 'specialty'])
            ->whereDate('appointment_date', $date)
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->get();

        $fileName = "BaoCao_TiepNhan_TamAnh_{$date}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'STT Khám',
                'Mã Lịch Hẹn',
                'Mã Bệnh Nhân',
                'Họ và Tên',
                'Ngày Sinh',
                'Giới Tính',
                'Số Điện Thoại',
                'Số CCCD',
                'Mã Thẻ BHYT',
                'Chuyên Khoa',
                'Bác Sĩ Khám',
                'Phòng Khám',
                'Ca Khám',
                'Giờ Hẹn',
                'Triệu Chứng',
                'Trạng Thái',
                'Ghi Chú / Lý Do Hủy',
            ]);

            foreach ($appointments as $app) {
                fputcsv($file, [
                    $app->queue_number,
                    $app->appointment_code,
                    $app->patientProfile ? $app->patientProfile->patient_code : '',
                    $app->patientProfile ? $app->patientProfile->full_name : '',
                    $app->patientProfile && $app->patientProfile->dob ? $app->patientProfile->dob->format('d/m/Y') : '',
                    $app->patientProfile ? $app->patientProfile->gender_name : '',
                    $app->patientProfile ? $app->patientProfile->phone : '',
                    $app->patientProfile ? $app->patientProfile->identity_card : '',
                    $app->patientProfile ? $app->patientProfile->insurance_code : '',
                    $app->specialty ? $app->specialty->name : '',
                    $app->doctor ? $app->doctor->full_name_with_title : '',
                    $app->doctor ? $app->doctor->room_number : '',
                    $app->shift_name,
                    $app->appointment_time ? substr($app->appointment_time, 0, 5) : 'Trong ca',
                    $app->symptom_description,
                    $app->status_name,
                    $app->cancellation_reason ?: '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Live display board for waiting room / reception kiosk.
     */
    public function queueDisplay(Request $request): View
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $waitingAppointments = Appointment::with(['patientProfile', 'doctor.user', 'specialty'])
            ->whereDate('appointment_date', $date)
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->orderBy('id', 'asc')
            ->get();

        $recentCompleted = Appointment::with(['patientProfile', 'doctor.user', 'specialty'])
            ->whereDate('appointment_date', $date)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('receptionist.appointments.queue_display', compact('waitingAppointments', 'recentCompleted', 'date'));
    }

    /**
     * Print patient fee receipt / invoice with Health Insurance (BHYT) co-payment and VietQR payment.
     */
    public function printReceipt(Appointment $appointment): View
    {
        $appointment->load(['patientProfile', 'doctor.user', 'specialty']);
        $totalFee = (float) ($appointment->doctor ? $appointment->doctor->consultation_fee : 350000);

        $insuranceCode = $appointment->patientProfile->insurance_code ?? null;
        $routeParam = request()->query('route', 'correct'); // 'correct', 'incorrect', 'none'
        $useInsurance = $routeParam !== 'none' && !empty($insuranceCode);
        $isCorrectRoute = $routeParam === 'correct';

        $insuranceData = null;
        if ($useInsurance && $insuranceCode) {
            $insuranceData = HealthInsuranceService::calculate($insuranceCode, $totalFee, $isCorrectRoute);
            $payableAmount = $insuranceData['patient_copay_amount'];
        } else {
            $payableAmount = $totalFee;
        }

        $fee = $totalFee;
        $feeWords = $this->convertNumberToVietnameseWords((int) $payableAmount) . ' đồng chẵn';
        $vietQrUrl = "https://img.vietqr.io/image/vietinbank-113366668888-compact2.png?amount={$payableAmount}&addInfo=TAMANH%20" . urlencode($appointment->appointment_code) . "&accountName=BV%20DA%20KHOA%20TAM%20ANH";

        return view('receptionist.appointments.receipt', compact('appointment', 'fee', 'payableAmount', 'feeWords', 'vietQrUrl', 'insuranceData', 'routeParam'));
    }

    private function convertNumberToVietnameseWords(int $number): string
    {
        if ($number === 0) return 'Không';
        $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

        $thousands = (int) floor($number / 1000);
        $remainder = $number % 1000;

        if ($thousands > 0 && $remainder === 0) {
            $hundreds = (int) floor($thousands / 100);
            $tens = (int) floor(($thousands % 100) / 10);
            $ones = $thousands % 10;

            $text = '';
            if ($hundreds > 0) $text .= $digits[$hundreds] . ' trăm ';
            if ($tens > 1) $text .= $digits[$tens] . ' mươi ';
            elseif ($tens === 1) $text .= 'mười ';
            elseif ($tens === 0 && $hundreds > 0 && $ones > 0) $text .= 'lẻ ';

            if ($ones === 5 && $tens > 0) $text .= 'lăm ';
            elseif ($ones === 1 && $tens > 1) $text .= 'mốt ';
            elseif ($ones > 0) $text .= $digits[$ones] . ' ';

            $text .= 'nghìn';
            return ucfirst(trim($text));
        }

        return number_format($number, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Phát lệnh gọi loa âm thanh AI & chuông Ding-Dong đến sảnh tiếp nhận.
     */
    public function callAudio(Appointment $appointment, Request $request)
    {
        $counter = $request->input('counter_number', 'Quầy 01');
        $appointment->update([
            'called_at' => now(),
            'counter_number' => $counter,
        ]);

        $patientName = $appointment->patientProfile->full_name;
        $stt = $appointment->queue_number;
        $speechText = "Kính mời bệnh nhân {$patientName}, số thứ tự " . sprintf('%02d', $stt) . ", vui lòng đến {$counter}!";

        return response()->json([
            'success' => true,
            'patient_name' => $patientName,
            'stt' => $stt,
            'counter' => $counter,
            'speech_text' => $speechText,
            'called_at' => $appointment->called_at->format('H:i:s'),
        ]);
    }
}
