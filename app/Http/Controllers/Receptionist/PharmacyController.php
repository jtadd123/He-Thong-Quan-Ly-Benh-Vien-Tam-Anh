<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Prescription;
use App\Services\AllergyCrossReactionService;
use App\Services\PrescriptionLabelService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    /**
     * Outpatient Pharmacy Dispensing Queue & Dashboard.
     */
    public function index(Request $request): View
    {
        $dateFilter = $request->input('date', Carbon::today()->format('Y-m-d'));
        $statusFilter = $request->input('status', 'all'); // 'all', 'pending', 'dispensed'
        $search = trim($request->input('q', ''));

        $query = MedicalRecord::has('prescriptions')
            ->with([
                'appointment.specialty',
                'appointment.doctor.user',
                'patientProfile',
                'doctor.user',
                'prescriptions',
                'dispensedBy',
            ]);

        // Filter by Date (appointment date or record created date)
        if (!empty($dateFilter)) {
            $query->whereDate('created_at', $dateFilter);
        }

        // Filter by Status
        if ($statusFilter === 'pending') {
            $query->whereNull('dispensed_at');
        } elseif ($statusFilter === 'dispensed') {
            $query->whereNotNull('dispensed_at');
        }

        // Search keyword
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                    ->orWhereHas('patientProfile', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('patient_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('prescriptions', function ($rxq) use ($search) {
                        $rxq->where('medication_name', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->orderByRaw('dispensed_at IS NULL DESC')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Statistical KPI Counters for Today
        $todayBaseQuery = MedicalRecord::has('prescriptions')->whereDate('created_at', Carbon::today());
        $pendingCount = (clone $todayBaseQuery)->whereNull('dispensed_at')->count();
        $dispensedTodayCount = (clone $todayBaseQuery)->whereNotNull('dispensed_at')->count();
        $totalTodayCount = $pendingCount + $dispensedTodayCount;

        return view('receptionist.pharmacy.index', compact(
            'records',
            'dateFilter',
            'statusFilter',
            'search',
            'pendingCount',
            'dispensedTodayCount',
            'totalTodayCount'
        ));
    }

    /**
     * Mark a prescription record as dispensed with FEFO safety guard.
     */
    public function dispense(Request $request, MedicalRecord $record): RedirectResponse
    {
        $validated = $request->validate([
            'dispense_notes' => ['nullable', 'string', 'max:255'],
        ]);

        $record->load(['prescriptions', 'patientProfile']);

        // 1. FEFO Expiry Safety Guard: Block expired or critical near-expired medications (<=30 days)
        foreach ($record->prescriptions as $p) {
            $firstWord = explode(' ', trim($p->medication_name))[0] ?? '';
            if (mb_strlen($firstWord) >= 3) {
                $med = Medication::where('name', 'like', "%{$firstWord}%")->first();
                if ($med && !$med->fefo_status['is_dispensable']) {
                    $expStr = $med->expiry_date ? $med->expiry_date->format('d/m/Y') : 'Không xác định';
                    return back()->with('error', "CẢNH BÁO AN TOÀN DƯỢC (GPP/JCI): Thuốc [{$med->name}] (Số Lô: {$med->batch_number}) {$med->fefo_status['label']} (HSD: {$expStr}). Hệ thống khóa tự động, nghiêm cấm xuất cấp phát cho bệnh nhân!");
                }
            }
        }

        $record->update([
            'dispensed_at' => Carbon::now(),
            'dispensed_by_user_id' => Auth::id(),
            'dispense_notes' => $validated['dispense_notes'] ?? 'Đã kiểm tra đủ số lượng và hướng dẫn sử dụng cho bệnh nhân.',
        ]);

        // Real-time automated medication inventory deduction
        foreach ($record->prescriptions as $p) {
            $firstWord = explode(' ', trim($p->medication_name))[0] ?? '';
            if (mb_strlen($firstWord) >= 3) {
                $med = Medication::where('name', 'like', "%{$firstWord}%")->first();
                if ($med && $med->stock_quantity > 0) {
                    $deductQty = max(1, (int)$p->duration_days * 2);
                    $actualDeduct = min($med->stock_quantity, $deductQty);
                    $med->decrement('stock_quantity', $actualDeduct);
                }
            }
        }

        // Audit Trail: Record medication dispensing
        ClinicalAuditLog::record(
            $record->patient_profile_id,
            'dispense',
            "Cấp phát thuốc ngoại trú cho bệnh án {$record->record_code} ({$record->prescriptions->count()} mặt hàng thuốc)",
            null,
            ['dispense_notes' => $record->dispense_notes, 'record_code' => $record->record_code]
        );

        return back()->with('success', "Đã xác nhận cấp phát thuốc thành công cho bệnh nhân {$record->patientProfile->full_name} (Mã đơn: {$record->record_code}). Đã cập nhật tồn kho dược ngoại trú.");
    }

    /**
     * Revert / Undo dispense status if clicked mistakenly.
     */
    public function undoDispense(MedicalRecord $record): RedirectResponse
    {
        $record->load('prescriptions');
        foreach ($record->prescriptions as $p) {
            $firstWord = explode(' ', trim($p->medication_name))[0] ?? '';
            if (mb_strlen($firstWord) >= 3) {
                $med = Medication::where('name', 'like', "%{$firstWord}%")->first();
                if ($med) {
                    $restoreQty = max(1, (int)$p->duration_days * 2);
                    $med->increment('stock_quantity', $restoreQty);
                }
            }
        }

        $record->update([
            'dispensed_at' => null,
            'dispensed_by_user_id' => null,
            'dispense_notes' => null,
        ]);

        // Audit Trail: Record undo dispensing
        ClinicalAuditLog::record(
            $record->patient_profile_id,
            'undo_dispense',
            "Hoàn tác trạng thái cấp phát thuốc về chờ xử lý cho bệnh án {$record->record_code}"
        );

        return back()->with('success', "Đã hoàn tác trạng thái cấp phát thuốc về 'Chờ phát thuốc' cho mã {$record->record_code} và hoàn lại tồn kho dược.");
    }

    /**
     * Print prescription drug label (Decal / A5 bag label).
     */
    public function printLabel(MedicalRecord $record): View
    {
        $record->load([
            'appointment.specialty',
            'patientProfile',
            'doctor.user',
            'prescriptions',
            'dispensedBy',
        ]);

        return view('receptionist.pharmacy.label', compact('record'));
    }

    /**
     * Phát lệnh gọi loa nhận thuốc tại quầy Dược ngoại trú.
     */
    public function callAudio(MedicalRecord $record, Request $request)
    {
        $counter = $request->input('counter_number', 'Quầy Dược 01');
        $patientName = $record->patientProfile->full_name;
        $speechText = "Kính mời bệnh nhân {$patientName}, mã đơn {$record->record_code}, vui lòng đến {$counter} nhận thuốc và nghe tư vấn sử dụng!";

        return response()->json([
            'success' => true,
            'patient_name' => $patientName,
            'record_code' => $record->record_code,
            'counter' => $counter,
            'speech_text' => $speechText,
        ]);
    }

    /**
     * In toàn bộ tem nhãn dán hộp thuốc thông minh theo chuẩn decal nhiệt 75mm x 50mm.
     */
    public function printBottleLabels(MedicalRecord $record, PrescriptionLabelService $labelService): View
    {
        $labels = $labelService->generateLabelsForRecord($record);
        return view('receptionist.pharmacy.bottle_labels', compact('record', 'labels'));
    }

    /**
     * In một tem nhãn dán hộp thuốc đơn lẻ.
     */
    public function printSingleBottleLabel(Prescription $prescription, PrescriptionLabelService $labelService): View
    {
        $record = $prescription->medicalRecord;
        $record->load(['appointment.patientProfile', 'appointment.doctor.specialty']);
        $patient = $record->patientProfile ?? $record->appointment->patientProfile;
        $doctor = $record->doctor ?? $record->appointment->doctor;

        $label = $labelService->buildSingleLabelData($record, $prescription, $patient, $doctor);
        $labels = [$label];

        return view('receptionist.pharmacy.bottle_labels', compact('record', 'labels'));
    }
}
