@extends('layouts.admin')

@section('title', 'Trung Tâm Tiệt Khuẩn CSSD & Quản Lý Khay Dụng Cụ Mổ')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>✂️</span> TRUNG TÂM TIỆT KHUẨN DỤNG CỤ MỔ (CSSD) & TRUY VẾT KHAY PHẪU THUẬT
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Tiêu chuẩn kiểm soát nhiễm khuẩn vô trùng JCI PCI - Theo dõi chu trình tiệt khuẩn Autoclave/Plasma và chỉ thị sinh học.
            </p>
        </div>
        <button onclick="document.getElementById('modal-sterilization-cycle').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-teal-600 text-white font-bold text-sm shadow hover:bg-teal-500 transition flex items-center gap-2">
            <span>⚙️</span> Chạy Chu Trình Tiệt Khuẩn (Mẻ Mới)
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Khay Phẫu Thuật</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_trays'] }} khay</div>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Khay Vô Trùng Sẵn Sàng</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['sterilized_ready'] }} khay</div>
        </div>
        <div class="p-4 rounded-2xl bg-sky-500/10 border border-sky-500/30 shadow-sm">
            <span class="text-xs font-bold text-sky-600 uppercase">Đang Sử Dụng Trong Phòng Mổ</span>
            <div class="text-2xl font-black text-sky-600 mt-1">{{ $stats['in_surgery'] }} khay</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Nhiễm Khuẩn - Chờ Xử Lý</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['contaminated'] }} khay</div>
        </div>
    </div>

    <!-- Trays Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH MỤC KHAY DỤNG CỤ PHẪU THUẬT VÀ HẠN DÙNG VÔ TRÙNG</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Mã QR Khay</th>
                        <th class="p-3">Tên bộ khay mổ</th>
                        <th class="p-3">Khoa sử dụng</th>
                        <th class="p-3 text-center">Số lượng dụng cụ</th>
                        <th class="p-3 text-center">Trạng thái vô khuẩn</th>
                        <th class="p-3">Mẻ tiệt khuẩn gần nhất</th>
                        <th class="p-3 text-center">Hạn dùng vô trùng (30 ngày)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($trays as $tray)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-mono font-bold text-teal-600">{{ $tray->tray_code }}</td>
                        <td class="p-3 font-bold text-slate-800 dark:text-white">{{ $tray->tray_name }}</td>
                        <td class="p-3 text-slate-500">{{ $tray->department }}</td>
                        <td class="p-3 text-center font-bold">{{ $tray->total_instruments_count }} món</td>
                        <td class="p-3 text-center">
                            @if($tray->status === 'sterilized')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">✓ ĐẠT VÔ TRÙNG</span>
                            @elseif($tray->status === 'in_or')
                                <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-800 font-bold text-[10px] uppercase">Đang mổ</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] uppercase">{{ $tray->status }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($tray->lastCycle)
                                <span class="font-mono text-slate-600 dark:text-slate-300">{{ $tray->lastCycle->cycle_code }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $tray->lastCycle->method }}</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Chưa tiệt khuẩn</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-mono font-bold {{ $tray->expiry_date && $tray->expiry_date->isPast() ? 'text-red-600' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $tray->expiry_date ? $tray->expiry_date->format('d/m/Y') : '--' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Chưa có khay dụng cụ phẫu thuật nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $trays->links() }}
        </div>
    </div>
</div>

<!-- Modal Chạy Mẻ Tiệt Khuẩn -->
<div id="modal-sterilization-cycle" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Kích Hoạt Chu Trình Tiệt Khuẩn Dụng Cụ Mổ CSSD</h3>
            <button onclick="document.getElementById('modal-sterilization-cycle').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.cssd.run-cycle') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Thiết Bị Tiệt Khuẩn *</label>
                <input type="text" name="machine_name" value="Nồi Hấp Áp Suất Autoclave #01 (Getinge)" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="font-bold block mb-1">Phương Pháp *</label>
                    <select name="method" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="steam_autoclave_134c">Hơi nước 134°C</option>
                        <option value="plasma_peroxide">Plasma H2O2</option>
                        <option value="eo_gas">Khí EO</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold block mb-1">Nhiệt Độ (°C) *</label>
                    <input type="number" name="temp_celsius" value="134" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
                <div>
                    <label class="font-bold block mb-1">Áp Suất (bar) *</label>
                    <input type="number" step="0.1" name="pressure_bar" value="2.1" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
            </div>
            <div class="p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 space-y-2">
                <span class="font-bold text-slate-700 dark:text-slate-200 block">Kiểm Tra Chỉ Thị Vô Trùng (Bắt Buộc JCI PCI)</span>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="chemical_indicator_pass" value="1" checked>
                    <span>Chỉ thị màu hóa học chuyển màu hoàn toàn (Đạt Class 5/6)</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="biological_indicator_pass" value="1" checked>
                    <span>Chỉ thị sinh học ốc cấy Geobacillus không mọc (Âm tính)</span>
                </label>
            </div>
            <div>
                <label class="font-bold block mb-1">Chọn Khay Tiệt Khuẩn Trong Mẻ Này *</label>
                <div class="max-h-32 overflow-y-auto border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 space-y-1">
                    @foreach($trays as $t)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="tray_ids[]" value="{{ $t->id }}" checked>
                            <span>{{ $t->tray_code }} - {{ $t->tray_name }} ({{ $t->total_instruments_count }} món)</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Kỹ Thuật Viên Vận Hành *</label>
                <input type="text" name="operator_name" value="KTV. Đỗ Văn Nam (CSSD)" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-sterilization-cycle').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-500">Chạy Mẻ & Cấp Chứng Nhận Vô Trùng</button>
            </div>
        </form>
    </div>
</div>
@endsection
