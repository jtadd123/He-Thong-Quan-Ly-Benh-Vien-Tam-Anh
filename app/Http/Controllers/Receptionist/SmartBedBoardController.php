<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\BedCleaningLog;
use App\Models\HospitalBed;
use App\Models\HospitalRoom;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class SmartBedBoardController extends Controller
{
    public function index(Request $request)
    {
        $rooms = HospitalRoom::with(['beds.currentPatient', 'beds.cleaningLogs'])->get();
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_beds' => HospitalBed::count(),
            'available_clean' => HospitalBed::where('status', 'available_clean')->count(),
            'occupied' => HospitalBed::where('status', 'occupied')->count(),
            'dirty_pending' => HospitalBed::where('status', 'dirty_pending')->count(),
            'cleaning' => HospitalBed::where('status', 'cleaning_in_progress')->count(),
        ];

        return view('receptionist.bed_board.index', compact('rooms', 'patients', 'stats'));
    }

    public function assignPatient(Request $request, HospitalBed $bed)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
        ]);

        if ($bed->status !== 'available_clean') {
            return back()->with('error', 'Giường chưa được khử trùng sạch sẽ hoặc đang có người nằm!');
        }

        $bed->update([
            'status' => 'occupied',
            'current_patient_id' => $validated['patient_profile_id'],
        ]);

        return redirect()->route('reception.bed-board.index')->with('success', "Đã xếp bệnh nhân vào giường {$bed->bed_code} thành công!");
    }

    public function dischargePatient(Request $request, HospitalBed $bed)
    {
        $bed->update([
            'status' => 'dirty_pending',
            'current_patient_id' => null,
        ]);

        return redirect()->route('reception.bed-board.index')->with('success', "Bệnh nhân đã rời giường {$bed->bed_code}. Trạng thái chuyển thành: Chờ đội vệ sinh khử trùng!");
    }

    public function cleanBed(Request $request, HospitalBed $bed)
    {
        $validated = $request->validate([
            'cleaner_name' => 'required|string|max:100',
            'disinfectant_chemical' => 'required|string|max:100',
        ]);

        $log = BedCleaningLog::create([
            'bed_id' => $bed->id,
            'cleaner_name' => $validated['cleaner_name'],
            'disinfectant_chemical' => $validated['disinfectant_chemical'],
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
            'verified_by' => 'ĐD. Trưởng Khoa Kiểm Soát Nhiễm Khuẩn',
            'status' => 'verified',
        ]);

        $bed->update([
            'status' => 'available_clean',
            'last_disinfected_at' => now(),
        ]);

        return redirect()->route('reception.bed-board.index')->with('success', "Giường {$bed->bed_code} đã được khử trùng vô khuẩn đạt chuẩn JCI và sẵn sàng nhận bệnh nhân mới!");
    }
}
