<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\PatientVital;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Display a listing of patient profiles with advanced search and filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $gender = $request->query('gender');
        $insurance = $request->query('insurance');
        $sort = $request->query('sort', 'latest');

        $query = PatientProfile::with('user')
            ->withCount(['appointments', 'medicalRecords']);

        // Gender filter
        if ($gender) {
            $query->where('gender', $gender);
        }

        // Insurance filter
        if ($insurance === 'yes') {
            $query->whereNotNull('insurance_code')->where('insurance_code', '!=', '');
        } elseif ($insurance === 'no') {
            $query->where(function ($q) {
                $q->whereNull('insurance_code')->orWhere('insurance_code', '');
            });
        }

        // Full-text search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_code', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('identity_card', 'LIKE', "%{$search}%")
                  ->orWhere('insurance_code', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        match ($sort) {
            'name_asc' => $query->orderBy('full_name', 'asc'),
            'name_desc' => $query->orderBy('full_name', 'desc'),
            'dob_asc' => $query->orderBy('dob', 'asc'),
            'dob_desc' => $query->orderBy('dob', 'desc'),
            'visits_desc' => $query->orderBy('appointments_count', 'desc'),
            default => $query->latest('id'),
        };

        $patients = $query->paginate(15)->withQueryString();

        // Summary statistics
        $stats = [
            'total' => PatientProfile::count(),
            'male' => PatientProfile::where('gender', 'male')->count(),
            'female' => PatientProfile::where('gender', 'female')->count(),
            'insured' => PatientProfile::whereNotNull('insurance_code')->where('insurance_code', '!=', '')->count(),
            'new_this_month' => PatientProfile::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        return view('receptionist.patients.index', compact('patients', 'search', 'gender', 'insurance', 'sort', 'stats'));
    }

    /**
     * Show the form for creating a new patient profile.
     */
    public function create(): View
    {
        return view('receptionist.patients.create');
    }

    /**
     * Store a newly created patient profile in storage.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Check potential duplicate phone number or identity card for awareness
        if (!empty($data['identity_card'])) {
            $existingCccd = PatientProfile::where('identity_card', $data['identity_card'])->first();
            if ($existingCccd) {
                return back()->withInput()->with('error', "Số CCCD/CMND {$data['identity_card']} đã tồn tại trong hệ thống (thuộc bệnh nhân {$existingCccd->full_name} - Mã: {$existingCccd->patient_code}). Vui lòng kiểm tra lại hồ sơ.");
            }
        }

        $data['patient_code'] = PatientProfile::generatePatientCode();

        $patient = PatientProfile::create($data);

        // Audit Trail: Log patient creation
        ClinicalAuditLog::record($patient, 'create', "Tạo mới hồ sơ bệnh nhân {$patient->full_name} ({$patient->patient_code})", null, $data);

        return redirect()
            ->route('reception.patients.show', $patient)
            ->with('success', "Hồ sơ bệnh nhân {$patient->full_name} đã được tạo thành công với mã định danh: {$patient->patient_code}");
    }

    /**
     * Display the specified patient profile with comprehensive medical history.
     */
    public function show(PatientProfile $patient): View
    {
        $patient->load([
            'user',
            'appointments' => function ($q) {
                $q->with(['doctor.user', 'specialty'])->latest('appointment_date');
            },
            'medicalRecords' => function ($q) {
                $q->with(['doctor.user', 'prescriptions', 'attachments'])->latest('created_at');
            },
            'vitals.recordedBy',
            'auditLogs.user',
        ]);

        // Audit Trail: Record view access log
        ClinicalAuditLog::record($patient, 'view', "Truy cập tra cứu hồ sơ bệnh nhân {$patient->patient_code}");

        // Auto-extract vitals from past records if no structured vitals exist yet
        if ($patient->vitals->isEmpty() && $patient->medicalRecords->isNotEmpty()) {
            foreach ($patient->medicalRecords as $record) {
                $text = ($record->symptoms ?? '') . ' ' . ($record->clinical_notes ?? '');
                $extracted = self::extractVitalsFromText($text);
                if (!empty($extracted['systolic_bp']) || !empty($extracted['heart_rate'])) {
                    PatientVital::create([
                        'patient_profile_id' => $patient->id,
                        'appointment_id' => $record->appointment_id,
                        'recorded_by_user_id' => $record->doctor?->user_id,
                        'recorded_at' => $record->created_at,
                        'systolic_bp' => $extracted['systolic_bp'] ?? null,
                        'diastolic_bp' => $extracted['diastolic_bp'] ?? null,
                        'heart_rate' => $extracted['heart_rate'] ?? null,
                        'temperature' => $extracted['temperature'] ?? null,
                        'spo2' => $extracted['spo2'] ?? null,
                        'notes' => 'Trích xuất tự động từ bệnh án ' . $record->record_code,
                    ]);
                }
            }
            $patient->load('vitals.recordedBy');
        }

        $completedVisits = $patient->appointments->where('status', 'completed')->count();
        $latestAppointment = $patient->appointments->first();

        // Prepare EHR Vitals Timeline & Chart datasets (ordered chronologically for line charts)
        $chronologicalVitals = $patient->vitals->sortBy('recorded_at')->values();
        $vitalsChartData = [
            'labels' => $chronologicalVitals->map(fn($v) => $v->recorded_at->format('d/m/Y H:i'))->toArray(),
            'systolic' => $chronologicalVitals->map(fn($v) => $v->systolic_bp)->toArray(),
            'diastolic' => $chronologicalVitals->map(fn($v) => $v->diastolic_bp)->toArray(),
            'heartRate' => $chronologicalVitals->map(fn($v) => $v->heart_rate)->toArray(),
            'temperature' => $chronologicalVitals->map(fn($v) => $v->temperature)->toArray(),
            'spo2' => $chronologicalVitals->map(fn($v) => $v->spo2)->toArray(),
        ];

        $latestVital = $patient->vitals->first(); // latest by recorded_at desc

        return view('receptionist.patients.show', compact(
            'patient',
            'completedVisits',
            'latestAppointment',
            'vitalsChartData',
            'latestVital'
        ));
    }

    /**
     * Regex helper to parse clinical vitals from unstructured doctor/nurse notes.
     */
    public static function extractVitalsFromText(string $text): array
    {
        $res = [];
        // BP: Huyết áp 140/90 mmHg hoặc HA: 120/80
        if (preg_match('/(?:Huyết\s*áp|HA|BP)[:\s]*([0-9]{2,3})\s*[\/\-]\s*([0-9]{2,3})/iu', $text, $m)) {
            $res['systolic_bp'] = (int)$m[1];
            $res['diastolic_bp'] = (int)$m[2];
        }
        // Pulse / Heart rate: nhịp tim 88 ck/phút hoặc Mạch: 78
        if (preg_match('/(?:Mạch|Nhịp\s*tim|HR|Pulse)[:\s]*([0-9]{2,3})/iu', $text, $m)) {
            $res['heart_rate'] = (int)$m[1];
        } elseif (preg_match('/([0-9]{2,3})\s*(?:ck|nhịp|lần)\/phút/iu', $text, $m)) {
            $res['heart_rate'] = (int)$m[1];
        }
        // Temperature: Thân nhiệt 36.8°C hoặc 37.5°C
        if (preg_match('/(?:Thân\s*nhiệt|Nhiệt\s*độ|Nhiệt)[:\s]*([0-9]{2}(?:\.[0-9])?)/iu', $text, $m)) {
            $res['temperature'] = (float)$m[1];
        } elseif (preg_match('/([0-9]{2}(?:\.[0-9])?)\s*°?C/iu', $text, $m)) {
            $res['temperature'] = (float)$m[1];
        }
        // SpO2: SpO2 98%
        if (preg_match('/(?:SpO2|Oxy)[:\s]*([0-9]{2,3})/iu', $text, $m)) {
            $res['spo2'] = (int)$m[1];
        }
        return $res;
    }

    /**
     * Show the form for editing the specified patient profile.
     */
    public function edit(PatientProfile $patient): View
    {
        return view('receptionist.patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient profile in storage.
     */
    public function update(UpdatePatientRequest $request, PatientProfile $patient): RedirectResponse
    {
        $data = $request->validated();

        // Check if CCCD belongs to another patient
        if (!empty($data['identity_card'])) {
            $duplicateCccd = PatientProfile::where('identity_card', $data['identity_card'])
                ->where('id', '!=', $patient->id)
                ->first();
            if ($duplicateCccd) {
                return back()->withInput()->with('error', "Số CCCD/CMND {$data['identity_card']} đã thuộc về bệnh nhân {$duplicateCccd->full_name} ({$duplicateCccd->patient_code}). Không thể cập nhật trùng.");
            }
        }

        $oldValues = $patient->only(array_keys($data));
        $patient->update($data);

        // Audit Trail: Record changes
        $action = (!empty($data['blood_group']) && ($oldValues['blood_group'] ?? null) !== $data['blood_group'])
            || (!empty($data['allergies']) && ($oldValues['allergies'] ?? null) !== $data['allergies'])
            ? 'update_safety'
            : 'update_profile';

        ClinicalAuditLog::record(
            $patient,
            $action,
            "Cập nhật thông tin hồ sơ bệnh nhân {$patient->full_name} ({$patient->patient_code})",
            $oldValues,
            $data
        );

        return redirect()
            ->route('reception.patients.show', $patient)
            ->with('success', "Cập nhật thông tin bệnh nhân {$patient->full_name} thành công.");
    }

    /**
     * Remove the specified patient profile from storage with relational safety.
     */
    public function destroy(PatientProfile $patient): RedirectResponse
    {
        // Strict database integrity constraint: Cannot delete if any clinical or appointment records exist
        if ($patient->appointments()->exists() 
            || $patient->medicalRecords()->exists()
            || $patient->vaccinationRecords()->exists()
            || $patient->labOrders()->exists()
            || $patient->bloodTransfusions()->exists()
            || $patient->equipmentLoans()->exists()
            || $patient->nutritionAssessments()->exists()) {
            return back()->with('error', "Ràng buộc toàn vẹn dữ liệu: Không thể xóa hồ sơ bệnh nhân {$patient->full_name} ({$patient->patient_code}) vì đã phát sinh dữ liệu lâm sàng (khám bệnh, tiêm chủng, xét nghiệm, truyền máu, thiết bị hoặc dinh dưỡng) trong hệ thống y tế.");
        }

        DB::transaction(function () use ($patient) {
            $patient->vitals()->delete();
            $patient->notifications()->delete();
            $patient->auditLogs()->delete();
            $patient->surveys()->delete();
            $patient->informedConsents()->delete();
            $patient->delete();
        });

        return redirect()
            ->route('reception.patients.index')
            ->with('success', "Đã xóa hồ sơ bệnh nhân {$patient->full_name} ({$patient->patient_code}) thành công.");
    }

    /**
     * Print patient barcode identity card.
     */
    public function printCard(PatientProfile $patient): View
    {
        return view('receptionist.patients.print_card', compact('patient'));
    }

    /**
     * Print International Standard Patient Medical Wristband (JCI IPSG.1).
     */
    public function printWristband(PatientProfile $patient): View
    {
        ClinicalAuditLog::record(
            $patient,
            'print_wristband',
            "In vòng tay nhận diện người bệnh (Mã BN: {$patient->patient_code})"
        );

        return view('receptionist.patients.print_wristband', compact('patient'));
    }
}
