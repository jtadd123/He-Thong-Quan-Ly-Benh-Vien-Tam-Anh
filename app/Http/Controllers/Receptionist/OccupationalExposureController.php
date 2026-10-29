<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\OccupationalExposure;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class OccupationalExposureController extends Controller
{
    public function index(Request $request)
    {
        $query = OccupationalExposure::with('sourcePatient')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $exposures = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_incidents' => OccupationalExposure::count(),
            'monitoring' => OccupationalExposure::where('status', 'monitoring')->count(),
            'pep_prescribed' => OccupationalExposure::where('pep_started', true)->count(),
        ];

        return view('receptionist.occupational_exposure.index', compact('exposures', 'patients', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:100',
            'employee_role' => 'required|string|in:doctor,nurse,lab_tech,cleaning_staff',
            'department' => 'required|string|max:100',
            'exposure_type' => 'required|string|in:needlestick,sharp_cut,splash_mucosa',
            'source_patient_id' => 'nullable|exists:patient_profiles,id',
            'source_status' => 'required|string|in:negative,hiv_pos,hbv_pos,hcv_pos,unknown',
            'pep_started' => 'nullable|boolean',
            'pep_start_hours_elapsed' => 'nullable|integer|min:0|max:168',
            'incident_description' => 'nullable|string',
        ]);

        OccupationalExposure::create([
            'incident_code' => OccupationalExposure::generateCode(),
            'employee_name' => $validated['employee_name'],
            'employee_role' => $validated['employee_role'],
            'department' => $validated['department'],
            'exposure_type' => $validated['exposure_type'],
            'source_patient_id' => $validated['source_patient_id'] ?? null,
            'source_status' => $validated['source_status'],
            'pep_started' => (bool) ($validated['pep_started'] ?? false),
            'pep_start_hours_elapsed' => $validated['pep_start_hours_elapsed'] ?? null,
            'followup_timeline' => [
                'month_1' => ['scheduled_date' => now()->addMonth()->toDateString(), 'status' => 'pending'],
                'month_3' => ['scheduled_date' => now()->addMonths(3)->toDateString(), 'status' => 'pending'],
                'month_6' => ['scheduled_date' => now()->addMonths(6)->toDateString(), 'status' => 'pending'],
            ],
            'status' => 'monitoring',
            'incident_description' => $validated['incident_description'] ?? null,
        ]);

        return redirect()->route('reception.occupational-exposure.index')->with('success', 'Đã lập biên bản theo dõi tai nạn phơi nhiễm nghề nghiệp thành công theo chuẩn JCI GL.4!');
    }
}
