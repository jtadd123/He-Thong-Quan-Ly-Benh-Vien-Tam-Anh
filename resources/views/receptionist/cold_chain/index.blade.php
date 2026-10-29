@extends('layouts.admin')

@section('title', 'Giám Sát Chuỗi Cung Ứng Lạnh Vắc-Xin & Sinh Phẩm IoT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>❄️</span> GIÁM SÁT CHUỖI CUNG ỨNG LẠNH VẮC-XIN & SINH PHẨM (GSP IOT)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Theo dõi nhiệt độ liên tục 24/7 (2°C - 8°C) bảo quản Vắc-xin, Máu, Insulin & Cơ chế khóa an toàn tự động.
            </p>
        </div>
        <button onclick="document.getElementById('modal-telemetry').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-sky-600 text-white font-bold text-sm shadow hover:bg-sky-500 transition flex items-center gap-2">
            <span>📡</span> Ghi Nhận Dữ Liệu Cảm Biến IoT
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Kho Lạnh Đang Hoạt Động</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_storages'] }} kho</div>
        </div>
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 shadow-sm">
            <span class="text-xs font-bold text-red-600 uppercase">Kho Bị Khóa An Toàn (Kê Đơn Bị Chặn)</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['locked_storages'] }} kho</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Cảnh Báo Vượt Ngưỡng</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['warnings'] }} lần</div>
        </div>
    </div>

    <!-- Storages Real-time Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($storages as $st)
        <div class="p-5 rounded-2xl border bg-white dark:bg-slate-800 shadow-sm relative overflow-hidden space-y-3">
            @if($st->is_locked)
            <div class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-black px-3 py-1 rounded-bl-xl uppercase tracking-wider animate-pulse">
                🔒 ĐÃ KHÓA AN TOÀN
            </div>
            @endif
            <div>
                <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $st->storage_code }}</span>
                <h4 class="font-bold text-base text-slate-800 dark:text-white">{{ $st->storage_name }}</h4>
                <span class="text-xs text-slate-500">Chuẩn bảo quản: {{ $st->temp_min }}°C - {{ $st->temp_max }}°C</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border flex justify-between items-center">
                <div>
                    <span class="text-xs text-slate-400 block">Nhiệt độ hiện tại</span>
                    <span class="text-3xl font-mono font-black {{ $st->is_locked ? 'text-red-600' : 'text-sky-600' }}">
                        {{ $st->current_temp }}°C
                    </span>
                </div>
                <div class="text-right text-xs">
                    <span class="px-2.5 py-1 rounded-full font-bold uppercase {{ $st->status === 'normal' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $st->status }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-1">Cập nhật: {{ $st->last_reading_at ? $st->last_reading_at->format('H:i:s') : 'Chưa có' }}</span>
                </div>
            </div>
            @if($st->is_locked)
            <div class="p-2.5 bg-red-50 dark:bg-red-950/40 rounded-xl text-xs text-red-700 dark:text-red-300 border border-red-200">
                ⚠️ <strong>CẢNH BÁO BỘ Y TẾ:</strong> Nhiệt độ vi phạm tiêu chuẩn bảo quản! Toàn bộ vắc-xin/sinh phẩm trong kho này đã bị tạm dừng chỉ định để bảo đảm an toàn tính mạng người bệnh.
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-3 p-8 text-center text-slate-400 bg-white rounded-2xl border">
            Chưa có kho lạnh nào trong hệ thống.
        </div>
        @endforelse
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">NHẬT KÝ ĐO NHIỆT ĐỘ LIÊN TỤC (TELEMETRY STREAM)</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Thời gian ghi nhận</th>
                        <th class="p-3">Kho bảo quản</th>
                        <th class="p-3 text-center">Nhiệt độ (°C)</th>
                        <th class="p-3 text-center">Độ ẩm (%)</th>
                        <th class="p-3 text-center">Trạng thái cảm biến</th>
                        <th class="p-3 text-center">Cảnh báo khẩn cấp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 text-slate-500 font-mono">{{ $log->recorded_at->format('H:i:s d/m/Y') }}</td>
                        <td class="p-3 font-semibold">{{ $log->storage->storage_name }}</td>
                        <td class="p-3 text-center font-mono font-bold text-sm {{ $log->status_tag === 'normal' ? 'text-sky-600' : 'text-red-600' }}">
                            {{ $log->temperature }}°C
                        </td>
                        <td class="p-3 text-center font-mono">{{ $log->humidity ?? 55 }}%</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $log->status_tag === 'normal' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $log->status_tag }}
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            @if($log->alert_dispatched)
                                <span class="px-2 py-0.5 rounded bg-red-600 text-white font-bold text-[10px] animate-pulse">🚨 ĐÃ BÁO ĐỘNG</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Bình thường</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">Chưa có nhật ký đo nhiệt độ nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $recentLogs->links() }}
        </div>
    </div>
</div>

<!-- Modal Telemetry Sensor -->
<div id="modal-telemetry" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Ghi Nhận Dữ Liệu Cảm Biến Kho Lạnh IoT</h3>
            <button onclick="document.getElementById('modal-telemetry').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.cold-chain.telemetry') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Tủ Lạnh / Kho GSP *</label>
                <select name="storage_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($storages as $st)
                        <option value="{{ $st->id }}">{{ $st->storage_name }} ({{ $st->temp_min }}°C - {{ $st->temp_max }}°C)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-bold block mb-1">Nhiệt Độ Đo Được (°C) *</label>
                <input type="number" step="0.1" name="temperature" value="4.5" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-mono font-bold text-lg">
                <span class="text-[10px] text-slate-400">Thử nghiệm nhiệt độ > 8.0°C hoặc < 2.0°C để kích hoạt khóa kê đơn tự động</span>
            </div>
            <div>
                <label class="font-bold block mb-1">Độ Ẩm (%)</label>
                <input type="number" step="0.1" name="humidity" value="50.0" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-telemetry').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-500">Cập Nhật Telemetry</button>
            </div>
        </form>
    </div>
</div>
@endsection
