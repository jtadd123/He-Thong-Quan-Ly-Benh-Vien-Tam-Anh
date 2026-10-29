<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\EmergencyCustody;
use App\Models\LostFoundItem;
use App\Models\PatientProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmergencyCustodyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $tab = $request->query('tab', 'custody');

        $custodies = EmergencyCustody::with(['patientProfile', 'nurse', 'security', 'returnedBy'])
            ->latest('id')
            ->paginate(15, ['*'], 'custody_page');

        $lostFounds = LostFoundItem::with('receivedBy')
            ->latest('id')
            ->paginate(15, ['*'], 'lost_page');

        $patients = PatientProfile::latest('id')->limit(50)->get();
        $staffUsers = User::whereIn('role', [User::ROLE_RECEPTIONIST, User::ROLE_ADMIN, User::ROLE_DOCTOR])->get();

        $stats = [
            'sealed_custody' => EmergencyCustody::where('status', 'sealed')->count(),
            'returned_custody' => EmergencyCustody::where('status', 'returned')->count(),
            'unclaimed_items' => LostFoundItem::where('status', 'unclaimed')->count(),
            'claimed_items' => LostFoundItem::where('status', 'claimed')->count(),
        ];

        return view('receptionist.emergency_custody.index', compact('custodies', 'lostFounds', 'patients', 'staffUsers', 'stats', 'tab', 'search'));
    }

    public function storeCustody(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'bag_seal_number' => ['required', 'string', 'max:50'],
            'item_description' => ['required', 'string'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'security_user_id' => ['required', 'exists:users,id'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $custodyCode = EmergencyCustody::generateCustodyCode();

        $custody = EmergencyCustody::create([
            'custody_code' => $custodyCode,
            'patient_profile_id' => $patient->id,
            'bag_seal_number' => $validated['bag_seal_number'],
            'item_description' => $validated['item_description'],
            'cash_amount' => $validated['cash_amount'] ?? 0,
            'nurse_user_id' => Auth::id(),
            'security_user_id' => $validated['security_user_id'],
            'status' => 'sealed',
        ]);

        ClinicalAuditLog::record(
            $patient,
            'emergency_custody_seal',
            "Niêm phong tài sản cấp cứu: Mã {$custody->custody_code}, Túi số {$custody->bag_seal_number}. Gồm: {$custody->item_description}"
        );

        return back()->with('success', "Đã niêm phong tài sản cấp cứu an toàn! Mã biên bản: {$custody->custody_code}");
    }

    public function returnCustody(Request $request, EmergencyCustody $custody): RedirectResponse
    {
        $validated = $request->validate([
            'return_recipient_name' => ['required', 'string', 'max:100'],
            'return_recipient_id_card' => ['required', 'string', 'max:20'],
            'return_relationship' => ['required', 'string', 'max:50'],
        ]);

        $custody->update([
            'status' => 'returned',
            'return_recipient_name' => $validated['return_recipient_name'],
            'return_recipient_id_card' => $validated['return_recipient_id_card'],
            'return_relationship' => $validated['return_relationship'],
            'returned_at' => Carbon::now(),
            'returned_by_user_id' => Auth::id(),
        ]);

        ClinicalAuditLog::record(
            $custody->patientProfile,
            'emergency_custody_return',
            "Bàn giao trả tài sản cấp cứu {$custody->custody_code} cho {$custody->return_recipient_name} (Quan hệ: {$custody->return_relationship})"
        );

        return back()->with('success', "Đã hoàn tất bàn giao trả tài sản cho người bệnh/thân nhân!");
    }

    public function storeLostFound(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:150'],
            'found_location' => ['required', 'string', 'max:150'],
            'found_at' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $itemCode = LostFoundItem::generateItemCode();

        $item = LostFoundItem::create(array_merge($validated, [
            'item_code' => $itemCode,
            'status' => 'unclaimed',
            'received_by_user_id' => Auth::id(),
        ]));

        return back()->with('success', "Đã ghi nhận đồ thất lạc nhặt được tại sảnh: {$item->item_name} (Mã: {$item->item_code})");
    }

    public function claimLostFound(Request $request, LostFoundItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'claimant_name' => ['required', 'string', 'max:100'],
            'claimant_phone' => ['required', 'string', 'max:20'],
            'claimant_id_card' => ['required', 'string', 'max:20'],
        ]);

        $item->update([
            'status' => 'claimed',
            'claimant_name' => $validated['claimant_name'],
            'claimant_phone' => $validated['claimant_phone'],
            'claimant_id_card' => $validated['claimant_id_card'],
            'claimed_at' => Carbon::now(),
        ]);

        return back()->with('success', "Đã bàn giao trả đồ thất lạc cho khách hàng: {$item->claimant_name}");
    }

    public function printCustody(EmergencyCustody $custody): View
    {
        $custody->load(['patientProfile', 'nurse', 'security', 'returnedBy']);
        return view('receptionist.emergency_custody.print_custody', compact('custody'));
    }
}
