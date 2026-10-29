<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\HospitalWayfinding;
use Illuminate\Http\Request;

class HospitalWayfindingController extends Controller
{
    public function index(Request $request)
    {
        $routes = HospitalWayfinding::latest()->paginate(15);

        return view('receptionist.wayfinding.index', compact('routes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_location' => 'required|string|max:100',
            'destination_department' => 'required|string|max:100',
            'floor_level' => 'required|integer',
            'estimated_walk_minutes' => 'required|integer|min:1|max:30',
            'step_directions' => 'required|array|min:1',
            'accessibility_friendly' => 'required|in:wheelchair_accessible,stairs_only',
        ]);

        HospitalWayfinding::create($validated);

        return redirect()->route('reception.wayfinding.index')->with('success', 'Đã tạo lộ trình bản đồ điều hướng trong nhà thành công!');
    }
}
