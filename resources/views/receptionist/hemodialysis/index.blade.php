@extends('layouts.admin')

@section('title', 'Trung Tâm Lọc Máu Thận Nhân Tạo Chu Kỳ')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🩸</span> TRUNG TÂM LỌC MÁU THẬN NHÂN TẠO CHU KỲ (HEMODIALYSIS)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Theo dõi ca chạy thận nhân tạo, tự động tính thể tích siêu lọc (UF Target) và quản lý vòng đời quả lọc Dialyzer.
            </p>
        </div>
        <button onclick="document.getElementById('modal-new-hd').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm shadow hover:bg-red-500 transition flex items-center gap-2">
            <span>➕</span> Kết Nối Ca Lọc Máu Mới
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Ca Lọc Máu</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_sessions'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 shadow-sm">
            <span class="text-xs font-bold text-red-600 uppercase">Máy Đang Chạy Thận Trực Tiếp</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['ongoing_now'] }} máy</div>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Ca Hoàn Tất An Toàn</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['completed'] }} ca</div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH BỆNH NHÂN LỌC MÁU CHU KỲ</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Ngày & Ca</th>
                        <th class="p-3">Máy lọc</th>
                        <th class="p-3">Bệnh nhân</th>
                        <th class="p-3 text-center">Cân nặng khô / Trước / Sau</th>
                        <th class="p-3 text-center">Mục tiêu rút dịch (UF Target)</th>
                        <th class="p-3 text-center">Lần tái dùng màng lọc (1-6)</th>
                        <th class="p-3 text-center">Trạng thái ca</th>
                        <th class="p-3 text-center">Thao tác kết thúc</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($sessions as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3">
                            <span class="font-bold block text-slate-800 dark:text-white">{{ $item->session_date->format('d/m/Y') }}</span>
                            <span class="text-[10px] uppercase text-slate-400 font-semibold">Ca: {{ $item->shift }}</span>
                        </td>
                        <td class="p-3 font-mono font-bold text-sky-600">{{ $item->machine_number }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $item->patientProfile->full_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $item->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3 text-center font-mono">
                            <span class="text-slate-400">{{ $item->dry_weight_kg }}kg</span> /
                            <span class="text-red-600 font-bold">{{ $item->pre_weight_kg }}kg</span> /
                            <span class="text-emerald-600 font-bold">{{ $item->post_weight_kg ? $item->post_weight_kg . 'kg' : '--' }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="font-bold text-indigo-600">{{ number_format($item->uf_target_ml) }} ml</span>
                            @if($item->uf_actual_ml)
                                <span class="text-[10px] text-slate-400 block">(Đã rút: {{ number_format($item->uf_actual_ml) }} ml)</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->dialyzer_reuse_count >= 5 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                                Lần {{ $item->dialyzer_reuse_count }} / 6
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            @if($item->status === 'ongoing')
                                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-800 font-bold text-[10px] animate-pulse">ĐANG LỌC</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">HOÀN TẤT</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($item->status === 'ongoing')
                                <form action="{{ route('reception.hemodialysis.complete', $item) }}" method="POST" class="flex items-center gap-1 justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" step="0.1" name="post_weight_kg" placeholder="Kg sau" value="{{ $item->dry_weight_kg }}" class="w-16 border rounded p-1 text-center font-bold text-[11px]" required>
                                    <input type="hidden" name="uf_actual_ml" value="{{ $item->uf_target_ml }}">
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white rounded font-bold hover:bg-emerald-500 text-[10px]">Kết thúc</button>
                                </form>
                            @else
                                <span class="text-slate-400 text-[11px]">Đã lưu hồ sơ</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">Chưa có ca chạy thận nhân tạo nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

<!-- Modal Kết Nối Ca Lọc Máu -->
<div id="modal-new-hd" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Bắt Đầu Ca Lọc Máu Thận Nhân Tạo</h3>
            <button onclick="document.getElementById('modal-new-hd').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.hemodialysis.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Bệnh Nhân Thận Nhân Tạo *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="font-bold block mb-1">Ngày Lọc *</label>
                    <input type="date" name="session_date" value="{{ date('Y-m-d') }}" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">Ca Lọc *</label>
                    <select name="shift" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="morning">Sáng (07:00 - 11:30)</option>
                        <option value="afternoon">Chiều (12:30 - 17:00)</option>
                        <option value="evening">Tối (17:30 - 22:00)</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold block mb-1">Máy Lọc Số *</label>
                    <input type="text" name="machine_number" value="HD-MACHINE-03" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Cân Nặng Khô Mục Tiêu (Kg) *</label>
                    <input type="number" step="0.1" name="dry_weight_kg" value="55.0" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
                <div>
                    <label class="font-bold block mb-1">Cân Nặng Trước Lọc (Kg) *</label>
                    <input type="number" step="0.1" name="pre_weight_kg" value="57.5" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold text-red-600">
                    <span class="text-[10px] text-slate-400">Hệ thống sẽ tự động tính lượng dịch thừa 2.5kg = 2,500ml</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Mã Quả Lọc (Dialyzer Barcode) *</label>
                    <input type="text" name="dialyzer_barcode" value="FX-CORAL-60-SN098" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-mono">
                </div>
                <div>
                    <label class="font-bold block mb-1">Số Lần Đã Dùng Quả Lọc Này *</label>
                    <input type="number" name="dialyzer_reuse_count" value="1" min="1" max="6" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Điều Dưỡng Phụ Trách Máy *</label>
                <input type="text" name="nurse_in_charge" value="ĐD. Nguyễn Hoàng Lan (Đơn vị Thận)" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-new-hd').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-500">Bắt Đầu Vận Hành Lọc Máu</button>
            </div>
        </form>
    </div>
</div>
@endsection
