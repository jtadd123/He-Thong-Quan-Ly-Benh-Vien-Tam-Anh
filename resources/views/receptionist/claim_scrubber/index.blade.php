@extends('layouts.admin')

@section('title', 'Giám Định BHYT Thời Gian Thực & Xuất XML 4210/BYT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>⚖️</span> GIÁM ĐỊNH BHYT THỜI GIAN THỰC & CỔNG XML 4210/QĐ-BYT
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Bộ lọc kiểm soát lỗi chỉ định thuốc, xét nghiệm, chẩn đoán ICD-10 chống cơ quan BHXH xuất toán tiền tỷ.
            </p>
        </div>
        <button onclick="document.getElementById('modal-scrub-claim').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow hover:bg-indigo-500 transition flex items-center gap-2">
            <span>🔍</span> Thẩm Định Hồ Sơ Chi Phí BHYT
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Hồ Sơ Giám Định</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_claims'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Hồ Sơ Chuẩn (Clean Claim)</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['clean_pass'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 shadow-sm">
            <span class="text-xs font-bold text-red-600 uppercase">Nguy Cơ Bị Từ Chối (Xuất Toán)</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['rejected_risk'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-sky-500/10 border border-sky-500/30 shadow-sm">
            <span class="text-xs font-bold text-sky-600 uppercase">Đã Xuất XML Cổng 4210</span>
            <div class="text-2xl font-black text-sky-600 mt-1">{{ $stats['xml_exported'] }}</div>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH HỒ SƠ GIÁM ĐỊNH BHYT ĐIỆN TỬ</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Bệnh nhân</th>
                        <th class="p-3">Chẩn đoán ICD-10</th>
                        <th class="p-3 text-right">Chi phí đề nghị BHYT</th>
                        <th class="p-3 text-center">Kết quả lọc vi phạm</th>
                        <th class="p-3">Lỗi cảnh báo xuất toán phát hiện</th>
                        <th class="p-3 text-center">Cổng XML 4210</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($claims as $c)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $c->patientProfile->full_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $c->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">[{{ $c->icd10_code }}]</span>
                            <span class="text-slate-700 dark:text-slate-300 block">{{ $c->icd10_name }}</span>
                        </td>
                        <td class="p-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                            {{ number_format($c->claim_amount) }} đ
                        </td>
                        <td class="p-3 text-center">
                            @if($c->scrub_status === 'clean')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">✓ HỢP LỆ (CLEAN)</span>
                            @elseif($c->scrub_status === 'rejected_high_risk')
                                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-800 font-bold text-[10px] uppercase">🚨 NGUY CƠ CAO</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] uppercase">CẢNH BÁO NHẸ</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if(!empty($c->scrub_violations))
                                <div class="space-y-1">
                                    @foreach($c->scrub_violations as $viol)
                                        <div class="text-red-600 dark:text-red-400 font-semibold text-[11px] flex items-center gap-1">
                                            <span>⚠️</span> {{ $viol }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-emerald-600 font-semibold text-[11px]">Không có lỗi quy tắc thanh toán</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($c->xml_4210_exported)
                                <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-mono font-bold text-[10px]">
                                    XML1,2,3,4,5 READY
                                </span>
                            @else
                                <span class="text-slate-400 text-[10px]">Bị chặn do lỗi</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">Chưa có hồ sơ giám định BHYT nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $claims->links() }}
        </div>
    </div>
</div>

<!-- Modal Giám Định BHYT -->
<div id="modal-scrub-claim" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Giám Định Thời Gian Thực Hồ Sơ BHYT</h3>
            <button onclick="document.getElementById('modal-scrub-claim').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.claim-scrubber.scrub') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Bệnh Nhân BHYT *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Mã Bệnh ICD-10 *</label>
                    <input type="text" name="icd10_code" value="I10" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-mono font-bold">
                </div>
                <div>
                    <label class="font-bold block mb-1">Tổng Số Tiền Đề Nghị BHYT (VNĐ) *</label>
                    <input type="number" name="claim_amount" value="1850000" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Tên Bệnh Theo Mã ICD-10 *</label>
                <input type="text" name="icd10_name" value="Bệnh tăng huyết áp vô căn (nguyên phát)" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 space-y-2">
                <span class="font-bold text-slate-700 dark:text-slate-200 block">Chọn Thuốc & Kỹ Thuật Chỉ Định Để Quét Lỗi BHYT:</span>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="designated_tests[]" value="mri_brain">
                    <span class="text-red-600 font-semibold">Chỉ định Chụp MRI Sọ Não (Thử nghiệm quét lỗi nếu ICD-10 không phải thần kinh)</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="prescribed_drugs[]" value="meropenem">
                    <span class="text-amber-600 font-semibold">Kháng sinh Meropenem 1g (Thử nghiệm cảnh báo thiếu biên bản hội chẩn)</span>
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-scrub-claim').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-500">Quét & Kiểm Tra BHYT</button>
            </div>
        </form>
    </div>
</div>
@endsection
