<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\NarcoticDispenseLog;
use App\Models\NarcoticEmergencyBox;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NarcoticBoxController extends Controller
{
    public function index(Request $request)
    {
        $boxes = NarcoticEmergencyBox::withCount('dispenseLogs')->get();
        $logs = NarcoticDispenseLog::with(['box', 'patientProfile'])->latest()->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_boxes' => NarcoticEmergencyBox::count(),
            'total_stock' => NarcoticEmergencyBox::sum('quantity_stock'),
            'dispensed_today' => NarcoticDispenseLog::whereDate('created_at', today())->sum('quantity_dispensed'),
            'pending_disposal' => NarcoticDispenseLog::where('disposal_verified', false)->count(),
        ];

        return view('receptionist.narcotics.index', compact('boxes', 'logs', 'patients', 'stats'));
    }

    public function dispense(Request $request)
    {
        $validated = $request->validate([
            'box_id' => 'required|exists:narcotic_emergency_boxes,id',
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'doctor_name' => 'required|string|max:100',
            'nurse_name' => 'required|string|max:100',
            'witness_name' => 'required|string|max:100',
            'quantity_dispensed' => 'required|integer|min:1|max:20',
            'indication' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $box = NarcoticEmergencyBox::lockForUpdate()->findOrFail($validated['box_id']);

            if ($box->quantity_stock < $validated['quantity_dispensed']) {
                abort(400, 'Số lượng thuốc trong tủ trực không đủ để cấp phát!');
            }

            $box->decrement('quantity_stock', $validated['quantity_dispensed']);

            NarcoticDispenseLog::create([
                'box_id' => $box->id,
                'patient_profile_id' => $validated['patient_profile_id'],
                'doctor_name' => $validated['doctor_name'],
                'nurse_name' => $validated['nurse_name'],
                'witness_name' => $validated['witness_name'],
                'quantity_dispensed' => $validated['quantity_dispensed'],
                'indication' => $validated['indication'],
                'empty_ampoules_returned' => 0,
                'disposal_verified' => false,
            ]);
        });

        return redirect()->route('reception.narcotics.index')->with('success', 'Đã xuất thuốc gây nghiện/hướng thần thành công có chữ ký đối chiếu kép!');
    }

    public function verifyDisposal(Request $request, NarcoticDispenseLog $log)
    {
        $validated = $request->validate([
            'empty_ampoules_returned' => 'required|integer|min:1',
        ]);

        $log->update([
            'empty_ampoules_returned' => $validated['empty_ampoules_returned'],
            'disposal_verified' => true,
        ]);

        return redirect()->route('reception.narcotics.index')->with('success', 'Đã xác nhận tiêu hủy vỏ ống thuốc độc thành công theo Thông tư 20/2017/TT-BYT!');
    }
}
