@extends('layouts.admin')

@section('title', 'Giám Sát Phơi Nhiễm Nghề Nghiệp & Tai Nạn Kim Tiêm')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>💉</span> GIÁM SÁT PHƠI NHIỄM NGHỀ NGHIỆP & TAI NẠN KIM TIÊM (JCI GL.4)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Quản lý quy trình xử trí phơi nhiễm HIV/HBV/HCV trong "Thời Gian Vàng 72 Giờ" và phác đồ dự phòng ARV (PEP).
            </p>
        </div>
        <button onclick="document.getElementById('modal-report-exposure').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm shadow hover:bg-red-500 transition flex items-center gap-2">
            <span>➕</span> Khai Báo Sự Cố Phơi Nhiễm
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Sự Cố Phơi Nhiễm</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_incidents'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 shadow-sm">
            <span class="text-xs font-bold text-amber-600 uppercase">Nhân Viên Đang Theo Dõi (1-6 tháng)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['monitoring'] }} người</div>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-600 uppercase">Đã Uống PEP Trong 72 Giờ</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['pep_prescribed'] }} ca</div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">NHẬT KÝ THEO DÕI TAI NẠN NGHỀ NGHIỆP NHÂN VIÊN Y TẾ</h3>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Mã sự cố</th>
                        <th class="p-3">Nhân viên y tế</th>
                        <th class="p-3">Khoa / Phòng</th>
                        <th class="p-3">Hình thức phơi nhiễm</th>
                        <th class="p-3">Nguồn bệnh nhân</th>
                        <th class="p-3 text-center">Điều trị PEP ARV</th>
                        <th class="p-3">Trạng thái giám sát</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($exposures as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-mono font-bold text-red-600">{{ $item->incident_code }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $item->employee_name }}</strong>
                            <span class="text-[11px] text-slate-400 capitalize">Vị trí: {{ $item->employee_role }}</span>
                        </td>
                        <td class="p-3">{{ $item->department }}</td>
                        <td class="p-3">
                            @if($item->exposure_type === 'needlestick')
                                <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 font-bold">📌 Kim tiêm đâm thấu da</span>
                            @elseif($item->exposure_type === 'sharp_cut')
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">🔪 Vật sắc nhọn cứa</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-bold">💦 Máu bắn niêm mạc</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div>BN: <strong>{{ $item->sourcePatient ? $item->sourcePatient->full_name : 'Không rõ nguồn' }}</strong></div>
                            <span class="text-[10px] uppercase font-bold text-slate-500">Tình trạng: {{ $item->source_status }}</span>
                        </td>
                        <td class="p-3 text-center">
                            @if($item->pep_started)
                                <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px]">
                                    ✓ Đã uống PEP (Sau {{ $item->pep_start_hours_elapsed }}h)
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Không chỉ định</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px] uppercase">
                                {{ $item->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Không có sự cố tai nạn kim tiêm hay phơi nhiễm nào được ghi nhận.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $exposures->links() }}
        </div>
    </div>
</div>

<!-- Modal Khai Báo Sự Cố -->
<div id="modal-report-exposure" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Khai Báo Phơi Nhiễm Nghề Nghiệp (Sharps Injury)</h3>
            <button onclick="document.getElementById('modal-report-exposure').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.occupational-exposure.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Họ Tên Nhân Viên Y Tế Bị Tai Nạn *</label>
                <input type="text" name="employee_name" required placeholder="ĐD. Nguyễn Thị Thảo" class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Vị Trí Công Tác *</label>
                    <select name="employee_role" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="nurse">Điều dưỡng</option>
                        <option value="doctor">Bác sĩ</option>
                        <option value="lab_tech">Kỹ thuật viên xét nghiệm</option>
                        <option value="cleaning_staff">Nhân viên vệ sinh / Hộ lý</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold block mb-1">Khoa / Phòng Xảy Ra Sự Cố *</label>
                    <input type="text" name="department" value="Khoa Cấp Cứu Tầng 1" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold block mb-1">Hình Thức Tai Nạn *</label>
                    <select name="exposure_type" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="needlestick">Kim tiêm đâm thấu da</option>
                        <option value="sharp_cut">Lưỡi dao mổ cứa rách da</option>
                        <option value="splash_mucosa">Máu/dịch bắn vào mắt hoặc niêm mạc</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold block mb-1">Tình Trạng Nguồn Bệnh Nhân *</label>
                    <select name="source_status" required class="w-full border rounded-xl p-2 bg-slate-50 dark:bg-slate-700">
                        <option value="unknown">Chưa rõ (Vật rác thải vô danh)</option>
                        <option value="negative">Âm tính đã xét nghiệm</option>
                        <option value="hiv_pos">HIV Dương Tính</option>
                        <option value="hbv_pos">Viêm Gan B (HBsAg+)</option>
                        <option value="hcv_pos">Viêm Gan C (Anti-HCV+)</option>
                    </select>
                </div>
            </div>
            <div class="p-3 border rounded-xl bg-slate-50 dark:bg-slate-900 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="pep_started" id="pep_check" value="1" checked>
                    <label for="pep_check" class="font-bold text-red-600">Đã chỉ định phác đồ điều trị dự phòng ARV (PEP)</label>
                </div>
                <div>
                    <label class="font-bold block mb-1">Số giờ trôi qua từ lúc bị nạn đến khi uống thuốc (≤72h)</label>
                    <input type="number" name="pep_start_hours_elapsed" value="2" min="1" max="72" class="w-full border rounded p-1.5 font-bold">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-report-exposure').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-500">Lập Biên Bản Sự Cố</button>
            </div>
        </form>
    </div>
</div>
@endsection
