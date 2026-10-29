<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ColdChainStorage;
use App\Models\TemperatureLog;
use Illuminate\Http\Request;

class ColdChainController extends Controller
{
    public function index(Request $request)
    {
        $storages = ColdChainStorage::withCount('temperatureLogs')->get();
        $recentLogs = TemperatureLog::with('storage')->latest('recorded_at')->paginate(20);

        $stats = [
            'total_storages' => ColdChainStorage::count(),
            'locked_storages' => ColdChainStorage::where('is_locked', true)->count(),
            'warnings' => ColdChainStorage::whereIn('status', ['warning_high', 'warning_low'])->count(),
        ];

        return view('receptionist.cold_chain.index', compact('storages', 'recentLogs', 'stats'));
    }

    public function recordTelemetry(Request $request)
    {
        $validated = $request->validate([
            'storage_id' => 'required|exists:cold_chain_storages,id',
            'temperature' => 'required|numeric|between:-90,50',
            'humidity' => 'nullable|numeric|between:0,100',
        ]);

        $storage = ColdChainStorage::findOrFail($validated['storage_id']);
        $temp = (float) $validated['temperature'];

        $isOutOfRange = ($temp < $storage->temp_min || $temp > $storage->temp_max);
        $statusTag = 'normal';

        if ($temp > $storage->temp_max) {
            $statusTag = 'warning_high';
        } elseif ($temp < $storage->temp_min) {
            $statusTag = 'warning_low';
        }

        $isLocked = $isOutOfRange;

        $storage->update([
            'current_temp' => $temp,
            'status' => $statusTag,
            'is_locked' => $isLocked,
            'last_reading_at' => now(),
        ]);

        TemperatureLog::create([
            'storage_id' => $storage->id,
            'temperature' => $temp,
            'humidity' => $validated['humidity'] ?? 55.0,
            'alert_dispatched' => $isOutOfRange,
            'status_tag' => $statusTag,
            'recorded_at' => now(),
        ]);

        $message = $isLocked
            ? "CẢNH BÁO: Nhiệt độ kho lạnh ({$temp}°C) vượt ngưỡng an toàn! Hệ thống đã tự động khóa kê đơn các lô thuốc liên quan!"
            : "Đã cập nhật dữ liệu nhiệt độ kho lạnh GSP thành công ({$temp}°C).";

        return redirect()->route('reception.cold-chain.index')->with($isLocked ? 'warning' : 'success', $message);
    }
}
