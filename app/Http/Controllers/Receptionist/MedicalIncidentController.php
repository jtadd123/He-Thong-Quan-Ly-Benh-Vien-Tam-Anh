<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\MedicalIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MedicalIncidentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $severity = $request->query('severity');

        $query = MedicalIncident::with(['reporter', 'reviewedBy'])
            ->latest('id');

        if ($severity) {
            $query->where('severity_level', $severity);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_code', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%")
                  ->orWhere('incident_category', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $incidents = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => MedicalIncident::count(),
            'nc0_near_miss' => MedicalIncident::where('severity_level', 'NC0')->count(),
            'investigating' => MedicalIncident::whereIn('status', ['reported', 'investigating'])->count(),
            'resolved' => MedicalIncident::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('receptionist.medical_incidents.index', compact('incidents', 'search', 'severity', 'stats'));
    }

    public function create(): View
    {
        return view('receptionist.medical_incidents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reporter_type' => ['required', 'in:named,anonymous'],
            'location' => ['required', 'string', 'max:150'],
            'occurred_at' => ['required', 'date'],
            'severity_level' => ['required', 'in:NC0,NC1,NC2,NC3'],
            'incident_category' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'immediate_action' => ['nullable', 'string'],
            'root_cause_analysis' => ['nullable', 'string'],
        ]);

        $incidentCode = MedicalIncident::generateIncidentCode();

        $incident = MedicalIncident::create(array_merge($validated, [
            'incident_code' => $incidentCode,
            'reporter_user_id' => ($validated['reporter_type'] === 'named') ? Auth::id() : null,
            'status' => 'reported',
        ]));

        return redirect()->route('reception.medical-incidents.index')
            ->with('success', "Đã ghi nhận báo cáo sự cố y khoa: {$incident->incident_code}. Ban An Toàn Người Bệnh sẽ thẩm định phòng ngừa!");
    }

    public function show(MedicalIncident $medicalIncident): View
    {
        $medicalIncident->load(['reporter', 'reviewedBy']);
        return view('receptionist.medical_incidents.show', compact('medicalIncident'));
    }

    public function updateRca(Request $request, MedicalIncident $medicalIncident): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:investigating,resolved,closed'],
            'root_cause_analysis' => ['required', 'string'],
        ]);

        $medicalIncident->update([
            'status' => $validated['status'],
            'root_cause_analysis' => $validated['root_cause_analysis'],
            'reviewed_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', "Đã cập nhật phân tích nguyên nhân gốc rễ RCA và kế hoạch hành động khắc phục!");
    }
}
