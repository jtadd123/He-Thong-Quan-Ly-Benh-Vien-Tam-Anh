<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Models\PatientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FamilyMemberController extends Controller
{
    /**
     * Display family members registered by the authenticated patient.
     */
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user->patientProfile ?: PatientProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'patient_code' => PatientProfile::generatePatientCode(),
                'full_name' => $user->name,
                'phone' => '0900000000',
                'gender' => 'other',
                'dob' => '1990-01-01',
            ]
        );

        $members = FamilyMember::where('user_id', $user->id)
            ->with('patientProfile')
            ->latest()
            ->get();

        return view('patient.family.index', [
            'members' => $members,
            'profile' => $profile,
        ]);
    }

    /**
     * Store a new family member dependent.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'id_card_number' => ['nullable', 'string', 'max:20'],
            'health_insurance_code' => ['nullable', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên người thân.',
            'relationship.required' => 'Vui lòng chọn mối quan hệ.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.before' => 'Ngày sinh phải ở trong quá khứ.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số bắt đầu bằng số 0.',
        ]);

        $guardianPhone = $user->patientProfile?->phone ?? '0900000000';

        $dependentProfile = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => $validated['full_name'],
            'dob' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'phone' => !empty($validated['phone']) ? $validated['phone'] : $guardianPhone,
            'identity_card' => $validated['id_card_number'] ?? null,
            'insurance_code' => $validated['health_insurance_code'] ?? null,
            'medical_history' => $validated['medical_notes'] ?? null,
        ]);

        FamilyMember::create([
            'user_id' => $user->id,
            'patient_profile_id' => $dependentProfile->id,
            'relationship' => $validated['relationship'],
            'notes' => $validated['medical_notes'] ?? null,
        ]);

        return back()->with('success', "Đã thêm hồ sơ người thân \"{$validated['full_name']}\" vào danh sách gia đình thành công!");
    }

    /**
     * Delete a family member.
     */
    public function destroy(FamilyMember $familyMember): RedirectResponse
    {
        $user = Auth::user();

        if ($familyMember->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa hồ sơ người thân này.');
        }

        $name = $familyMember->full_name;
        $familyMember->delete();

        return back()->with('success', "Đã xóa hồ sơ người thân \"{$name}\" thành công.");
    }
}
