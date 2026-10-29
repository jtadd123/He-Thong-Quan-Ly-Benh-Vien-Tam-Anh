<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\OrganDonationAndLivingWill;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class LivingWillOrganDonationController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganDonationAndLivingWill::with('patientProfile')->latest();

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        $records = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_registered' => OrganDonationAndLivingWill::count(),
            'organ_donors' => OrganDonationAndLivingWill::where('record_type', 'organ_donation')->count(),
            'living_wills' => OrganDonationAndLivingWill::where('record_type', 'living_will_dnr')->count(),
        ];

        return view('receptionist.living_will.index', compact('records', 'patients', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'record_type' => 'required|in:organ_donation,living_will_dnr',
            'organs_pledged' => 'nullable|array',
            'dnr_directives' => 'nullable|array',
            'representative_name' => 'required|string|max:100',
            'representative_phone' => 'required|string|max:20',
            'representative_relationship' => 'required|string|max:50',
            'legal_consent_signed' => 'required|boolean',
        ]);

        $code = OrganDonationAndLivingWill::generateCode($validated['record_type']);

        OrganDonationAndLivingWill::create([
            'patient_profile_id' => $validated['patient_profile_id'],
            'record_type' => $validated['record_type'],
            'registration_number' => $code,
            'organs_pledged' => $validated['organs_pledged'] ?? [],
            'dnr_directives' => $validated['dnr_directives'] ?? [],
            'representative_name' => $validated['representative_name'],
            'representative_phone' => $validated['representative_phone'],
            'representative_relationship' => $validated['representative_relationship'],
            'legal_consent_signed' => (bool) $validated['legal_consent_signed'],
            'status' => 'active_registered',
        ]);

        $label = ($validated['record_type'] === 'organ_donation') ? 'Đăng ký hiến mô, tạng cứu người' : 'Di chúc y tế / Chỉ thị không hồi sức DNR';

        return redirect()->route('reception.living-will.index')->with('success', "Đã ghi nhận {$label} thành công (Mã: {$code})!");
    }
}
