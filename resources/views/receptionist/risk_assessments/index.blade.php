@extends('layouts.admin')

@section('title', 'Đánh Giá Nguy Cơ An Toàn Điều Dưỡng (Braden & Morse)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🛏️</span> ĐÁNH GIÁ NGUY CƠ LOÉT TÌ ĐÈ (BRADEN) & NGUY CƠ NGÃ (MORSE)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Bộ tiêu chuẩn an toàn người bệnh quốc tế JCI IPSG.6 & Thông tư hướng dẫn chăm sóc điều dưỡng Bộ Y Tế.
            </p>
        </div>
        <button onclick="document.getElementById('modal-assessment').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow hover:bg-indigo-500 transition flex items-center gap-2">
            <span>➕</span> Thực Hiện Đánh Giá Nguy Cơ
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Lượt Đánh Giá</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Nguy Cơ Ngã Cao (Morse &ge; 45)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['morse_high_risk'] }} ca</div>
            <span class="text-[11px] text-amber-700">Yêu cầu gắn vòng tay vàng & thanh chắn</span>
        </div>
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 shadow-sm">
            <span class="text-xs font-bold text-rose-600 uppercase">Nguy Cơ Loét Cao (Braden &le; 12)</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['braden_high_risk'] }} ca</div>
            <span class="text-[11px] text-rose-700">Yêu cầu đệm hơi & đổi tư thế 2h/lần</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b flex justify-between items-center text-xs">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH BỆNH NHÂN ĐƯỢC GIÁM SÁT AN TOÀN</h3>
            <div class="flex gap-2">
                <a href="{{ route('reception.risk-assessments.index') }}" class="px-2.5 py-1 rounded {{ !request('assessment_type') ? 'bg-slate-800 text-white dark:bg-white dark:text-slate-900 font-bold' : 'text-slate-500' }}">Tất cả</a>
                <a href="{{ route('reception.risk-assessments.index', ['assessment_type' => 'morse_fall']) }}" class="px-2.5 py-1 rounded {{ request('assessment_type') == 'morse_fall' ? 'bg-amber-600 text-white font-bold' : 'text-slate-500' }}">Thang đo Ngã (Morse)</a>
                <a href="{{ route('reception.risk-assessments.index', ['assessment_type' => 'braden_scale']) }}" class="px-2.5 py-1 rounded {{ request('assessment_type') == 'braden_scale' ? 'bg-rose-600 text-white font-bold' : 'text-slate-500' }}">Thang đo Loét (Braden)</a>
            </div>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Thời gian</th>
                        <th class="p-3">Bệnh nhân</th>
                        <th class="p-3">Loại đánh giá</th>
                        <th class="p-3 text-center">Điểm số</th>
                        <th class="p-3 text-center">Phân loại nguy cơ</th>
                        <th class="p-3">Can thiệp an toàn đã kích hoạt</th>
                        <th class="p-3">Điều dưỡng thực hiện</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($assessments as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 text-slate-500">{{ $item->assessed_at->format('H:i d/m/Y') }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $item->patientProfile->full_name }}</strong>
                            <span class="text-[11px] text-slate-400">{{ $item->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3">
                            @if($item->assessment_type === 'morse_fall')
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">🚶 Nguy Cơ Ngã (Morse)</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">🛏️ Loét Tì Đè (Braden)</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-mono font-bold text-sm">{{ $item->score }}</td>
                        <td class="p-3 text-center">
                            <span class="px-2.5 py-1 rounded-full font-bold text-[11px] uppercase {{ in_array($item->risk_category, ['high', 'very_high']) ? 'bg-red-600 text-white' : ($item->risk_category === 'moderate' ? 'bg-amber-500 text-white' : 'bg-emerald-500/10 text-emerald-600') }}">
                                {{ $item->risk_category }}
                            </span>
                        </td>
                        <td class="p-3">
                            @if(!empty($item->preventive_actions))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($item->preventive_actions as $act)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px]">{{ $act }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 text-[11px]">Chăm sóc thường quy</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-600 dark:text-slate-300">{{ $item->assessed_by }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Chưa có bản ghi đánh giá nguy cơ điều dưỡng nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $assessments->links() }}
        </div>
    </div>
</div>

<!-- Modal Đánh Giá Nguy Cơ -->
<div id="modal-assessment" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Thực Hiện Đánh Giá Nguy Cơ Điều Dưỡng</h3>
            <button onclick="document.getElementById('modal-assessment').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.risk-assessments.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Bệnh Nhân *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-bold block mb-1">Thang Đo Lâm Sàng *</label>
                <select name="assessment_type" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    <option value="morse_fall">Thang Đo Nguy Cơ Té Ngã (Morse Fall Scale)</option>
                    <option value="braden_scale">Thang Đo Nguy Cơ Loét Tì Đè (Braden Scale)</option>
                </select>
            </div>
            <div>
                <label class="font-bold block mb-1">Tổng Điểm Chấm Được *</label>
                <input type="number" name="score" value="50" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                <span class="text-[10px] text-slate-400">Morse: 0-24 (Thấp), 25-44 (Trung bình), &ge;45 (Cao). Braden: &le;12 (Rất cao), 13-14 (Cao), 15-18 (TB), &ge;19 (Thấp)</span>
            </div>
            <div>
                <label class="font-bold block mb-1">Các Can Thiệp An Toàn Bắt Buộc Kèm Theo</label>
                <div class="space-y-1.5 p-3 border rounded-xl bg-slate-50 dark:bg-slate-900">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="preventive_actions[]" value="Đeo vòng tay cảnh báo nguy cơ vàng" checked>
                        <span>Đeo vòng tay cảnh báo nguy cơ vàng</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="preventive_actions[]" value="Kéo thanh chắn giường 2 bên" checked>
                        <span>Kéo thanh chắn giường 2 bên</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="preventive_actions[]" value="Nằm đệm hơi chống loét" checked>
                        <span>Nằm đệm hơi chống loét</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="preventive_actions[]" value="Lăn trở tư thế 2 giờ/lần">
                        <span>Lăn trở tư thế 2 giờ/lần</span>
                    </label>
                </div>
            </div>
            <input type="hidden" name="details[note]" value="Thực hiện theo dõi định kỳ">
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-assessment').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-500">Lưu Đánh Giá</button>
            </div>
        </form>
    </div>
</div>
@endsection
