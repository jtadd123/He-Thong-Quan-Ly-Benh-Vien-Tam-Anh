@extends('layouts.admin')

@section('title', 'Cảnh Báo Sớm Sinh Hiệu NEWS2 & Đội Phản Ứng Nhanh RRT')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🫀</span> HỆ THỐNG CẢNH BÁO SỚM NEWS2 & ĐỘI PHẢN ỨNG NHANH (RRT)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Tiêu chuẩn National Early Warning Score 2 (Royal College of Physicians & JCI) - Nhận diện sớm nguy cơ suy sụp sinh hiệu.
            </p>
        </div>
        <button onclick="document.getElementById('modal-record-news2').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm shadow hover:bg-red-500 transition flex items-center gap-2">
            <span>➕</span> Ghi Nhận Sinh Hiệu NEWS2
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Lượt Đo NEWS2</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 shadow-sm">
            <span class="text-xs font-bold text-red-600 uppercase">Nguy Cơ Rất Cao (≥7 Điểm)</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['high_critical'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Nguy Cơ Trung Bình (5-6)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['medium'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-sky-500/10 border border-sky-500/30 shadow-sm">
            <span class="text-xs font-bold text-sky-600 uppercase">Ca RRT Đang Xử Trí</span>
            <div class="text-2xl font-black text-sky-600 mt-1">{{ $stats['rrt_active'] }}</div>
        </div>
    </div>

    <!-- Active RRT Dispatches Banner -->
    @if($rrtDispatches->count() > 0)
    <div class="p-4 rounded-2xl bg-gradient-to-r from-red-600 to-rose-700 text-white shadow-lg space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl animate-pulse">🚨</span>
                <h3 class="font-black text-base uppercase tracking-wider">NHẬT KÝ ĐỘI PHẢN ỨNG NHANH RRT XUẤT KÍCH</h3>
            </div>
            <span class="px-3 py-1 rounded-full bg-white/20 text-xs font-bold">Thời gian vàng: &lt; 3 phút</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            @foreach($rrtDispatches->take(2) as $rrt)
            <div class="p-3 bg-white/10 rounded-xl backdrop-blur-sm border border-white/20 flex justify-between items-center">
                <div>
                    <span class="font-bold text-yellow-300">{{ $rrt->activation_code }}</span> • {{ $rrt->patientProfile->full_name }}
                    <p class="text-white/80 mt-0.5">Vị trí: <strong>{{ $rrt->ward_location }}</strong></p>
                </div>
                <span class="px-2.5 py-1 rounded bg-yellow-400 text-slate-900 font-bold uppercase">{{ $rrt->status }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">LỊCH SỬ ĐO ĐIỂM NEWS2 & CẢNH BÁO LÂM SÀNG</h3>
            <div class="flex gap-2">
                <a href="{{ route('reception.news2-rrt.index') }}" class="px-2.5 py-1 text-xs rounded-lg {{ !request('risk_level') ? 'bg-slate-800 text-white dark:bg-white dark:text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-100' }}">Tất cả</a>
                <a href="{{ route('reception.news2-rrt.index', ['risk_level' => 'high_critical']) }}" class="px-2.5 py-1 text-xs rounded-lg {{ request('risk_level') == 'high_critical' ? 'bg-red-600 text-white font-bold' : 'text-red-600 hover:bg-red-50' }}">Nguy cơ rất cao</a>
            </div>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Thời gian</th>
                        <th class="p-3">Bệnh nhân</th>
                        <th class="p-3 text-center">Nhịp thở (RR)</th>
                        <th class="p-3 text-center">SpO2 / O2</th>
                        <th class="p-3 text-center">Huyết áp / Tim</th>
                        <th class="p-3 text-center">Tri giác (ACVPU)</th>
                        <th class="p-3 text-center">Nhiệt độ</th>
                        <th class="p-3 text-center">Điểm NEWS2</th>
                        <th class="p-3 text-center">RRT Trigger</th>
                        <th class="p-3">Người đo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-mono text-slate-500">{{ $alert->created_at->format('H:i d/m/Y') }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $alert->patientProfile->full_name }}</strong>
                            <span class="text-[11px] text-slate-400">{{ $alert->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3 text-center font-bold">{{ $alert->respiratory_rate }}/p</td>
                        <td class="p-3 text-center">
                            <span class="font-bold {{ $alert->oxygen_saturation < 92 ? 'text-red-600' : 'text-emerald-600' }}">{{ $alert->oxygen_saturation }}%</span>
                            @if($alert->supplemental_oxygen)<span class="px-1 py-0.5 rounded bg-sky-100 text-sky-700 text-[10px] block">O2 Khí Dung</span>@endif
                        </td>
                        <td class="p-3 text-center font-mono">{{ $alert->systolic_bp }} / {{ $alert->pulse_rate }} bpm</td>
                        <td class="p-3 text-center uppercase font-bold">{{ $alert->consciousness_level }}</td>
                        <td class="p-3 text-center">{{ $alert->temperature }}°C</td>
                        <td class="p-3 text-center">
                            <span class="px-2.5 py-1 rounded-full font-black text-xs {{ $alert->risk_level === 'high_critical' ? 'bg-red-600 text-white animate-pulse' : ($alert->risk_level === 'medium' ? 'bg-amber-500 text-white' : 'bg-emerald-500/10 text-emerald-600') }}">
                                {{ $alert->news2_score }}
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            @if($alert->rrt_triggered)
                                <span class="px-2 py-0.5 rounded bg-red-600 text-white font-bold text-[10px] animate-bounce inline-block">🚨 ĐÃ ĐIỀU ĐỘNG</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Bình thường</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-600 dark:text-slate-300">{{ $alert->recorded_by }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-slate-400">Chưa có bản ghi sinh hiệu NEWS2 nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $alerts->links() }}
        </div>
    </div>
</div>

<!-- Modal Ghi Nhận NEWS2 -->
<div id="modal-record-news2" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Ghi Nhận Sinh Hiệu NEWS2 (Royal College)</h3>
            <button onclick="document.getElementById('modal-record-news2').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.news2-rrt.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Bệnh Nhân *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Nhịp thở (lần/phút) *</label>
                    <input type="number" name="respiratory_rate" value="18" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">SpO2 (%) *</label>
                    <input type="number" name="oxygen_saturation" value="98" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Huyết áp tâm thu (mmHg) *</label>
                    <input type="number" name="systolic_bp" value="120" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">Nhịp tim (bpm) *</label>
                    <input type="number" name="pulse_rate" value="75" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Mức độ tri giác (ACVPU) *</label>
                    <select name="consciousness_level" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="alert">Tỉnh táo (Alert)</option>
                        <option value="confusion">Lú lẫn mới xuất hiện (Confusion)</option>
                        <option value="voice">Đáp ứng với lời nói (Voice)</option>
                        <option value="pain">Chỉ đáp ứng kích thích đau (Pain)</option>
                        <option value="unresponsive">Không phản ứng (Unresponsive)</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold block mb-1">Thân nhiệt (°C) *</label>
                    <input type="number" step="0.1" name="temperature" value="37.0" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="supplemental_oxygen" id="supp_o2" value="1" class="rounded">
                <label for="supp_o2" class="font-semibold">Bệnh nhân đang thở oxy qua mask/gọng kính (+2 điểm)</label>
            </div>
            <div>
                <label class="font-bold block mb-1">Vị trí buồng bệnh / khu vực</label>
                <input type="text" name="ward_location" placeholder="P.302 Khoa Nội Tim Mạch" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-record-news2').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-500">Tính Điểm & Lưu</button>
            </div>
        </form>
    </div>
</div>
@endsection
