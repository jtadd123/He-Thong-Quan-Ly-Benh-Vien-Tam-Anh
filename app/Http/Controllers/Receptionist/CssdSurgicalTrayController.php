<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\CssdInstrumentTray;
use App\Models\SterilizationCycle;
use Illuminate\Http\Request;

class CssdSurgicalTrayController extends Controller
{
    public function index(Request $request)
    {
        $trays = CssdInstrumentTray::with('lastCycle')->latest()->paginate(15);
        $cycles = SterilizationCycle::withCount('instrumentTrays')->latest()->paginate(10);

        $stats = [
            'total_trays' => CssdInstrumentTray::count(),
            'sterilized_ready' => CssdInstrumentTray::where('status', 'sterilized')->count(),
            'in_surgery' => CssdInstrumentTray::where('status', 'in_or')->count(),
            'contaminated' => CssdInstrumentTray::where('status', 'contaminated')->count(),
        ];

        return view('receptionist.cssd.index', compact('trays', 'cycles', 'stats'));
    }

    public function runCycle(Request $request)
    {
        $validated = $request->validate([
            'machine_name' => 'required|string|max:100',
            'method' => 'required|string|in:steam_autoclave_134c,plasma_peroxide,eo_gas',
            'temp_celsius' => 'required|integer',
            'pressure_bar' => 'required|numeric',
            'chemical_indicator_pass' => 'required|boolean',
            'biological_indicator_pass' => 'required|boolean',
            'operator_name' => 'required|string|max:100',
            'tray_ids' => 'required|array|min:1',
            'tray_ids.*' => 'exists:cssd_instrument_trays,id',
        ]);

        $cyclePassed = ($validated['chemical_indicator_pass'] && $validated['biological_indicator_pass']);

        $cycle = SterilizationCycle::create([
            'cycle_code' => SterilizationCycle::generateCode(),
            'machine_name' => $validated['machine_name'],
            'method' => $validated['method'],
            'temp_celsius' => $validated['temp_celsius'],
            'pressure_bar' => $validated['pressure_bar'],
            'chemical_indicator_pass' => (bool) $validated['chemical_indicator_pass'],
            'biological_indicator_pass' => (bool) $validated['biological_indicator_pass'],
            'operator_name' => $validated['operator_name'],
            'status' => $cyclePassed ? 'passed' : 'failed_rejected',
            'cycle_time' => now(),
        ]);

        if ($cyclePassed) {
            CssdInstrumentTray::whereIn('id', $validated['tray_ids'])->update([
                'status' => 'sterilized',
                'last_cycle_id' => $cycle->id,
                'expiry_date' => now()->addDays(30)->toDateString(),
            ]);
        }

        return redirect()->route('reception.cssd.index')->with(
            $cyclePassed ? 'success' : 'error',
            $cyclePassed ? "Chu trình tiệt khuẩn CSSD {$cycle->cycle_code} hoàn tất thành công! Các khay mổ đã sẵn sàng." : "CẢNH BÁO: Chỉ thị vô trùng không đạt! Toàn bộ mẻ tiệt khuẩn đã bị từ chối."
        );
    }
}
