@extends('layouts.admin')

@section('title', 'Bản Đồ Điều Hướng Trong Nhà Thông Minh 3D/SVG')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🗺️</span> BẢN ĐỒ ĐIỀU HƯỚNG TRONG NHÀ THÔNG MINH (INDOOR WAYFINDING)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Chỉ dẫn đường đi từng bước (Turn-by-turn navigation) từ Kiosk sảnh đến các phòng khám, xét nghiệm và bãi đỗ xe.
            </p>
        </div>
        <button onclick="document.getElementById('modal-add-route').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow hover:bg-indigo-500 transition flex items-center gap-2">
            <span>➕</span> Thêm Lộ Trình Chỉ Dẫn Mới
        </button>
    </div>

    <!-- Visual Interactive Map Preview -->
    <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950 text-white shadow-xl space-y-4">
        <div class="flex justify-between items-center border-b border-indigo-800 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🏥</span>
                <div>
                    <h3 class="font-black text-sm uppercase text-indigo-300">SƠ ĐỒ KHÔNG GIAN BỆNH VIỆN TÂM ANH (INTERACTIVE MAP)</h3>
                    <p class="text-[11px] text-indigo-200/60">Tự động dẫn đường tối ưu tiết kiệm thời gian đi lại cho người cao tuổi</p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-xs border border-emerald-500/30">
                GPS Indoor Active
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400 transition cursor-pointer">
                <strong class="text-indigo-300 block">TẦNG 1 (SẢNH CHÍNH)</strong>
                <span class="text-white/60">Kiosk Tiếp Đón, Cấp Cứu 115, Quầy Dược Ngoại Trú, Thu Viện Phí VietQR</span>
            </div>
            <div class="p-3 bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400 transition cursor-pointer">
                <strong class="text-indigo-300 block">TẦNG 2 (CẬN LÂM SÀNG)</strong>
                <span class="text-white/60">Chụp MRI 3.0 Tesla, CT 768 Lát, Xét Nghiệm Máu Tự Động Roche, Siêu Âm Tim</span>
            </div>
            <div class="p-3 bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400 transition cursor-pointer">
                <strong class="text-indigo-300 block">TẦNG 3 (PHÒNG KHÁM CHUYÊN KHOA)</strong>
                <span class="text-white/60">Trung Tâm Tim Mạch, Nội Tiết Tiểu Đường, Tiêu Hóa Gan Mật, Cơ Xương Khớp</span>
            </div>
            <div class="p-3 bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400 transition cursor-pointer">
                <strong class="text-indigo-300 block">TẦNG 5 (PHẪU THUẬT & ICU)</strong>
                <span class="text-white/60">Hệ Thống 12 Phòng Mổ Áp Lực Dương Hybrid, Khoa Hồi Sức Tích Cực ICU</span>
            </div>
        </div>
    </div>

    <!-- Routes Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH LỘ TRÌNH ĐIỀU HƯỚNG TỪNG BƯỚC</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Điểm xuất phát</th>
                        <th class="p-3">Khoa / Phòng đích đến</th>
                        <th class="p-3 text-center">Tầng</th>
                        <th class="p-3 text-center">Thời gian đi bộ</th>
                        <th class="p-3">Các bước chỉ dẫn lộ trình (Turn-by-turn)</th>
                        <th class="p-3 text-center">Hỗ trợ xe lăn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($routes as $r)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-semibold text-slate-700 dark:text-slate-200">{{ $r->start_location }}</td>
                        <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">{{ $r->destination_department }}</td>
                        <td class="p-3 text-center font-bold">Tầng {{ $r->floor_level }}</td>
                        <td class="p-3 text-center font-mono font-bold">{{ $r->estimated_walk_minutes }} phút</td>
                        <td class="p-3">
                            <div class="space-y-1">
                                @foreach($r->step_directions as $idx => $step)
                                    <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                        <span class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-bold flex items-center justify-center">{{ $idx + 1 }}</span>
                                        <span>{{ $step }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            @if($r->accessibility_friendly === 'wheelchair_accessible')
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">♿ Đi Thang Máy</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">Thang Bộ</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">Chưa có lộ trình điều hướng nào được thiết lập.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $routes->links() }}
        </div>
    </div>
</div>

<!-- Modal Thêm Lộ Trình -->
<div id="modal-add-route" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Thêm Lộ Trình Bản Đồ Điều Hướng Mới</h3>
            <button onclick="document.getElementById('modal-add-route').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.wayfinding.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Điểm Xuất Phát *</label>
                <input type="text" name="start_location" value="Kiosk Cảm Ứng Sảnh Cửa Số 01" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div>
                <label class="font-bold block mb-1">Khoa / Phòng Đích Đến *</label>
                <input type="text" name="destination_department" value="Phòng Khám Nội Tim Mạch (P.302)" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="font-bold block mb-1">Tầng Số *</label>
                    <input type="number" name="floor_level" value="3" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
                <div>
                    <label class="font-bold block mb-1">Ước Tính Đi Bộ *</label>
                    <input type="number" name="estimated_walk_minutes" value="3" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700 font-bold">
                </div>
                <div>
                    <label class="font-bold block mb-1">Tiếp Cận Xe Lăn *</label>
                    <select name="accessibility_friendly" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="wheelchair_accessible">Có (Thang máy)</option>
                        <option value="stairs_only">Chỉ thang bộ</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Các Bước Hướng Dẫn Chi Tiết *</label>
                <div class="space-y-1.5">
                    <input type="text" name="step_directions[]" value="Từ cửa sảnh, đi thẳng 25m qua Quầy Lễ Tân Số 01." required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                    <input type="text" name="step_directions[]" value="Rẽ trái vào Cụm Thang Máy Khu B, bấm lên Tầng 3." required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                    <input type="text" name="step_directions[]" value="Bước ra khỏi thang máy rẽ phải 10m, phòng 302 ở phía tay trái." required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-add-route').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-500">Lưu Lộ Trình</button>
            </div>
        </form>
    </div>
</div>
@endsection
