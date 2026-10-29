@extends('layouts.admin')

@section('title', 'Cổng Khai Báo Tiền Sử & Triệu Chứng Từ Xa Pre-Visit')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📱</span> CỔNG TỰ KHAI BÁO TIỀN SỬ & TRIỆU CHỨNG TỪ XA (PRE-VISIT)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Bệnh nhân khai báo triệu chứng, thuốc tại nhà & tiền sử dị ứng qua Mobile trước ngày khám để tự động đồng bộ vào màn hình Bác sĩ.
            </p>
        </div>
        <button onclick="document.getElementById('modal-previsit').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-teal-600 text-white font-bold text-sm shadow hover:bg-teal-500 transition flex items-center gap-2">
            <span>➕</span> Mô Phỏng Khai Báo Bệnh Nhân
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Lượt Khai Báo Trước Khám</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 shadow-sm">
            <span class="text-xs font-bold text-red-600 uppercase">Cờ Đỏ Cấp Cứu Khẩn (Red Flag)</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['urgent_red_flags'] }} ca</div>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Đã Tự Động Đồng Bộ EMR Bác Sĩ</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['synced'] }} ca</div>
        </div>
    </div>

    <!-- Screenings Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH HỒ SƠ TỰ KHAI BÁO BỆNH TRƯỚC LỊCH HẸN</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Mã lịch hẹn</th>
                        <th class="p-3">Họ tên & SĐT</th>
                        <th class="p-3">Triệu chứng tự khai</th>
                        <th class="p-3">Thuốc đang uống tại nhà</th>
                        <th class="p-3">Dị ứng ghi nhận</th>
                        <th class="p-3 text-center">Phân tầng nguy cơ</th>
                        <th class="p-3 text-center">Đồng bộ EMR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($screenings as $sc)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-mono font-bold text-teal-600">{{ $sc->appointment_code }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $sc->patient_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $sc->patient_phone }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($sc->symptoms_declared as $sym)
                                    <span class="px-2 py-0.5 rounded {{ str_contains($sym, 'cap') || str_contains($sym, 'du_doi') ? 'bg-red-100 text-red-800 font-bold' : 'bg-slate-100 text-slate-700' }} text-[10px]">
                                        {{ str_replace('_', ' ', $sym) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-3 text-slate-600 dark:text-slate-300">{{ $sc->home_medications ?? 'Không dùng' }}</td>
                        <td class="p-3">
                            @if($sc->known_allergies)
                                <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 font-bold text-[10px]">⚠️ {{ $sc->known_allergies }}</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Không dị ứng</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($sc->risk_stratification === 'urgent_red_flag')
                                <span class="px-2.5 py-1 rounded-full bg-red-600 text-white font-black text-[10px] uppercase animate-pulse">CỜ ĐỎ KHẨN CẤP</span>
                            @elseif($sc->risk_stratification === 'priority')
                                <span class="px-2.5 py-1 rounded-full bg-amber-500 text-white font-bold text-[10px] uppercase">Ưu Tiên</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">Thường Quy</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <span class="text-emerald-600 font-bold text-xs">✓ Đã đồng bộ EMR</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Chưa có hồ sơ tự khai báo nào trước khám.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $screenings->links() }}
        </div>
    </div>
</div>

<!-- Modal Khai Báo Pre-Visit -->
<div id="modal-previsit" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Mô Phỏng Cổng Bệnh Nhân Tự Khai Báo Pre-Visit</h3>
            <button onclick="document.getElementById('modal-previsit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.pre-visit.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Mã Lịch Hẹn Khám *</label>
                    <input type="text" name="appointment_code" value="AP-{{ date('Ymd') }}-PRE1" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-mono">
                </div>
                <div>
                    <label class="font-bold block mb-1">Họ Tên Bệnh Nhân *</label>
                    <input type="text" name="patient_name" value="Nguyễn Hoàng Minh" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Số Điện Thoại *</label>
                <input type="text" name="patient_phone" value="0918765432" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div>
                <label class="font-bold block mb-1">Các Triệu Chứng Đang Gặp Phải (Tích Chọn) *</label>
                <div class="grid grid-cols-2 gap-2 p-3 border rounded-xl bg-slate-50 dark:bg-slate-900">
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="symptoms_declared[]" value="sot_nhe"> <span>Sốt nhẹ</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="symptoms_declared[]" value="ho_khan"> <span>Ho khan</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="symptoms_declared[]" value="dau_dau"> <span>Đau đầu</span></label>
                    <label class="flex items-center gap-1.5 text-red-600 font-bold"><input type="checkbox" name="symptoms_declared[]" value="dau_nguc_du_doi"> <span>Đau ngực dữ dội (CỜ ĐỎ)</span></label>
                    <label class="flex items-center gap-1.5 text-red-600 font-bold"><input type="checkbox" name="symptoms_declared[]" value="kho_tho_cap"> <span>Khó thở cấp (CỜ ĐỎ)</span></label>
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Thuốc Đang Uống Tại Nhà</label>
                <input type="text" name="home_medications" value="Amlodipine 5mg (1v/ngày), Glucophage 500mg (2v/ngày)" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div>
                <label class="font-bold block mb-1">Tiền Sử Dị Ứng</label>
                <input type="text" name="known_allergies" value="Dị ứng tôm cua, kháng sinh Ampicillin" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-previsit').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-500">Gửi Hồ Sơ & Đồng Bộ EMR</button>
            </div>
        </form>
    </div>
</div>
@endsection
