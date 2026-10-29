<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\VisitorPass;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorPassController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = VisitorPass::with(['patientProfile', 'issuedBy'])
            ->latest('id');

        if ($status === 'active') {
            $query->where('status', 'active')->where('valid_to', '>=', Carbon::now());
        } elseif ($status === 'checked_out') {
            $query->where('status', 'checked_out');
        } elseif ($status === 'expired') {
            $query->where('status', 'active')->where('valid_to', '<', Carbon::now());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pass_code', 'LIKE', "%{$search}%")
                  ->orWhere('visitor_name', 'LIKE', "%{$search}%")
                  ->orWhere('visitor_phone', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $passes = $query->paginate(15)->withQueryString();

        $patients = PatientProfile::latest('id')->limit(50)->get();

        $stats = [
            'active_visitors' => VisitorPass::where('status', 'active')->where('valid_to', '>=', Carbon::now())->count(),
            'checked_out_today' => VisitorPass::where('status', 'checked_out')->whereDate('checked_out_at', Carbon::today())->count(),
            'total_passes' => VisitorPass::count(),
        ];

        return view('receptionist.visitor_passes.index', compact('passes', 'patients', 'search', 'status', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'visitor_name' => ['required', 'string', 'max:100'],
            'visitor_phone' => ['required', 'string', 'max:20'],
            'visitor_id_card' => ['nullable', 'string', 'max:20'],
            'relationship' => ['required', 'string', 'max:50'],
            'target_department' => ['required', 'string', 'max:100'],
            'valid_hours' => ['required', 'integer', 'min:1', 'max:4'],
            'infection_screening_passed' => ['required', 'boolean'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);

        // JCI Safety Rule: Không quá 2 người thăm bệnh cùng 1 lúc cho 1 bệnh nhân
        $activeVisitorsCount = VisitorPass::where('patient_profile_id', $patient->id)
            ->where('status', 'active')
            ->where('valid_to', '>=', Carbon::now())
            ->count();

        if ($activeVisitorsCount >= 2) {
            return back()->withInput()->with('error', "Quy chuẩn Kiểm soát nhiễm khuẩn: Bệnh nhân {$patient->full_name} hiện đã có 2 người đang trong phòng thăm. Vui lòng đợi người thăm trước check-out.");
        }

        if (!$validated['infection_screening_passed']) {
            return back()->withInput()->with('error', 'Người thăm không vượt qua bước sàng lọc nhiễm khuẩn (có triệu chứng sốt/ho/hô hấp). Tạm thời không cấp thẻ vào khu điều trị.');
        }

        $validFrom = Carbon::now();
        $validTo = Carbon::now()->addHours($validated['valid_hours']);
        $passCode = VisitorPass::generatePassCode();

        $pass = VisitorPass::create([
            'pass_code' => $passCode,
            'patient_profile_id' => $patient->id,
            'visitor_name' => $validated['visitor_name'],
            'visitor_phone' => $validated['visitor_phone'],
            'visitor_id_card' => $validated['visitor_id_card'] ?? null,
            'relationship' => $validated['relationship'],
            'target_department' => $validated['target_department'],
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'infection_screening_passed' => true,
            'status' => 'active',
            'issued_by_user_id' => Auth::id(),
        ]);

        ClinicalAuditLog::record(
            $patient,
            'visitor_pass_issued',
            "Cấp thẻ thăm bệnh {$pass->pass_code} cho {$pass->visitor_name} ({$pass->relationship}) vào khoa {$pass->target_department} (Hiệu lực: {$validated['valid_hours']} giờ)"
        );

        return back()->with('success', "Đã cấp Thẻ thăm bệnh điện tử thành công! Mã thẻ: {$pass->pass_code}");
    }

    public function checkout(VisitorPass $visitorPass): RedirectResponse
    {
        $visitorPass->update([
            'status' => 'checked_out',
            'checked_out_at' => Carbon::now(),
        ]);

        return back()->with('success', "Khách thăm {$visitorPass->visitor_name} đã check-out ra về thành công!");
    }

    public function print(VisitorPass $visitorPass): View
    {
        $visitorPass->load(['patientProfile', 'issuedBy']);
        return view('receptionist.visitor_passes.print', compact('visitorPass'));
    }
}
