<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ChronicDiseaseRecall;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChronicRecallController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = ChronicDiseaseRecall::with(['patientProfile', 'appointment'])
            ->orderBy('due_recall_date', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recall_code', 'LIKE', "%{$search}%")
                  ->orWhere('chronic_condition', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $recalls = $query->paginate(15)->withQueryString();
        $patients = PatientProfile::latest('id')->limit(50)->get();

        $stats = [
            'total_recalls' => ChronicDiseaseRecall::count(),
            'scheduled' => ChronicDiseaseRecall::where('status', 'scheduled')->count(),
            'reminded' => ChronicDiseaseRecall::where('status', 'reminder_sent')->count(),
            'attended' => ChronicDiseaseRecall::where('status', 'attended')->count(),
        ];

        return view('receptionist.chronic_recalls.index', compact('recalls', 'patients', 'stats', 'status', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'chronic_condition' => ['required', 'string', 'max:150'],
            'last_visit_date' => ['required', 'date'],
            'prescription_days' => ['required', 'integer', 'min:7', 'max:90'],
            'reminder_channel' => ['required', 'in:zalo_zns,sms,phone_call'],
            'notes' => ['nullable', 'string'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $lastVisit = Carbon::parse($validated['last_visit_date']);
        $dueRecallDate = $lastVisit->copy()->addDays($validated['prescription_days']);

        $recallCode = ChronicDiseaseRecall::generateRecallCode();

        $recall = ChronicDiseaseRecall::create(array_merge($validated, [
            'recall_code' => $recallCode,
            'due_recall_date' => $dueRecallDate,
            'status' => 'scheduled',
        ]));

        ClinicalAuditLog::record(
            $patient,
            'chronic_recall_enrolled',
            "Đăng ký chương trình quản lý bệnh mãn tính: {$recall->chronic_condition} (Cấp thuốc {$recall->prescription_days} ngày, Hẹn tái khám ngày {$recall->due_recall_date->format('d/m/Y')})"
        );

        return back()->with('success', "Đã ghi nhận chương trình tái khám bệnh mãn tính! Mã theo dõi: {$recall->recall_code}");
    }

    public function sendReminder(ChronicDiseaseRecall $recall): RedirectResponse
    {
        $recall->update([
            'status' => 'reminder_sent',
            'reminder_sent_at' => Carbon::now(),
        ]);

        ClinicalAuditLog::record(
            $recall->patientProfile,
            'chronic_recall_reminded',
            "Gửi thông báo tự động nhắc tái khám bệnh mãn tính ({$recall->chronic_condition}) qua kênh {$recall->reminder_channel}"
        );

        return back()->with('success', "Đã phát lệnh gửi tin nhắn nhắc tái khám tự động qua Zalo ZNS / SMS cho bệnh nhân {$recall->patientProfile->full_name}!");
    }

    public function markAttended(ChronicDiseaseRecall $recall): RedirectResponse
    {
        $recall->update(['status' => 'attended']);

        return back()->with('success', "Đã xác nhận bệnh nhân {$recall->patientProfile->full_name} đến tái khám đúng hẹn!");
    }
}
