<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrBlockSchedule;
use App\Models\PorterTransportRequest;
use App\Models\DietaryOrder;

class HospitalOperationsController extends Controller
{
    public function index()
    {
        $stats = [
            'surgeries_today' => OrBlockSchedule::whereDate('surgery_date', today())->count(),
            'or_utilization' => OrBlockSchedule::whereDate('surgery_date', today())->where('block_status', 'completed')->count(),
            'transport_pending' => PorterTransportRequest::where('status', 'pending')->count(),
            'dietary_active' => DietaryOrder::where('is_active', true)->count(),
        ];
        return view('reception.hospital_operations.index', compact('stats'));
    }

    public function storeOrSchedule(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'or_room' => 'required|string|max:20',
            'surgery_date' => 'required|date',
            'scheduled_start_time' => 'required|date_format:H:i',
            'scheduled_end_time' => 'required|date_format:H:i|after:scheduled_start_time',
            'surgery_name' => 'required|string|max:200',
            'surgery_category' => 'required|in:elective,urgent,emergency',
            'anesthesia_type' => 'required|in:general,regional,local,sedation',
            'estimated_duration_minutes' => 'nullable|integer|min:15',
            'surgical_team' => 'nullable|string|max:200',
            'equipment_needed' => 'nullable|string|max:500',
            'pre_op_notes' => 'nullable|string|max:2000',
        ]);

        $record = OrBlockSchedule::create(array_merge($validated, ['block_status' => 'scheduled']));
        return response()->json(['success' => true, 'message' => 'Lên lịch phòng mổ thành công', 'data' => $record], 201);
    }

    public function storePorterRequest(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'from_location' => 'required|string|max:100',
            'to_location' => 'required|string|max:100',
            'transport_mode' => 'required|in:wheelchair,stretcher,bed,ambulatory,incubator',
            'priority' => 'required|in:routine,urgent,emergency',
            'requested_by' => 'required|string|max:100',
            'oxygen_required' => 'nullable|boolean',
            'iv_line_present' => 'nullable|boolean',
            'monitoring_required' => 'nullable|boolean',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $record = PorterTransportRequest::create(array_merge($validated, [
            'requested_at' => now(),
            'status' => 'pending',
            'oxygen_required' => (bool)($validated['oxygen_required'] ?? false),
            'iv_line_present' => (bool)($validated['iv_line_present'] ?? false),
            'monitoring_required' => (bool)($validated['monitoring_required'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Yêu cầu vận chuyển thành công', 'data' => $record], 201);
    }

    public function storeDietaryOrder(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'diet_type' => 'required|in:regular,diabetic,low_sodium,low_fat,renal,hepatic,high_protein,soft,liquid,npo,tube_feeding,parenteral',
            'caloric_target' => 'nullable|numeric|min:0',
            'protein_target_g' => 'nullable|numeric|min:0',
            'texture' => 'nullable|in:regular,minced,pureed,liquid',
            'halal' => 'nullable|boolean',
            'vegetarian' => 'nullable|boolean',
            'allergy_notes' => 'nullable|string|max:500',
            'nutritional_status' => 'nullable|in:well_nourished,at_risk,malnourished,severely_malnourished',
            'order_start_date' => 'required|date',
            'dietitian_notes' => 'nullable|string|max:2000',
        ]);

        $record = DietaryOrder::create(array_merge($validated, [
            'is_active' => true,
            'halal' => (bool)($validated['halal'] ?? false),
            'vegetarian' => (bool)($validated['vegetarian'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Đơn dinh dưỡng thành công', 'data' => $record], 201);
    }
}
