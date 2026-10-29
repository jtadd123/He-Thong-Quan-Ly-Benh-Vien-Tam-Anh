@extends('layouts.admin')

@section('title', 'Tủ Thuốc Trực Cấp Cứu & Thuốc Gây Nghiện TT20')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>💊</span> TỦ THUỐC TRỰC CẤP CỨU & THUỐC GÂY NGHIỆN (TT 20/2017/TT-BYT)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Quản lý xuất - hủy thuốc gây nghiện, hướng thần, tiền chất với cơ chế ký kép (Dual Sign-Off) và đối chiếu vỏ ống.
            </p>
        </div>
        <button onclick="document.getElementById('modal-dispense-narcotic').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-purple-600 text-white font-bold text-sm shadow hover:bg-purple-500 transition flex items-center gap-2">
            <span>➕</span> Xuất Thuốc Trực Cấp Cứu
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tủ Trực Hoạt Động</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_boxes'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Tồn Kho Thuốc Độc</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_stock'] }} ống</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Đã Xuất Hôm Nay</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['dispensed_today'] }} ống</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Chờ Hủy Vỏ Ống</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending_disposal'] }} lượt</div>
        </div>
    </div>

    <!-- Boxes Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($boxes as $box)
        <div class="p-4 rounded-2xl border bg-white dark:bg-slate-800 shadow-sm space-y-2">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 font-bold text-[10px]">{{ $box->box_code }}</span>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white mt-1">{{ $box->medication_name }}</h4>
                    <span class="text-xs text-slate-500">{{ $box->concentration }}</span>
                </div>
                <div class="text-right">
                    <span class="text-xl font-black {{ $box->quantity_stock <= $box->min_threshold ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ $box->quantity_stock }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">{{ $box->unit }}</span>
                </div>
            </div>
            <div class="text-xs text-slate-500 pt-2 border-t flex justify-between items-center">
                <span>Vị trí: <strong>{{ $box->ward_name }}</strong></span>
                <span class="text-[11px]">Đã xuất: {{ $box->dispense_logs_count }} lần</span>
            </div>
        </div>
        @empty
        <div class="col-span-3 p-6 text-center text-slate-400 bg-white rounded-2xl border">
            Chưa có tủ thuốc trực nào được tạo.
        </div>
        @endforelse
    </div>

    <!-- Dispense Logs -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">SỔ THEO DÕI XUẤT - HỦY THUỐC GÂY NGHIỆN (BỘ Y TẾ)</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Thời gian</th>
                        <th class="p-3">Thuốc</th>
                        <th class="p-3">Bệnh nhân</th>
                        <th class="p-3 text-center">Số lượng</th>
                        <th class="p-3">Bác sĩ chỉ định</th>
                        <th class="p-3">Điều dưỡng & Nhân chứng (Ký kép)</th>
                        <th class="p-3">Chỉ định y khoa</th>
                        <th class="p-3 text-center">Thu hồi vỏ ống & Hủy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 text-slate-500">{{ $log->created_at->format('H:i d/m/Y') }}</td>
                        <td class="p-3 font-bold text-purple-700 dark:text-purple-400">{{ $log->box->medication_name }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $log->patientProfile->full_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $log->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3 text-center font-bold text-sm">{{ $log->quantity_dispensed }}</td>
                        <td class="p-3 font-semibold">{{ $log->doctor_name }}</td>
                        <td class="p-3 text-slate-600 dark:text-slate-300">
                            <div>ĐD: <strong>{{ $log->nurse_name }}</strong></div>
                            <div>NC: <strong>{{ $log->witness_name }}</strong></div>
                        </td>
                        <td class="p-3 text-slate-500">{{ $log->indication }}</td>
                        <td class="p-3 text-center">
                            @if($log->disposal_verified)
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    ✓ Đã hủy {{ $log->empty_ampoules_returned }} vỏ ống
                                </span>
                            @else
                                <form action="{{ route('reception.narcotics.verify-disposal', $log) }}" method="POST" class="flex items-center gap-1 justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="empty_ampoules_returned" value="{{ $log->quantity_dispensed }}" max="{{ $log->quantity_dispensed }}" min="1" class="w-14 border rounded p-1 text-center font-bold">
                                    <button type="submit" class="px-2 py-1 bg-amber-600 text-white rounded font-bold hover:bg-amber-500 text-[10px]">Hủy</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">Chưa có lượt xuất thuốc trực cấp cứu nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Modal Xuất Thuốc Trực -->
<div id="modal-dispense-narcotic" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Xuất Thuốc Gây Nghiện / Hướng Thần (Dual Sign-Off)</h3>
            <button onclick="document.getElementById('modal-dispense-narcotic').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.narcotics.dispense') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Tủ Thuốc Trực Cấp Cứu *</label>
                <select name="box_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($boxes as $b)
                        <option value="{{ $b->id }}">{{ $b->box_code }} - {{ $b->medication_name }} (Tồn: {{ $b->quantity_stock }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-bold block mb-1">Bệnh Nhân Nhận Thuốc *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Bác Sĩ Chỉ Định Kê Đơn *</label>
                    <input type="text" name="doctor_name" value="BS. CKII. Nguyễn Minh Trí" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">Số Lượng Xuất (Ống) *</label>
                    <input type="number" name="quantity_dispensed" value="1" min="1" max="10" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Điều Dưỡng Xuất Thuốc *</label>
                    <input type="text" name="nurse_name" value="ĐD. Trần Thị Mai" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">Nhân Chứng Đối Chiếu (Bắt buộc JCI) *</label>
                    <input type="text" name="witness_name" value="ĐD. Lê Văn Hùng" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Chỉ Định Điều Trị Cụ Thể *</label>
                <input type="text" name="indication" value="Giảm đau cấp sau phẫu thuật thay van tim" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-dispense-narcotic').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-500">Xác Nhận Ký Kép & Xuất Thuốc</button>
            </div>
        </form>
    </div>
</div>
@endsection
