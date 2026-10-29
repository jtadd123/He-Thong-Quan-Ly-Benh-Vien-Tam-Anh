@extends('layouts.admin')

@section('title', 'Đăng Ký Hiến Tạng Nhân Đạo & Di Chúc Y Tế Giảm Nhẹ Living Will')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🕊️</span> ĐĂNG KÝ HIẾN TẠNG NHÂN ĐẠO & DI CHÚC Y TẾ (LIVING WILL / DNR)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Quản lý hồ sơ đăng ký hiến mô tạng tự nguyện cứu người & Hồ sơ Chăm sóc giảm nhẹ / Chỉ thị không hồi sức tim phổi (DNR).
            </p>
        </div>
        <button onclick="document.getElementById('modal-living-will').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-pink-600 text-white font-bold text-sm shadow hover:bg-pink-500 transition flex items-center gap-2">
            <span>➕</span> Đăng Ký Hồ Sơ Nhân Đạo
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Tổng Hồ Sơ Pháp Lý Đã Đăng Ký</span>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['total_registered'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-pink-500/10 border border-pink-500/30 shadow-sm">
            <span class="text-xs font-bold text-pink-600 uppercase">Hồ Sơ Đăng Ký Hiến Mô, Tạng Cứu Người</span>
            <div class="text-2xl font-black text-pink-600 mt-1">{{ $stats['organ_donors'] }} người</div>
        </div>
        <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 shadow-sm">
            <span class="text-xs font-bold text-purple-600 uppercase">Di Chúc Y Tế / Chỉ Thị DNR Giảm Nhẹ</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['living_wills'] }} ca</div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b flex justify-between items-center text-xs">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white">DANH SÁCH HỒ SƠ NGUYỆN VỌNG Y KHOA</h3>
            <div class="flex gap-2">
                <a href="{{ route('reception.living-will.index') }}" class="px-2.5 py-1 rounded {{ !request('record_type') ? 'bg-slate-800 text-white dark:bg-white dark:text-slate-900 font-bold' : 'text-slate-500' }}">Tất cả</a>
                <a href="{{ route('reception.living-will.index', ['record_type' => 'organ_donation']) }}" class="px-2.5 py-1 rounded {{ request('record_type') == 'organ_donation' ? 'bg-pink-600 text-white font-bold' : 'text-slate-500' }}">Hiến Tạng</a>
                <a href="{{ route('reception.living-will.index', ['record_type' => 'living_will_dnr']) }}" class="px-2.5 py-1 rounded {{ request('record_type') == 'living_will_dnr' ? 'bg-purple-600 text-white font-bold' : 'text-slate-500' }}">Di Chúc Y Tế (DNR)</a>
            </div>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase border-b">
                    <tr>
                        <th class="p-3">Mã đăng ký</th>
                        <th class="p-3">Bệnh nhân / Người đăng ký</th>
                        <th class="p-3">Phân loại hồ sơ</th>
                        <th class="p-3">Nội dung nguyện vọng / Bộ phận hiến</th>
                        <th class="p-3">Người đại diện gia đình ủy quyền</th>
                        <th class="p-3 text-center">Cam kết hợp pháp</th>
                        <th class="p-3 text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($records as $rec)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/50">
                        <td class="p-3 font-mono font-bold text-pink-600 dark:text-pink-400">{{ $rec->registration_number }}</td>
                        <td class="p-3">
                            <strong class="text-slate-800 dark:text-white block">{{ $rec->patientProfile->full_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $rec->patientProfile->patient_code }}</span>
                        </td>
                        <td class="p-3">
                            @if($rec->record_type === 'organ_donation')
                                <span class="px-2.5 py-1 rounded-full bg-pink-100 text-pink-800 font-bold text-[10px] uppercase">❤️ Hiến Mô, Tạng</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] uppercase">🕊️ DNR Giảm Nhẹ</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($rec->record_type === 'organ_donation' && !empty($rec->organs_pledged))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rec->organs_pledged as $org)
                                        <span class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 border border-pink-200 text-[10px] font-semibold">{{ $org }}</span>
                                    @endforeach
                                </div>
                            @elseif($rec->record_type === 'living_will_dnr' && !empty($rec->dnr_directives))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rec->dnr_directives as $dir)
                                        <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-semibold">{{ $dir }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400">Tất cả theo chỉ định pháp lý</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-700 dark:text-slate-300">
                            <div><strong>{{ $rec->representative_name }}</strong> ({{ $rec->representative_relationship }})</div>
                            <span class="text-[10px] text-slate-400">SĐT: {{ $rec->representative_phone }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                ✓ Đã Ký Bản Cam Kết
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-[10px] uppercase">
                                {{ $rec->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Chưa có hồ sơ hiến tạng hay di chúc y tế nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t">
            {{ $records->links() }}
        </div>
    </div>
</div>

<!-- Modal Đăng Ký Hồ Sơ -->
<div id="modal-living-will" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Đăng Ký Hiến Mô Tạng & Di Chúc Y Tế (DNR)</h3>
            <button onclick="document.getElementById('modal-living-will').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="{{ route('reception.living-will.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Người Đăng Ký (Bệnh Nhân) *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-bold block mb-1">Loại Nguyện Vọng Pháp Lý *</label>
                <select name="record_type" id="rec_type_select" required onchange="toggleDirectives(this.value)" class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    <option value="organ_donation">Đăng ký hiến mô, tạng tự nguyện sau khi qua đời</option>
                    <option value="living_will_dnr">Di chúc y tế / Chỉ thị không hồi sức cấp cứu (DNR Directive)</option>
                </select>
            </div>
            <div id="box-organ" class="p-3 border rounded-xl bg-pink-50/50 dark:bg-slate-900 space-y-2">
                <span class="font-bold text-pink-700 dark:text-pink-300 block">Các Bộ Phận Tự Nguyện Hiến Cứu Người:</span>
                <div class="grid grid-cols-2 gap-1.5">
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="Tim" checked> <span>Tim</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="Gan" checked> <span>Gan</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="2 Thận" checked> <span>2 Thận</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="Giác Mạc Mắt" checked> <span>Giác Mạc Mắt</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="Tụy Tạng"> <span>Tụy Tạng</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="organs_pledged[]" value="Phổi"> <span>Phổi</span></label>
                </div>
            </div>
            <div id="box-dnr" class="p-3 border rounded-xl bg-purple-50/50 dark:bg-slate-900 space-y-2 hidden">
                <span class="font-bold text-purple-700 dark:text-purple-300 block">Chỉ Thị Chăm Sóc Giảm Nhẹ (DNR Directives):</span>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="dnr_directives[]" value="Không ép tim ngoài lồng ngực (No CPR)" checked> <span>Không ép tim ngoài lồng ngực (No CPR)</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="dnr_directives[]" value="Không đặt ống nội khí quản thở máy" checked> <span>Không đặt ống nội khí quản thở máy</span></label>
                    <label class="flex items-center gap-1.5"><input type="checkbox" name="dnr_directives[]" value="Ưu tiên tối đa giảm đau xoa dịu (Palliative)" checked> <span>Ưu tiên tối đa giảm đau xoa dịu (Palliative)</span></label>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="font-bold block mb-1">Người Đại Diện *</label>
                    <input type="text" name="representative_name" value="Nguyễn Văn An" required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">SĐT Liên Hệ *</label>
                    <input type="text" name="representative_phone" value="0903123456" required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                </div>
                <div>
                    <label class="font-bold block mb-1">Quan Hệ *</label>
                    <input type="text" name="representative_relationship" value="Con ruột" required class="w-full border rounded p-1.5 bg-slate-50 dark:bg-slate-700">
                </div>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="legal_consent_signed" id="consent_check" value="1" checked required>
                <label for="consent_check" class="font-bold text-slate-700 dark:text-slate-300">Xác nhận bản cam đoan tự nguyện đã được ký số / có chữ ký tay</label>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-living-will').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-pink-600 text-white rounded-xl font-bold hover:bg-pink-500">Lưu Hồ Sơ Ý Nguyện</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDirectives(val) {
        if (val === 'living_will_dnr') {
            document.getElementById('box-dnr').classList.remove('hidden');
            document.getElementById('box-organ').classList.add('hidden');
        } else {
            document.getElementById('box-organ').classList.remove('hidden');
            document.getElementById('box-dnr').classList.add('hidden');
        }
    }
</script>
@endsection
